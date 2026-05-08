<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMutation;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class StockMutationController extends Controller
{
    protected InventoryService $inv;

    public function __construct(InventoryService $inv)
    {
        $this->inv = $inv;
    }

    public function index(Request $r)
    {
        $q = StockMutation::with(['product', 'warehouse', 'user'])
            ->orderByDesc('created_at');

        if ($r->filled('type')) {
            $q->where('type', $r->type);
        }
        if ($r->filled('warehouse_id')) {
            $q->where('warehouse_id', $r->warehouse_id);
        }
        if ($r->filled('product_id')) {
            $q->where('product_id', $r->product_id);
        }
        if ($r->filled('start_date')) {
            $q->whereDate('created_at', '>=', $r->start_date);
        }
        if ($r->filled('end_date')) {
            $q->whereDate('created_at', '<=', $r->end_date);
        }

        $mutations = $q->paginate(20);
        $types = ['in', 'out', 'adjustment', 'transfer_in', 'transfer_out', 'opname', 'initial', 'return_in', 'return_out'];
        $warehouses = Warehouse::active()->orderBy('name')->get();

        return view('inventory.stock.index', compact('mutations', 'types', 'warehouses'));
    }

    public function createIn()
    {
        $products = Product::orderBy('name')->get(['id', 'name', 'code', 'unit']);
        $warehouses = Warehouse::active()->orderBy('name')->get();
        return view('inventory.stock.in', compact('products', 'warehouses'));
    }

    public function storeIn(Request $r)
    {
        $data = $r->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|numeric|min:0.01',
            'unit_cost' => 'nullable|numeric|min:0',
            'reference' => 'nullable|string|max:200',
            'notes' => 'nullable|string',
        ]);

        try {
            $this->inv->adjustStock(
                $data['product_id'], $data['warehouse_id'], auth()->id(),
                $data['quantity'], 'in',
                $data['reference'] ?? 'Stock Masuk',
                $data['notes'] ?? null, null, null, null,
                $data['unit_cost'] ?? 0
            );
            return redirect()->route('inventory.stock.index')->with('success', 'Stock masuk berhasil');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function createOut()
    {
        $products = Product::orderBy('name')->get(['id', 'name', 'code', 'unit']);
        $warehouses = Warehouse::active()->orderBy('name')->get();
        return view('inventory.stock.out', compact('products', 'warehouses'));
    }

    public function storeOut(Request $r)
    {
        $data = $r->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|numeric|min:0.01',
            'reference' => 'nullable|string|max:200',
            'notes' => 'nullable|string',
        ]);

        try {
            $this->inv->adjustStock(
                $data['product_id'], $data['warehouse_id'], auth()->id(),
                $data['quantity'], 'out',
                $data['reference'] ?? 'Stock Keluar',
                $data['notes'] ?? null
            );
            return redirect()->route('inventory.stock.index')->with('success', 'Stock keluar berhasil');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function createAdjustment()
    {
        $products = Product::orderBy('name')->get(['id', 'name', 'code', 'unit']);
        $warehouses = Warehouse::active()->orderBy('name')->get();
        return view('inventory.stock.adjust', compact('products', 'warehouses'));
    }

    public function storeAdjustment(Request $r)
    {
        $data = $r->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|numeric', // can be + or -
            'notes' => 'required|string',
        ]);

        try {
            $this->inv->adjustStock(
                $data['product_id'], $data['warehouse_id'], auth()->id(),
                $data['quantity'], 'adjustment',
                'Penyesuaian Stock',
                $data['notes']
            );
            return redirect()->route('inventory.stock.index')->with('success', 'Penyesuaian stock berhasil');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function createTransfer()
    {
        $products = Product::orderBy('name')->get(['id', 'name', 'code', 'unit']);
        $warehouses = Warehouse::active()->orderBy('name')->get();
        return view('inventory.stock.transfer', compact('products', 'warehouses'));
    }

    public function storeTransfer(Request $r)
    {
        $data = $r->validate([
            'product_id' => 'required|exists:products,id',
            'from_warehouse_id' => 'required|exists:warehouses,id|different:to_warehouse_id',
            'to_warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ]);

        try {
            $this->inv->transferStock(
                $data['product_id'], $data['from_warehouse_id'], $data['to_warehouse_id'],
                $data['quantity'], auth()->id(), $data['notes'] ?? null
            );
            return redirect()->route('inventory.stock.index')->with('success', 'Transfer stock berhasil');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show(StockMutation $stockMutation)
    {
        $stockMutation->load(['product', 'warehouse', 'user', 'variant']);
        return view('inventory.stock.show', ['mutation' => $stockMutation]);
    }

    public function getProductStock(Product $product)
    {
        $stocks = $this->inv->getStockAllLocations($product->id);
        return response()->json([
            'product' => $product->only(['id', 'name', 'code', 'purchase_price', 'selling_price', 'unit', 'min_stock']),
            'stocks' => $stocks,
        ]);
    }

    public function chart(Request $r)
    {
        $productId = $r->input('product_id');
        $days = (int) $r->input('days', 30);
        if (!$productId) {
            return response()->json([]);
        }
        $data = $this->inv->getStockMovementChart($productId, $days);
        return response()->json($data);
    }

    public function export(Request $r)
    {
        $q = StockMutation::with(['product', 'warehouse', 'user'])->orderByDesc('created_at');

        if ($r->filled('type')) $q->where('type', $r->type);
        if ($r->filled('warehouse_id')) $q->where('warehouse_id', $r->warehouse_id);
        if ($r->filled('start_date')) $q->whereDate('created_at', '>=', $r->start_date);
        if ($r->filled('end_date')) $q->whereDate('created_at', '<=', $r->end_date);

        $mutations = $q->get();
        $filename = 'stock-mutations-' . now()->format('Ymd') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($mutations) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tanggal', 'Produk', 'Tipe', 'Qty', 'Stok Awal', 'Stok Akhir', 'Gudang', 'User', 'Referensi', 'Catatan', 'Harga Satuan']);
            foreach ($mutations as $m) {
                fputcsv($handle, [
                    $m->created_at->format('Y-m-d H:i'),
                    $m->product->name ?? '-',
                    $m->type,
                    (float) $m->quantity,
                    (float) $m->stock_before,
                    (float) $m->stock_after,
                    $m->warehouse->name ?? '-',
                    $m->user->name ?? '-',
                    $m->reference ?? '',
                    $m->notes ?? '',
                    (float) $m->unit_cost,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
