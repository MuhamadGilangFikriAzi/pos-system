<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::orderBy('name')->paginate(20);
        return view('inventory.warehouses.index', compact('warehouses'));
    }

    public function create()
    {
        return view('inventory.warehouses.form');
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name' => 'required|string|max:200',
            'code' => 'required|string|max:20|unique:warehouses',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:30',
            'is_active' => 'boolean',
        ]);
        $data['is_active'] = $r->boolean('is_active', true);

        Warehouse::create($data);
        return redirect()->route('inventory.warehouses.index')->with('success', 'Gudang berhasil dibuat');
    }

    public function edit(Warehouse $warehouse)
    {
        return view('inventory.warehouses.form', compact('warehouse'));
    }

    public function update(Request $r, Warehouse $warehouse)
    {
        $data = $r->validate([
            'name' => 'required|string|max:200',
            'code' => 'required|string|max:20|unique:warehouses,code,' . $warehouse->id,
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:30',
            'is_active' => 'boolean',
        ]);
        $data['is_active'] = $r->boolean('is_active', true);

        $warehouse->update($data);
        return redirect()->route('inventory.warehouses.index')->with('success', 'Gudang diperbarui');
    }

    public function destroy(Warehouse $warehouse)
    {
        if ($warehouse->products()->count() > 0) {
            return back()->with('error', 'Tidak bisa hapus gudang yang memiliki produk');
        }
        $warehouse->delete();
        return redirect()->route('inventory.warehouses.index')->with('success', 'Gudang dihapus');
    }
}
