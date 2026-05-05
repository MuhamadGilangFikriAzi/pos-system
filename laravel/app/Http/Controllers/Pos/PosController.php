<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;

class PosController extends Controller
{
    public function index()
    {
        // Load products langsung dari backend, bukan via JS fetch
        $products = Product::where('stock', '>', 0)
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'selling_price', 'stock', 'unit']);

        return view('pos.index', compact('products'));
    }

    public function products()
    {
        $search = request('search');
        $query = Product::where('stock', '>', 0);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('name')
            ->get(['id', 'code', 'name', 'selling_price', 'stock', 'unit'])
            ->values();
    }

    public function store()
    {
        $data = request()->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'payment' => 'required|numeric|min:0',
        ]);

        $total = collect($data['items'])->sum(fn($i) => $i['price'] * $i['quantity']);
        $payment = $data['payment'];
        $change = $payment - $total;

        if ($change < 0) {
            return response()->json(['success' => false, 'message' => 'Pembayaran kurang'], 422);
        }

        $todayCount = Transaction::whereDate('created_at', today())->count();
        $invoice = 'INV-' . now()->format('Ymd') . '-' . str_pad($todayCount + 1, 4, '0', STR_PAD_LEFT);

        $transaction = Transaction::create([
            'invoice_number' => $invoice,
            'user_id' => auth()->id(),
            'total' => $total,
            'payment' => $payment,
            'change' => $change,
        ]);

        foreach ($data['items'] as $item) {
            $product = Product::findOrFail($item['product_id']);
            $qty = (int)$item['quantity'];
            $price = (float)$item['price'];

            $transaction->items()->create([
                'product_id' => $product->id,
                'quantity' => $qty,
                'price' => $price,
                'subtotal' => $price * $qty,
            ]);

            $product->decrement('stock', $qty);
        }

        return response()->json([
            'success' => true,
            'transaction_id' => $transaction->id,
        ]);
    }

    public function receipt(Transaction $transaction)
    {
        $transaction->load('items.product', 'user');
        return view('pos.receipt', compact('transaction'));
    }
}
