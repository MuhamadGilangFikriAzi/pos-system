<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        "invoice_number", "user_id", "shift_id", "outlet_id",
        "total", "subtotal", "discount_percent", "discount_amount",
        "tax_percent", "tax_amount", "grand_total",
        "payment", "change", "payment_method", "status", "voucher_code"
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
    ];

    public function items(): HasMany { return $this->hasMany(TransactionItem::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function shift(): BelongsTo { return $this->belongsTo(Shift::class); }
    public function outlet(): BelongsTo { return $this->belongsTo(Outlet::class); }

    public function scopeByUser($query, $userId) {
        return $query->where('user_id', $userId);
    }

    public function scopeByShift($query, $shiftId) {
        return $query->where('shift_id', $shiftId);
    }

    public function scopeByOutlet($query, $outletId) {
        return $query->where('outlet_id', $outletId);
    }

    public function scopeCompleted($query) {
        return $query->where('status', 'completed');
    }

    public function scopeSuspended($query) {
        return $query->where('status', 'suspended');
    }
}
