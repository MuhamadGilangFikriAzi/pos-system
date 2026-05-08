<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Support\Collection;

class DiscountCalculator
{
    /**
     * Hitung total keranjang dengan diskon per item, diskon global, dan pajak.
     *
     * Rumus per item:
     *   subtotal_item = harga_satuan × qty
     *   diskon_item = subtotal_item × (diskon_percent / 100)   (jika persen)
     *   diskon_item = min(diskon_amount, subtotal_item)         (jika nominal)
     *   subtotal_setelah_diskon = subtotal_item - diskon_item
     *
     * Rumus transaksi:
     *   subtotal_transaksi = Σ subtotal_setelah_diskon (semua item)
     *   diskon_global = subtotal_transaksi × (global_discount_percent / 100)   (jika persen)
     *   diskon_global = min(global_discount_amount, subtotal_transaksi)         (jika nominal)
     *   setelah_diskon_global = subtotal_transaksi - diskon_global
     *   pajak = setelah_diskon_global × (tax_percent / 100)
     *   grand_total = setelah_diskon_global + pajak
     *
     * @param Collection $items  Collection of items, each: {product_id, quantity, price, discount_percent, discount_amount, is_persen}
     * @param float $globalDiscountPercent
     * @param float $globalDiscountAmount  (if > 0, used instead of percent)
     * @param float $taxPercent
     * @return array  { items: [...], subtotal, global_discount, subtotal_after_global, tax, grand_total }
     */
    public function calculate(
        Collection $items,
        float $globalDiscountPercent = 0,
        float $globalDiscountAmount = 0,
        float $taxPercent = 0
    ): array {
        // 1. Hitung per item
        $calculatedItems = $items->map(function ($item) {
            $subtotal = (float) $item['price'] * (int) $item['quantity'];
            $discountPercent = (float) ($item['discount_percent'] ?? 0);
            $discountAmount = (float) ($item['discount_amount'] ?? 0);
            $isPercent = (bool) ($item['is_percent'] ?? ($discountPercent > 0 && $discountAmount == 0));

            $discountValue = 0;
            if ($discountPercent > 0 && $isPercent) {
                $discountValue = round($subtotal * ($discountPercent / 100), 2);
            } elseif ($discountAmount > 0) {
                $discountValue = min($discountAmount, $subtotal);
            }

            // Apply promo: Buy 1 Get 1
            if (!empty($item['promo_bogo']) && $item['quantity'] >= 2) {
                // Item paling murah gratis
                $discountValue += (float) $item['price']; // 1 gratis
            }

            // Validasi: diskon tidak melebihi subtotal
            $discountValue = min($discountValue, $subtotal);

            $priceAfterDiscount = $subtotal - $discountValue;

            return [
                'product_id' => $item['product_id'],
                'quantity' => (int) $item['quantity'],
                'price' => (float) $item['price'],
                'subtotal' => $subtotal,
                'discount_percent' => $isPercent ? $discountPercent : 0,
                'discount_amount' => $discountValue,
                'price_after_discount' => $priceAfterDiscount,
            ];
        });

        // 2. Subtotal transaksi (setelah diskon item)
        $subtotal = round($calculatedItems->sum('price_after_discount'), 2);

        // 3. Diskon global transaksi
        $globalDiscount = 0;
        if ($globalDiscountPercent > 0) {
            $globalDiscount = round($subtotal * ($globalDiscountPercent / 100), 2);
        } elseif ($globalDiscountAmount > 0) {
            $globalDiscount = min($globalDiscountAmount, $subtotal);
        }
        $globalDiscount = min($globalDiscount, $subtotal);

        $afterGlobalDiscount = round($subtotal - $globalDiscount, 2);

        // 4. Pajak (setelah diskon global)
        $tax = 0;
        if ($taxPercent > 0) {
            $tax = round($afterGlobalDiscount * ($taxPercent / 100), 2);
        }

        // 5. Grand total
        $grandTotal = round($afterGlobalDiscount + $tax, 2);

        return [
            'items' => $calculatedItems,
            'subtotal' => $subtotal,
            'global_discount_percent' => $globalDiscountPercent,
            'global_discount_amount' => $globalDiscount,
            'subtotal_after_global' => $afterGlobalDiscount,
            'tax_percent' => $taxPercent,
            'tax_amount' => $tax,
            'grand_total' => $grandTotal,
        ];
    }

    /**
     * Simpan transaksi ke database dengan data diskon lengkap.
     */
    public function saveTransaction(
        Collection $items,
        float $payment,
        float $globalDiscountPercent = 0,
        float $globalDiscountAmount = 0,
        float $taxPercent = 0,
        ?string $voucherCode = null
    ): Transaction {
        $result = $this->calculate(
            collect($items),
            $globalDiscountPercent,
            $globalDiscountAmount,
            $taxPercent
        );

        if ($payment < $result['grand_total']) {
            throw new \Exception('Pembayaran kurang. Total: Rp' . number_format($result['grand_total'], 0, ',', '.'));
        }

        $totalItemPrices = collect($items)->sum(fn($i) => (float)$i['price'] * (int)$i['quantity']);

        $todayCount = Transaction::whereDate('created_at', today())->count();
        $invoice = 'INV-' . now()->format('Ymd') . '-' . str_pad($todayCount + 1, 4, '0', STR_PAD_LEFT);

        $transaction = Transaction::create([
            'invoice_number' => $invoice,
            'user_id' => auth()->id(),
            'total' => $totalItemPrices, // total sebelum diskon (harga kotor)
            'subtotal' => $result['subtotal'],
            'discount_percent' => $result['global_discount_percent'],
            'discount_amount' => $result['global_discount_amount'],
            'tax_percent' => $result['tax_percent'],
            'tax_amount' => $result['tax_amount'],
            'grand_total' => $result['grand_total'],
            'payment' => $payment,
            'change' => round($payment - $result['grand_total'], 2),
            'voucher_code' => $voucherCode,
        ]);

        foreach ($result['items'] as $item) {
            $product = Product::findOrFail($item['product_id']);
            $qty = $item['quantity'];

            $transaction->items()->create([
                'product_id' => $product->id,
                'quantity' => $qty,
                'price' => $item['price'],
                'subtotal' => $item['subtotal'],
                'discount_percent' => $item['discount_percent'],
                'discount_amount' => $item['discount_amount'],
                'price_after_discount' => $item['price_after_discount'],
            ]);

            $product->decrement('stock', $qty);
        }

        return $transaction;
    }
}
