<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id', 'name', 'sku', 'barcode',
        'price_modifier', 'stock', 'stock_sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'price_modifier' => 'decimal:2',
            'stock' => 'integer',
            'stock_sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $variant) {
            if (empty($variant->sku)) {
                $prefix = 'VAR-' . str_pad((string) $variant->product_id, 4, '0', STR_PAD_LEFT) . '-';
                $max = static::where('sku', 'like', $prefix . '%')->count();
                $variant->sku = $prefix . str_pad((string) ($max + 1), 3, '0', STR_PAD_LEFT);
            }
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function stockMutations()
    {
        return $this->hasMany(StockMutation::class, 'variant_id');
    }
}
