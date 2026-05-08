<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'code', 'name', 'category_id', 'purchase_price', 'selling_price',
        'stock', 'unit', 'description', 'barcode', 'min_stock',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'stock' => 'integer',
            'min_stock' => 'integer',
        ];
    }

    // === RELATIONS ===

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'product_warehouse')
            ->withPivot('stock', 'min_stock', 'average_cost')
            ->withTimestamps();
    }

    public function productWarehouses()
    {
        return $this->hasMany(ProductWarehouse::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function stockMutations()
    {
        return $this->hasMany(StockMutation::class);
    }

    public function stockOpnameItems()
    {
        return $this->hasMany(StockOpnameItem::class);
    }

    public function productExpiries()
    {
        return $this->hasMany(ProductExpiry::class);
    }

    // === ACCESSORS ===

    public function getTotalStockAttribute()
    {
        return $this->productWarehouses()->sum('stock');
    }

    public function getStockValuationAttribute()
    {
        return $this->total_stock * ($this->purchase_price ?: 0);
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->total_stock <= $this->min_stock;
    }

    // === SCOPES ===

    public function scopeActive($q)
    {
        return $q->whereNull('deleted_at');
    }

    public function scopeLowStock($q)
    {
        return $q->whereRaw('(SELECT COALESCE(SUM(stock), 0) FROM product_warehouse WHERE product_id = products.id) <= products.min_stock');
    }

    public function scopeSearch($q, $term)
    {
        return $q->where(function ($qq) use ($term) {
            $qq->where('name', 'like', "%{$term}%")
               ->orWhere('code', 'like', "%{$term}%")
               ->orWhere('barcode', 'like', "%{$term}%");
        });
    }
}