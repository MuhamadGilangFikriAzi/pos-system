<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMutation extends Model
{
    protected $fillable = [
        'product_id', 'variant_id', 'warehouse_id', 'user_id',
        'type', 'quantity', 'stock_before', 'stock_after', 'unit_cost',
        'reference', 'reference_type', 'reference_id', 'notes',
    ];

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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeType($q, $type)
    {
        return $q->where('type', $type);
    }

    public function scopeToday($q)
    {
        return $q->whereDate('created_at', today());
    }

    public function scopeByWarehouse($q, $id)
    {
        return $q->where('warehouse_id', $id);
    }

    public function scopeByProduct($q, $id)
    {
        return $q->where('product_id', $id);
    }
}
