<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Transaction;

class DashboardController extends Controller
{
    public function index()
    {
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $lowStock = Product::where('stock', '<=', 5)->count();

        // Simple counts - gak perlu join
        $todayRevenue = Transaction::whereDate('created_at', today())->sum('total');
        $totalTransactions = Transaction::count();

        // Revenue 7 hari - pake simple query
        $revenueChart = Transaction::selectRaw('DATE(created_at) as date, SUM(total) as revenue')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Recent max 5 biar cepet
        $recentTransactions = Transaction::with('user')
            ->latest()
            ->take(5)
            ->get();

        return view('pos.dashboard', compact(
            'totalProducts', 'totalCategories', 'todayRevenue',
            'totalTransactions', 'lowStock', 'revenueChart', 'recentTransactions'
        ));
    }
}
