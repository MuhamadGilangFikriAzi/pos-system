<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    public function index(Product $product)
    {
        $variants = $product->variants()->orderBy('stock_sort_order')->get();
        return view('inventory.variants.index', compact('product', 'variants'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:100',
            'sku' => 'nullable|string|max:100|unique:product_variants',
            'barcode' => 'nullable|string|max:100',
            'price_modifier' => 'nullable|numeric',
            'is_active' => 'boolean',
        ]);
        $data['is_active'] = $r->boolean('is_active', true);

        ProductVariant::create($data);
        return back()->with('success', 'Varian produk ditambahkan');
    }

    public function update(Request $r, ProductVariant $productVariant)
    {
        $data = $r->validate([
            'name' => 'required|string|max:100',
            'sku' => 'nullable|string|max:100|unique:product_variants,sku,' . $productVariant->id,
            'barcode' => 'nullable|string|max:100',
            'price_modifier' => 'nullable|numeric',
            'stock_sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);
        $data['is_active'] = $r->boolean('is_active', true);

        $productVariant->update($data);
        return back()->with('success', 'Varian diperbarui');
    }

    public function destroy(ProductVariant $productVariant)
    {
        $productVariant->delete();
        return back()->with('success', 'Varian dihapus');
    }
}
