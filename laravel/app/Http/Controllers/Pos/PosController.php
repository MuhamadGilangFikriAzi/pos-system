<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Services\DiscountCalculator;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function index()
    {
        $products = Product::where('stock', '>', 0)
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'selling_price', 'purchase_price', 'stock', 'unit']);

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
            ->get(['id', 'code', 'name', 'selling_price', 'purchase_price', 'stock', 'unit'])
            ->values();
    }

    /**
     * API: Hitung total cart dengan diskon (realtime dari frontend).
     * Digunakan untuk preview total sebelum checkout.
     */
    public function calculate(Request $request)
    {
        $data = $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'global_discount_percent' => 'nullable|numeric|min:0|max:100',
            'global_discount_amount' => 'nullable|numeric|min:0',
            'tax_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        $calculator = new DiscountCalculator();
        $result = $calculator->calculate(
            collect($data['items']),
            (float)($data['global_discount_percent'] ?? 0),
            (float)($data['global_discount_amount'] ?? 0),
            (float)($data['tax_percent'] ?? 0)
        );

        return response()->json($result);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'payment' => 'required|numeric|min:0',
            'global_discount_percent' => 'nullable|numeric|min:0|max:100',
            'global_discount_amount' => 'nullable|numeric|min:0',
            'tax_percent' => 'nullable|numeric|min:0|max:100',
            'voucher_code' => 'nullable|string|max:50',
        ]);

        try {
            $calculator = new DiscountCalculator();
            $transaction = $calculator->saveTransaction(
                collect($data['items']),
                (float)$data['payment'],
                (float)($data['global_discount_percent'] ?? 0),
                (float)($data['global_discount_amount'] ?? 0),
                (float)($data['tax_percent'] ?? 0),
                $data['voucher_code'] ?? null
            );

            return response()->json([
                'success' => true,
                'transaction_id' => $transaction->id,
                'grand_total' => $transaction->grand_total,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function receipt(Transaction $transaction)
    {
        $transaction->load('items.product', 'user');
        return view('pos.receipt', compact('transaction'));
    }
}
