<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $pos = PurchaseOrder::with(['supplier', 'warehouse', 'user', 'items'])
            ->orderByDesc('created_at')
            ->paginate(20);
        return view('inventory.purchase-orders.index', compact('pos'));
    }

    public function create()
    {
        $suppliers = Supplier::active()->orderBy('name')->get();
        $warehouses = Warehouse::active()->orderBy('name')->get();
        $products = Product::orderBy('name')->get(['id', 'name', 'code', 'purchase_price', 'selling_price']);
        return view('inventory.purchase-orders.form', compact('suppliers', 'warehouses', 'products'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'notes' => 'nullable|string',
            'tax' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        $subtotal = 0;
        $poItems = [];
        foreach ($data['items'] as $item) {
            $itemSubtotal = $item['quantity'] * $item['price'];
            $subtotal += $itemSubtotal;
            $poItems[] = new PurchaseOrderItem([
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'] ?? null,
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'subtotal' => $itemSubtotal,
            ]);
        }

        $tax = (float) ($data['tax'] ?? 0);
        $total = $subtotal + ($subtotal * $tax / 100);

        $po = PurchaseOrder::create([
            'supplier_id' => $data['supplier_id'],
            'warehouse_id' => $data['warehouse_id'],
            'user_id' => auth()->id(),
            'status' => 'submitted',
            'notes' => $data['notes'] ?? null,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
        ]);

        $po->items()->saveMany($poItems);

        return redirect()->route('inventory.purchase-orders.show', $po->id)
            ->with('success', 'Purchase Order berhasil dibuat');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'warehouse', 'user', 'items.product', 'items.variant']);
        return view('inventory.purchase-orders.show', ['po' => $purchaseOrder]);
    }

    public function receive(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['items.product', 'items.variant']);
        return view('inventory.purchase-orders.receive', ['po' => $purchaseOrder]);
    }

    public function receiveStore(Request $r, PurchaseOrder $purchaseOrder)
    {
        $app = app(\App\Services\InventoryService::class);

        $data = $r->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:purchase_order_items,id',
            'items.*.receive_qty' => 'required|integer|min:0',
        ]);

        foreach ($data['items'] as $item) {
            $poItem = PurchaseOrderItem::findOrFail($item['id']);
            $receiveQty = (int) $item['receive_qty'];
            if ($receiveQty <= 0) continue;

            $app->adjustStock(
                $poItem->product_id,
                $purchaseOrder->warehouse_id,
                auth()->id(),
                $receiveQty,
                'in',
                "PO #{$purchaseOrder->po_number} - Penerimaan",
                null,
                'purchase_order',
                $purchaseOrder->id,
                $poItem->variant_id,
                $poItem->price,
            );

            $poItem->increment('received_qty', $receiveQty);
        }

        // Update PO status
        $allReceived = $purchaseOrder->items->every(fn ($i) => $i->received_qty >= $i->quantity);
        $anyReceived = $purchaseOrder->items->sum('received_qty') > 0;
        $purchaseOrder->update([
            'status' => $allReceived ? 'received' : ($anyReceived ? 'partial' : 'submitted'),
            'received_at' => $allReceived ? now() : $purchaseOrder->received_at,
        ]);

        return redirect()->route('inventory.purchase-orders.show', $purchaseOrder->id)
            ->with('success', 'Barang berhasil diterima');
    }

    public function cancel(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status === 'received') {
            return back()->with('error', 'PO sudah diterima, tidak bisa dibatalkan');
        }
        $purchaseOrder->update(['status' => 'cancelled']);
        return redirect()->route('inventory.purchase-orders.index')->with('success', 'PO dibatalkan');
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if (!in_array($purchaseOrder->status, ['draft', 'cancelled'])) {
            return back()->with('error', 'Hanya PO draft/cancelled yang bisa dihapus');
        }
        $purchaseOrder->items()->delete();
        $purchaseOrder->delete();
        return redirect()->route('inventory.purchase-orders.index')->with('success', 'PO dihapus');
    }

    // AJAX: get product info + variants
    public function getProduct(Product $product)
    {
        $product->load('variants');
        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'code' => $product->code,
            'purchase_price' => $product->purchase_price,
            'selling_price' => $product->selling_price,
            'unit' => $product->unit,
            'variants' => $product->variants->map(fn ($v) => [
                'id' => $v->id,
                'name' => $v->name,
                'sku' => $v->sku,
            ]),
        ]);
    }
}
