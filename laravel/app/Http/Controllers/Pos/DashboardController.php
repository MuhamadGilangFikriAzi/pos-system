<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Shift;
use App\Models\User;
use App\Services\ShiftService;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected ShiftService $shiftService;

    public function __construct(ShiftService $shiftService)
    {
        $this->shiftService = $shiftService;
    }

    public function index()
    {
        $user = auth()->user();
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $lowStock = Product::where('stock', '<=', DB::raw('min_stock'))->count();
        $lowStockProducts = Product::with('category')
            ->where('stock', '<=', DB::raw('min_stock'))
            ->orderBy('stock')
            ->take(10)
            ->get();

        $canViewAll = $user->canViewAllTransactions();

        // Revenue — pakai grand_total kalo ada
        $todayRevenue = Transaction::completed()
            ->when(!$canViewAll, fn($q) => $q->where('user_id', $user->id))
            ->whereDate('created_at', today())
            ->sum(DB::raw('COALESCE(grand_total, total)'));

        $totalTransactions = Transaction::completed()
            ->when(!$canViewAll, fn($q) => $q->where('user_id', $user->id))
            ->count();

        // Revenue 7 hari
        $revenueQuery = Transaction::completed()->where('created_at', '>=', now()->subDays(7));
        if (!$canViewAll) $revenueQuery->where('user_id', $user->id);
        $revenueChart = $revenueQuery->selectRaw('DATE(created_at) as date, SUM(COALESCE(grand_total, total)) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Recent transactions
        $recentQuery = Transaction::completed()->with('user');
        if (!$canViewAll) $recentQuery->where('user_id', $user->id);
        $recentTransactions = $recentQuery->latest()->take(5)->get();

        // Statistik personal
        $activeShift = Shift::getActiveShift($user->id, $user->outlet_id ?? 1);
        $myTodayRevenue = Transaction::completed()
            ->where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->sum(DB::raw('COALESCE(grand_total, total)'));
        $myTransactions = Transaction::completed()
            ->where('user_id', $user->id)
            ->count();

        return view('pos.dashboard', compact(
            'totalProducts', 'totalCategories', 'todayRevenue',
            'totalTransactions', 'lowStock', 'lowStockProducts',
            'revenueChart', 'recentTransactions', 'canViewAll',
            'myTransactions', 'myTodayRevenue', 'activeShift'
        ));
    }

    /**
     * Statistik semua kasir (admin/supervisor only).
     */
    public function allKasirStats()
    {
        $stats = $this->shiftService->getAllKasirStats(auth()->user()->outlet_id ?? 1);

        $todayTotal = Transaction::completed()
            ->whereDate('created_at', today())
            ->sum(DB::raw('COALESCE(grand_total, total)'));

        $openShifts = Shift::where('status', 'open')->count();
        $todayTransactions = Transaction::completed()
            ->whereDate('created_at', today())
            ->count();

        return view('pos.kasir.all_stats', array_merge($stats, [
            'todayTotal' => $todayTotal,
            'openShifts' => $openShifts,
            'todayTransactions' => $todayTransactions,
        ]));
    }
}
