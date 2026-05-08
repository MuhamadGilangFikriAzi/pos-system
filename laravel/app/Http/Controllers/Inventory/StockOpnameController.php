<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductWarehouse;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class StockOpnameController extends Controller
{
    protected InventoryService $inv;

    public function __construct(InventoryService $inv)
    {
        $this->inv = $inv;
    }

    public function index()
    {
        $opnames = StockOpname::with(['warehouse', 'user'])
            ->orderByDesc('created_at')
            ->paginate(20);
        return view('inventory.opname.index', compact('opnames'));
    }

    public function create()
    {
        $warehouses = Warehouse::active()->orderBy('name')->get();
        return view('inventory.opname.form', compact('warehouses'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'notes' => 'nullable|string',
            'opname_date' => 'required|date',
        ]);

        $opname = StockOpname::create([
            'warehouse_id' => $data['warehouse_id'],
            'user_id' => auth()->id(),
            'status' => 'in_progress',
            'notes' => $data['notes'] ?? null,
            'opname_date' => $data['opname_date'],
        ]);

        // Auto-populate items from current stock
        $products = ProductWarehouse::with('product')
            ->where('warehouse_id', $data['warehouse_id'])
            ->where('stock', '>', 0)
            ->get();

        foreach ($products as $pw) {
            $opname->items()->create([
                'product_id' => $pw->product_id,
                'variant_id' => null,
                'system_stock' => $pw->stock,
                'actual_stock' => $pw->stock, // default same
                'difference' => 0,
                'unit_cost' => $pw->average_cost,
            ]);
        }

        return redirect()->route('inventory.opname.show', $opname->id)
            ->with('success', 'Stock opname dibuat. Silakan isi stock aktual.');
    }

    public function show(StockOpname $stockOpname)
    {
        $stockOpname->load(['warehouse', 'user', 'items.product', 'items.variant']);
        return view('inventory.opname.show', ['opname' => $stockOpname]);
    }

    public function edit(StockOpname $stockOpname)
    {
        if ($stockOpname->status === 'completed') {
            return back()->with('error', 'Opname sudah selesai');
        }
        $stockOpname->load(['warehouse', 'items.product', 'items.variant']);
        return view('inventory.opname.show', ['opname' => $stockOpname, 'edit' => true]);
    }

    public function update(Request $r, StockOpname $stockOpname)
    {
        if ($stockOpname->status === 'completed') {
            return back()->with('error', 'Opname sudah selesai');
        }

        $data = $r->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:stock_opname_items,id',
            'items.*.actual_stock' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        foreach ($data['items'] as $item) {
            $oi = StockOpnameItem::findOrFail($item['id']);
            $diff = (float) $item['actual_stock'] - $oi->system_stock;
            $oi->update([
                'actual_stock' => $item['actual_stock'],
                'difference' => $diff,
                'notes' => $item['notes'] ?? $oi->notes,
            ]);
        }

        return redirect()->route('inventory.opname.show', $stockOpname->id)
            ->with('success', 'Data opname diperbarui');
    }

    public function complete(Request $r, StockOpname $stockOpname)
    {
        if ($stockOpname->status === 'completed') {
            return back()->with('error', 'Opname sudah selesai');
        }

        $stockOpname->load('items');

        foreach ($stockOpname->items as $item) {
            $diff = $item->actual_stock - $item->system_stock;
            if ($diff == 0) continue;

            // Apply correction via inventory service
            $this->inv->adjustStock(
                $item->product_id,
                $stockOpname->warehouse_id,
                auth()->id(),
                $item->actual_stock,
                'opname',
                "Opname #{$stockOpname->opname_number}",
                $item->notes ?? null,
                'opname',
                $stockOpname->id,
                $item->variant_id,
            );
        }

        $stockOpname->update(['status' => 'completed']);

        return redirect()->route('inventory.opname.index')
            ->with('success', 'Opname selesai. Stock telah disesuaikan.');
    }

    public function cancel(StockOpname $stockOpname)
    {
        $stockOpname->update(['status' => 'cancelled']);
        return redirect()->route('inventory.opname.index')->with('success', 'Opname dibatalkan');
    }
}
