<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductExpiry extends Model
{
    protected $fillable = [
        'product_id', 'variant_id', 'warehouse_id',
        'batch_number', 'quantity', 'expiry_date',
    ];

    protected function casts(): array
    {
        return ['expiry_date' => 'date'];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function scopeNearExpiry($q, $days = 30)
    {
        return $q->where('expiry_date', '>=', today())
            ->where('expiry_date', '<=', today()->addDays($days))
            ->where('quantity', '>', 0);
    }

    public function scopeExpired($q)
    {
        return $q->where('expiry_date', '<', today())->where('quantity', '>', 0);
    }
}
