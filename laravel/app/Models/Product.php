<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Product extends Model
{
    protected $fillable = ["code", "name", "category_id", "purchase_price", "selling_price", "stock", "unit", "description"];
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
}