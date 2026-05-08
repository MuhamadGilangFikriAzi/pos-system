<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductWarehouse;
use App\Models\StockMutation;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class InventoryDashboardController extends Controller
{
    protected InventoryService $inv;

    public function __construct(InventoryService $inv)
    {
        $this->inv = $inv;
    }

    public function index()
    {
        $summary = $this->inv->getDashboardSummary();
        $lowStockAlerts = $this->inv->checkMinStockAlerts();
        $todayMutations = StockMutation::with(['product', 'warehouse', 'user'])
            ->whereDate('created_at', today())
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
        $nearExpiry = $this->inv->getNearExpiryProducts(30);
        $topSelling = $this->inv->getTopSelling(now()->subDays(30)->format('Y-m-d'), now()->format('Y-m-d'), 10);

        return view('inventory.dashboard.index', compact(
            'summary', 'lowStockAlerts', 'todayMutations', 'nearExpiry', 'topSelling'
        ));
    }
}
