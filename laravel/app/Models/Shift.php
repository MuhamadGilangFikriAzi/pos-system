<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    protected $fillable = [
        'user_id', 'outlet_id', 'opened_at', 'closed_at',
        'cash_initial', 'cash_expected', 'cash_actual', 'cash_difference',
        'total_sales', 'total_cash_sales', 'total_non_cash',
        'transaction_count', 'status', 'notes'
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'cash_initial' => 'decimal:2',
        'cash_expected' => 'decimal:2',
        'cash_actual' => 'decimal:2',
        'cash_difference' => 'decimal:2',
        'total_sales' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Hitung ulang total dari transaksi yang sudah completed di shift ini.
     */
    public function recalculate(): void
    {
        $transactions = $this->transactions()->where('status', 'completed')->get();

        $this->total_sales = $transactions->sum('grand_total');
        $this->total_cash_sales = $transactions->where('payment_method', 'cash')->sum('grand_total');
        $this->total_non_cash = $transactions->where('payment_method', '!=', 'cash')->sum('grand_total');
        $this->transaction_count = $transactions->count();

        // Uang yang seharusnya ada = saldo awal + total penjualan tunai
        $this->cash_expected = $this->cash_initial + $this->total_cash_sales;

        if ($this->cash_actual > 0) {
            $this->cash_difference = $this->cash_actual - $this->cash_expected;
        }

        $this->save();
    }

    /**
     * Tutup shift.
     */
    public function close(float $cashActual, ?string $notes = null): void
    {
        $this->recalculate();
        $this->cash_actual = $cashActual;
        $this->cash_difference = $cashActual - $this->cash_expected;
        $this->closed_at = now();
        $this->status = 'closed';
        $this->notes = $notes;
        $this->save();
    }

    /**
     * Cek apakah shift masih open untuk user tertentu.
     */
    public static function getActiveShift(int $userId, int $outletId = 1): ?self
    {
        return self::where('user_id', $userId)
            ->where('outlet_id', $outletId)
            ->where('status', 'open')
            ->latest('opened_at')
            ->first();
    }
}
