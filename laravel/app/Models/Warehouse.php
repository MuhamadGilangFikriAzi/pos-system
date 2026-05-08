<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $fillable = ['name', 'code', 'address', 'phone', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_warehouse')
            ->withPivot('stock', 'min_stock', 'average_cost')
            ->withTimestamps();
    }

    public function stockMutations()
    {
        return $this->hasMany(StockMutation::class);
    }

    public function stockOpnames()
    {
        return $this->hasMany(StockOpname::class);
    }

    public function productExpiries()
    {
        return $this->hasMany(ProductExpiry::class);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
