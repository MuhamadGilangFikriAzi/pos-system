<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMutation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index()
    {
        $mutations = StockMutation::with(['product', 'user'])
            ->latest()
            ->paginate(30);
        return view('pos.stock.index', compact('mutations'));
    }

    public function create()
    {
        $products = Product::orderBy('name')->get();
        return view('pos.stock.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'reference' => 'nullable|string|max:100',
            'note' => 'nullable|string|max:500',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $qty = (int) $validated['quantity'];
        $stockBefore = $product->stock;

        DB::transaction(function () use ($product, $qty, $stockBefore, $validated) {
            if ($validated['type'] === 'in') {
                $product->increment('stock', $qty);
                $stockAfter = $stockBefore + $qty;
            } else {
                if ($product->stock < $qty) {
                    throw new \Exception("Stok tidak mencukupi. Stok saat ini: {$product->stock}");
                }
                $product->decrement('stock', $qty);
                $stockAfter = $stockBefore - $qty;
            }

            StockMutation::create([
                'product_id' => $product->id,
                'type' => $validated['type'],
                'quantity' => $qty,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reference' => $validated['reference'] ?? null,
                'note' => $validated['note'] ?? null,
                'user_id' => auth()->id(),
            ]);
        });

        return redirect()->route('stock.index')
            ->with('success', 'Stok berhasil diperbarui.');
    }

    public function show(Product $product)
    {
        $mutations = StockMutation::where('product_id', $product->id)
            ->with('user')
            ->latest()
            ->paginate(20);
        return view('pos.stock.show', compact('product', 'mutations'));
    }

    // AJAX: get product detail for stock form
    public function getProduct(Product $product)
    {
        return response()->json([
            'stock' => $product->stock,
            'min_stock' => $product->min_stock,
            'name' => $product->name,
        ]);
    }
}
