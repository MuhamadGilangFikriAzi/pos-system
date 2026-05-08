<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\ActivityLog;
use App\Services\ShiftService;
use Illuminate\Http\Request;

class KasirDashboardController extends Controller
{
    protected ShiftService $shiftService;

    public function __construct(ShiftService $shiftService)
    {
        $this->shiftService = $shiftService;
    }

    /**
     * Dashboard utama kasir (ringkasan personal).
     */
    public function index()
    {
        $user = auth()->user();
        $dashboard = $this->shiftService->getKasirDashboard($user->id, $user->outlet_id ?? 1);

        // Transaksi hari ini
        $todayTransactions = Transaction::byUser($user->id)
            ->completed()
            ->whereDate('created_at', today())
            ->with('items.product')
            ->latest()
            ->get();

        // Aktivitas terbaru
        $recentActivities = ActivityLog::where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        return view('pos.kasir.dashboard', array_merge($dashboard, [
            'todayTransactions' => $todayTransactions,
            'recentActivities' => $recentActivities,
        ]));
    }

    /**
     * Riwayat transaksi kasir (dengan filter).
     */
    public function transactions(Request $request)
    {
        $user = auth()->user();

        $query = Transaction::completed()
            ->with('items.product', 'shift');

        // Role-based filtering
        if (!$user->canViewAllTransactions()) {
            $query->where('user_id', $user->id);
        } else {
            if ($request->user_id) {
                $query->where('user_id', $request->user_id);
            }
        }

        // Filter shift
        if ($request->shift_id) {
            $query->where('shift_id', $request->shift_id);
        }

        // Filter tanggal
        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $transactions = $query->latest()->paginate(25);

        // Untuk dropdown filter kasir (admin/supervisor)
        $kasirs = collect();
        if ($user->canViewAllTransactions()) {
            $kasirs = \App\Models\User::whereIn('role', ['kasir', 'supervisor', 'admin'])
                ->where('is_active', true)
                ->get(['id', 'name', 'role']);
        }

        return view('pos.kasir.transactions', compact('transactions', 'kasirs'));
    }

    /**
     * Activity log.
     */
    public function activity(Request $request)
    {
        $user = auth()->user();

        $query = ActivityLog::with('user');

        if (!$user->canViewAllTransactions()) {
            $query->where('user_id', $user->id);
        }

        if ($request->action) {
            $query->where('action', $request->action);
        }

        $logs = $query->latest()->paginate(30);

        $actions = ActivityLog::select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('pos.kasir.activity', compact('logs', 'actions'));
    }

    /**
     * Export laporan transaksi ke CSV.
     */
    public function export(Request $request)
    {
        $user = auth()->user();

        $query = Transaction::completed()
            ->with('items.product', 'user', 'shift');

        if (!$user->canViewAllTransactions()) {
            $query->where('user_id', $user->id);
        }

        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $transactions = $query->latest()->get();

        $filename = 'laporan_transaksi_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($transactions) {
            $handle = fopen('php://output', 'w');

            // BOM UTF-8 for Excel
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header
            fputcsv($handle, [
                'Invoice', 'Tanggal', 'Kasir', 'Shift',
                'Item', 'Subtotal', 'Diskon Item', 'Diskon Global',
                'Pajak', 'Grand Total', 'Bayar', 'Kembali',
                'Metode', 'Status'
            ]);

            foreach ($transactions as $t) {
                $totalItemDisc = $t->items->sum('discount_amount');
                $itemCount = $t->items->count();
                $itemNames = $t->items->take(3)->map(fn($i) => $i->product->name ?? 'N/A')->implode(', ');
                if ($itemCount > 3) $itemNames .= ' ...';

                fputcsv($handle, [
                    $t->invoice_number,
                    $t->created_at->format('d/m/Y H:i'),
                    $t->user->name ?? '-',
                    $t->shift_id ? '#' . $t->shift_id : '-',
                    $itemCount . ' (' . $itemNames . ')',
                    number_format($t->subtotal ?? $t->total, 0, ',', '.'),
                    number_format($totalItemDisc, 0, ',', '.'),
                    number_format($t->discount_amount ?? 0, 0, ',', '.'),
                    number_format($t->tax_amount ?? 0, 0, ',', '.'),
                    number_format($t->grand_total ?? $t->total, 0, ',', '.'),
                    number_format($t->payment, 0, ',', '.'),
                    number_format($t->change, 0, ',', '.'),
                    $t->payment_method ?? 'cash',
                    $t->status ?? 'completed',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
