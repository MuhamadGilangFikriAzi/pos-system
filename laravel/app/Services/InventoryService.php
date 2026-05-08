<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductWarehouse;
use App\Models\StockMutation;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Get realtime stock for a product in a specific warehouse.
     */
    public function getStock(int $productId, ?int $warehouseId = null): int
    {
        $q = ProductWarehouse::where('product_id', $productId);
        if ($warehouseId) {
            $q->where('warehouse_id', $warehouseId);
        }
        return (int) $q->sum('stock');
    }

    /**
     * Get stock per warehouse.
     */
    public function getStockAllLocations(int $productId): array
    {
        return ProductWarehouse::with('warehouse')
            ->where('product_id', $productId)
            ->get()
            ->map(fn ($pw) => [
                'warehouse_id' => $pw->warehouse_id,
                'warehouse' => $pw->warehouse->name ?? '-',
                'stock' => (int) $pw->stock,
                'min_stock' => $pw->min_stock,
            ])
            ->toArray();
    }

    /**
     * Core: adjust stock (create mutation, update product_warehouse).
     */
    public function adjustStock(
        int $productId,
        int $warehouseId,
        int $userId,
        float $quantity,
        string $type,
        ?string $reference = null,
        ?string $notes = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?int $variantId = null,
        float $unitCost = 0,
    ): StockMutation {
        return DB::transaction(function () use ($productId, $warehouseId, $userId, $quantity, $type, $reference, $notes, $referenceType, $referenceId, $variantId, $unitCost) {
            $pw = ProductWarehouse::firstOrCreate(
                ['product_id' => $productId, 'warehouse_id' => $warehouseId],
                ['stock' => 0, 'min_stock' => 5, 'average_cost' => 0],
            );

            $stockBefore = (int) $pw->stock;
            $stockAfter = $stockBefore;

            // Determine stock change direction
            $inTypes = ['in', 'transfer_in', 'return_in', 'initial', 'opname_plus', 'release'];
            $outTypes = ['out', 'transfer_out', 'return_out', 'reservation', 'opname_minus'];

            if (in_array($type, $inTypes)) {
                $stockAfter = $stockBefore + abs($quantity);
            } elseif (in_array($type, $outTypes)) {
                $stockAfter = $stockBefore - abs($quantity);
            } elseif ($type === 'adjustment') {
                // quantity can be + or - for adjustment
                $stockAfter = $stockBefore + $quantity;
            } elseif ($type === 'opname') {
                // opname: quantity IS the final actual stock
                $stockAfter = abs($quantity);
            }

            // Validate non-negative
            if ($stockAfter < 0) {
                throw new \RuntimeException('Stok tidak mencukupi! Stok saat ini: ' . $stockBefore . ', dibutuhkan: ' . abs($quantity));
            }

            // Update average cost for incoming stock
            if (in_array($type, $inTypes) && $unitCost > 0) {
                $totalCost = ($pw->average_cost * $pw->stock) + ($unitCost * abs($quantity));
                $newStock = $stockAfter;
                $pw->average_cost = $newStock > 0 ? round($totalCost / $newStock, 2) : 0;
            }

            $pw->stock = $stockAfter;
            $pw->save();

            // Create mutation record
            $mutation = StockMutation::create([
                'product_id' => $productId,
                'variant_id' => $variantId,
                'warehouse_id' => $warehouseId,
                'user_id' => $userId,
                'type' => $type,
                'quantity' => $stockAfter - $stockBefore,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'unit_cost' => $unitCost ?: $pw->average_cost,
                'reference' => $reference,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes,
            ]);

            return $mutation;
        });
    }

    /**
     * Transfer stock between warehouses.
     */
    public function transferStock(int $productId, int $fromWarehouseId, int $toWarehouseId, float $quantity, int $userId, ?string $notes = null): array
    {
        return DB::transaction(function () use ($productId, $fromWarehouseId, $toWarehouseId, $quantity, $userId, $notes) {
            $out = $this->adjustStock(
                $productId, $fromWarehouseId, $userId, $quantity, 'transfer_out',
                "Transfer ke gudang #{$toWarehouseId}", $notes, 'transfer', null
            );
            $in = $this->adjustStock(
                $productId, $toWarehouseId, $userId, $quantity, 'transfer_in',
                "Transfer dari gudang #{$fromWarehouseId}", $notes, 'transfer', null
            );
            // Link references
            $out->update(['reference_id' => $in->id]);
            $in->update(['reference_id' => $out->id]);

            return ['out' => $out, 'in' => $in];
        });
    }

    /**
     * Process transaction items (called when POS transaction is completed).
     */
    public function processTransactionItems(array $items, int $warehouseId, int $userId): void
    {
        foreach ($items as $item) {
            $productId = $item['product_id'] ?? $item['id'];
            $qty = $item['quantity'] ?? 1;

            $this->adjustStock(
                $productId, $warehouseId, $userId, $qty, 'out',
                "Transaksi: {$item['invoice_number']} - {$item['name']}",
                null, 'transaction', $item['transaction_id'] ?? null
            );
        }
    }

    /**
     * Process goods receipt from purchase order.
     */
    public function processGoodsReceipt(int $purchaseOrderId, array $receivedItems, int $userId): void
    {
        foreach ($receivedItems as $item) {
            $this->adjustStock(
                $item['product_id'],
                $item['warehouse_id'],
                $userId,
                $item['quantity'],
                'in',
                "PO #{$purchaseOrderId} - Penerimaan",
                null,
                'purchase_order',
                $purchaseOrderId,
                $item['variant_id'] ?? null,
                $item['unit_cost'] ?? 0
            );
        }
    }

    /**
     * Calculate average cost for a product.
     */
    public function getCostOfGoodsSold(int $productId, ?int $warehouseId = null, string $method = 'average'): float
    {
        if ($method === 'average') {
            $q = ProductWarehouse::where('product_id', $productId);
            if ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            }
            $avg = $q->avg('average_cost');
            return (float) ($avg ?: 0);
        }

        // FIFO: earliest cost from stock mutations
        if ($method === 'fifo') {
            $q = StockMutation::where('product_id', $productId)
                ->where('type', 'in')
                ->where('quantity', '>', 0)
                ->orderBy('created_at');
            if ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            }
            $first = $q->first();
            return $first ? (float) $first->unit_cost : 0;
        }

        // LIFO: latest cost
        if ($method === 'lifo') {
            $q = StockMutation::where('product_id', $productId)
                ->where('type', 'in')
                ->where('quantity', '>', 0)
                ->orderByDesc('created_at');
            if ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            }
            $last = $q->first();
            return $last ? (float) $last->unit_cost : 0;
        }

        return 0;
    }

    /**
     * Total inventory valuation.
     */
    public function getProductValuation(?int $warehouseId = null): float
    {
        $q = ProductWarehouse::query();
        if ($warehouseId) {
            $q->where('warehouse_id', $warehouseId);
        }
        return (float) $q->get()->sum(fn ($pw) => $pw->stock * $pw->average_cost);
    }

    /**
     * Top selling products.
     */
    public function getTopSelling(string $startDate, string $endDate, int $limit = 10): array
    {
        return TransactionItem::selectRaw('product_id, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue')
            ->whereHas('transaction', fn ($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->with('product')
            ->get()
            ->toArray();
    }

    /**
     * Slow-moving products (low sales in X days).
     */
    public function getSlowMovingProducts(int $days = 90, int $limit = 20): array
    {
        $cutoff = now()->subDays($days);

        $sold = TransactionItem::selectRaw('product_id, SUM(quantity) as total_sold')
            ->whereHas('transaction', fn ($q) => $q->where('created_at', '>=', $cutoff))
            ->groupBy('product_id')
            ->pluck('total_sold', 'product_id');

        return Product::whereNotIn('id', $sold->filter(fn ($v) => $v >= 5)->keys())
            ->with('productWarehouses.warehouse')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Products below minimum stock alert.
     */
    public function checkMinStockAlerts(): array
    {
        return ProductWarehouse::with(['product', 'warehouse'])
            ->whereRaw('stock <= min_stock')
            ->get()
            ->toArray();
    }

    /**
     * Stock movement chart data (last N days).
     */
    public function getStockMovementChart(int $productId, int $days = 30): array
    {
        $cutoff = now()->subDays($days);

        $mutations = StockMutation::where('product_id', $productId)
            ->where('created_at', '>=', $cutoff)
            ->orderBy('created_at')
            ->get()
            ->groupBy(fn ($m) => $m->created_at->format('Y-m-d'));

        $result = [];
        $runningStock = null;

        for ($i = $days; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dayMutations = $mutations->get($date, collect());
            $in = $dayMutations->sum(fn ($m) => in_array($m->type, ['in', 'transfer_in', 'return_in', 'initial']) ? $m->quantity : 0);
            $out = $dayMutations->sum(fn ($m) => in_array($m->type, ['out', 'transfer_out', 'return_out']) ? abs($m->quantity) : 0);

            if ($runningStock === null && $dayMutations->isNotEmpty()) {
                $runningStock = $dayMutations->last()->stock_after;
            }

            $result[] = [
                'date' => $date,
                'in' => (float) $in,
                'out' => (float) $out,
            ];
        }

        return $result;
    }

    /**
     * Warehouse statistics.
     */
    public function getWarehouseStats(int $warehouseId): array
    {
        $products = ProductWarehouse::where('warehouse_id', $warehouseId);
        $totalItems = $products->count();
        $totalStock = (int) $products->sum('stock');
        $totalValue = (float) $products->get()->sum(fn ($pw) => $pw->stock * $pw->average_cost);
        $lowStockCount = (clone $products)->whereRaw('stock <= min_stock')->count();

        return [
            'total_products' => $totalItems,
            'total_stock' => $totalStock,
            'total_value' => $totalValue,
            'low_stock_count' => $lowStockCount,
        ];
    }

    /**
     * Near expiry products.
     */
    public function getNearExpiryProducts(int $days = 30): array
    {
        return \App\Models\ProductExpiry::with(['product', 'warehouse'])
            ->nearExpiry($days)
            ->orderBy('expiry_date')
            ->get()
            ->toArray();
    }

    /**
     * Batch update stock for multiple products (e.g., from CSV import).
     */
    public function batchUpdateStock(array $items, int $warehouseId, int $userId): array
    {
        $results = [];
        foreach ($items as $item) {
            try {
                $m = $this->adjustStock(
                    $item['product_id'], $warehouseId, $userId,
                    $item['quantity'], $item['type'] ?? 'adjustment',
                    $item['reference'] ?? 'Batch update',
                    $item['notes'] ?? null
                );
                $results[] = ['success' => true, 'product_id' => $item['product_id'], 'mutation_id' => $m->id];
            } catch (\Exception $e) {
                $results[] = ['success' => false, 'product_id' => $item['product_id'], 'error' => $e->getMessage()];
            }
        }
        return $results;
    }

    /**
     * Dashboard summary for inventory.
     */
    public function getDashboardSummary(): array
    {
        $totalProducts = Product::count();
        $totalStockValue = (float) ProductWarehouse::get()->sum(fn ($pw) => $pw->stock * $pw->average_cost);
        $lowStockCount = ProductWarehouse::whereRaw('stock <= min_stock')->count();
        $totalMutations = StockMutation::today()->count();
        $nearExpiry = \App\Models\ProductExpiry::nearExpiry(30)->sum('quantity');

        return [
            'total_products' => $totalProducts,
            'total_stock_value' => $totalStockValue,
            'low_stock_count' => $lowStockCount,
            'today_mutations' => $totalMutations,
            'near_expiry_qty' => $nearExpiry,
            'warehouse_count' => \App\Models\Warehouse::active()->count(),
            'supplier_count' => \App\Models\Supplier::active()->count(),
        ];
    }
}
