<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Services\ShiftService;
use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    protected ShiftService $shiftService;

    public function __construct(ShiftService $shiftService)
    {
        $this->shiftService = $shiftService;
    }

    /**
     * Form buka shift.
     */
    public function openForm()
    {
        return view('pos.shift.open');
    }

    /**
     * Proses buka shift.
     */
    public function openStore(Request $request)
    {
        $data = $request->validate([
            'cash_initial' => 'required|numeric|min:0',
        ]);

        try {
            $shift = $this->shiftService->openShift(
                auth()->id(),
                auth()->user()->outlet_id ?? 1,
                (float) $data['cash_initial']
            );

            return redirect()->route('pos.index')
                ->with('success', "Shift #{$shift->id} berhasil dibuka dengan saldo Rp" . number_format($data['cash_initial'], 0, ',', '.'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Form tutup shift.
     */
    public function closeForm()
    {
        $activeShift = Shift::getActiveShift(auth()->id(), auth()->user()->outlet_id ?? 1);

        if (!$activeShift) {
            return redirect()->route('pos.shift.open')
                ->with('error', 'Tidak ada shift yang aktif.');
        }

        // Recalculate dulu untuk data terbaru
        $activeShift->load('transactions');
        $activeShift->recalculate();

        return view('pos.shift.close', compact('activeShift'));
    }

    /**
     * Proses tutup shift.
     */
    public function closeStore(Request $request)
    {
        $data = $request->validate([
            'shift_id' => 'required|exists:shifts,id',
            'cash_actual' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $shift = $this->shiftService->closeShift(
                (int) $data['shift_id'],
                (float) $data['cash_actual'],
                $data['notes'] ?? null
            );

            return redirect()->route('pos.shift.history')
                ->with('success', "Shift #{$shift->id} berhasil ditutup. Selisih: Rp" .
                    number_format($shift->cash_difference, 0, ',', '.'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Riwayat shift user.
     */
    public function history(Request $request)
    {
        $user = auth()->user();

        $query = Shift::with('user')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        // Filter tanggal
        if ($request->start_date) {
            $query->whereDate('opened_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('closed_at', '<=', $request->end_date);
        }

        $shifts = $query->paginate(20);

        // Untuk admin: lihat semua shift
        if ($user->canViewAllTransactions()) {
            $allShifts = Shift::with('user')
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        }

        $summary = [
            'total_shifts' => Shift::where('user_id', $user->id)->count(),
            'total_sales' => Shift::where('user_id', $user->id)->sum('total_sales'),
            'total_difference' => Shift::where('user_id', $user->id)->sum('cash_difference'),
        ];

        return view('pos.shift.history', compact('shifts', 'summary'));
    }

    /**
     * Detail shift.
     */
    public function show(int $id)
    {
        $shift = Shift::with(['user', 'transactions' => function($q) {
            $q->completed()->with('items.product');
        }])->findOrFail($id);

        return view('pos.shift.show', compact('shift'));
    }
}
