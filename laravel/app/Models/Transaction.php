<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Transaction extends Model
{
    protected $fillable = ["invoice_number", "user_id", "total", "payment", "change"];
    public function items(): HasMany { return $this->hasMany(TransactionItem::class); }
    public function user(): BelongsTo { return $this->belongsTo(\App\Models\User::class); }
}