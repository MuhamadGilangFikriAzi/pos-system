<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::orderBy('name')->paginate(20);
        return view('inventory.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('inventory.suppliers.form');
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name' => 'required|string|max:200',
            'code' => 'nullable|string|max:20|unique:suppliers',
            'contact_person' => 'nullable|string|max:200',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        $data['is_active'] = $r->boolean('is_active', true);

        Supplier::create($data);
        return redirect()->route('inventory.suppliers.index')->with('success', 'Supplier berhasil dibuat');
    }

    public function edit(Supplier $supplier)
    {
        return view('inventory.suppliers.form', compact('supplier'));
    }

    public function update(Request $r, Supplier $supplier)
    {
        $data = $r->validate([
            'name' => 'required|string|max:200',
            'code' => 'nullable|string|max:20|unique:suppliers,code,' . $supplier->id,
            'contact_person' => 'nullable|string|max:200',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        $data['is_active'] = $r->boolean('is_active', true);

        $supplier->update($data);
        return redirect()->route('inventory.suppliers.index')->with('success', 'Supplier diperbarui');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->purchaseOrders()->count() > 0) {
            return back()->with('error', 'Tidak bisa hapus supplier yang memiliki purchase order');
        }
        $supplier->delete();
        return redirect()->route('inventory.suppliers.index')->with('success', 'Supplier dihapus');
    }
}
