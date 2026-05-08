<?php

namespace App\Services;

use App\Models\Shift;
use App\Models\ActivityLog;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class ShiftService
{
    /**
     * Buka shift baru.
     */
    public function openShift(int $userId, int $outletId, float $cashInitial): Shift
    {
        // Cek apakah masih ada shift open
        $existing = Shift::getActiveShift($userId, $outletId);
        if ($existing) {
            throw new \Exception('Anda masih memiliki shift yang aktif. Tutup shift terlebih dahulu.');
        }

        $shift = DB::transaction(function () use ($userId, $outletId, $cashInitial) {
            $shift = Shift::create([
                'user_id' => $userId,
                'outlet_id' => $outletId,
                'opened_at' => now(),
                'cash_initial' => $cashInitial,
                'status' => 'open',
            ]);

            ActivityLog::log(
                'open_shift',
                "Buka shift #{$shift->id} dengan saldo awal Rp" . number_format($cashInitial, 0, ',', '.'),
                $shift,
                ['cash_initial' => $cashInitial]
            );

            return $shift;
        });

        return $shift;
    }

    /**
     * Tutup shift dengan uang fisik.
     */
    public function closeShift(int $shiftId, float $cashActual, ?string $notes = null): Shift
    {
        return DB::transaction(function () use ($shiftId, $cashActual, $notes) {
            $shift = Shift::findOrFail($shiftId);

            if ($shift->status === 'closed') {
                throw new \Exception('Shift ini sudah ditutup.');
            }

            $shift->close($cashActual, $notes);

            ActivityLog::log(
                'close_shift',
                "Tutup shift #{$shift->id}: saldo awal Rp" . number_format($shift->cash_initial, 0, ',', '.') .
                ", penjualan Rp" . number_format($shift->total_sales, 0, ',', '.') .
                ", uang fisik Rp" . number_format($cashActual, 0, ',', '.') .
                ", selisih Rp" . number_format($shift->cash_difference, 0, ',', '.'),
                $shift,
                [
                    'cash_initial' => $shift->cash_initial,
                    'cash_expected' => $shift->cash_expected,
                    'cash_actual' => $cashActual,
                    'cash_difference' => $shift->cash_difference,
                    'total_sales' => $shift->total_sales,
                    'transaction_count' => $shift->transaction_count,
                ]
            );

            return $shift;
        });
    }

    /**
     * Dapatkan laporan shift untuk satu user.
     */
    public function getUserShiftReport(int $userId, ?string $startDate = null, ?string $endDate = null)
    {
        $query = Shift::with('transactions')
            ->where('user_id', $userId)
            ->where('status', 'closed');

        if ($startDate) {
            $query->whereDate('opened_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('closed_at', '<=', $endDate);
        }

        $shifts = $query->orderBy('closed_at', 'desc')->get();

        return [
            'shifts' => $shifts,
            'total_shifts' => $shifts->count(),
            'total_sales' => $shifts->sum('total_sales'),
            'total_cash' => $shifts->sum('total_cash_sales'),
            'total_transactions' => $shifts->sum('transaction_count'),
            'total_difference' => $shifts->sum('cash_difference'),
        ];
    }

    /**
     * Ambil statistik dashboard untuk kasir.
     */
    public function getKasirDashboard(int $userId, int $outletId = 1): array
    {
        $activeShift = Shift::getActiveShift($userId, $outletId);

        $todayTransactions = Transaction::byUser($userId)
            ->completed()
            ->whereDate('created_at', today());

        $todaySales = (float) $todayTransactions->sum('grand_total');
        $todayCount = $todayTransactions->count();

        $todayCash = (float) (clone $todayTransactions)
            ->where('payment_method', 'cash')
            ->sum('grand_total');

        $weekSales = Transaction::byUser($userId)
            ->completed()
            ->where('created_at', '>=', now()->subDays(7))
            ->sum('grand_total');

        $lastTransaction = Transaction::byUser($userId)
            ->completed()
            ->latest()
            ->with('items.product')
            ->first();

        return [
            'active_shift' => $activeShift,
            'today_sales' => $todaySales,
            'today_transactions' => $todayCount,
            'today_cash' => $todayCash,
            'week_sales' => $weekSales,
            'last_transaction' => $lastTransaction,
        ];
    }

    /**
     * Dapatkan statistik semua kasir (untuk admin/supervisor).
     */
    public function getAllKasirStats(int $outletId = 1): array
    {
        $kasirs = \App\Models\User::where('role', 'kasir')
            ->where('outlet_id', $outletId)
            ->get()
            ->map(function ($kasir) {
                $todaySales = Transaction::byUser($kasir->id)
                    ->completed()
                    ->whereDate('created_at', today())
                    ->sum('grand_total');

                $todayCount = Transaction::byUser($kasir->id)
                    ->completed()
                    ->whereDate('created_at', today())
                    ->count();

                $activeShift = Shift::getActiveShift($kasir->id, $kasir->outlet_id);

                return [
                    'id' => $kasir->id,
                    'name' => $kasir->name,
                    'email' => $kasir->email,
                    'is_active' => $kasir->is_active,
                    'has_active_shift' => $activeShift !== null,
                    'shift_id' => $activeShift?->id,
                    'today_sales' => $todaySales,
                    'today_count' => $todayCount,
                    'last_activity' => $kasir->last_activity_at,
                ];
            })
            ->sortByDesc('today_sales')
            ->values();

        $stats = \App\Models\User::whereIn('role', ['kasir'])
            ->where('outlet_id', $outletId)
            ->selectRaw('COUNT(*) as total_kasir')
            ->selectRaw('SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_kasir')
            ->first();

        return [
            'kasirs' => $kasirs,
            'total_kasir' => $stats->total_kasir ?? 0,
            'active_kasir' => $stats->active_kasir ?? 0,
        ];
    }
}
