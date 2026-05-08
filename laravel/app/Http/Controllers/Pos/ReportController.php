<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->get("start_date", now()->startOfMonth()->format("Y-m-d"));
        $endDate = $request->get("end_date", now()->format("Y-m-d"));

        // Transactions period — now using grand_total
        $transactions = Transaction::whereBetween("created_at", [$startDate . " 00:00:00", $endDate . " 23:59:59"])
            ->latest()->paginate(20);

        // Revenue (use grand_total which accounts for discounts & tax)
        $totalRevenue = Transaction::whereBetween("created_at", [$startDate . " 00:00:00", $endDate . " 23:59:59"])
            ->sum(DB::raw("COALESCE(grand_total, total)"));

        $totalTransactions = Transaction::whereBetween("created_at", [$startDate . " 00:00:00", $endDate . " 23:59:59"])
            ->count();

        // Total diskon periode
        $totalItemDiscount = TransactionItem::whereHas("transaction", function($q) use ($startDate, $endDate) {
                $q->whereBetween("created_at", [$startDate . " 00:00:00", $endDate . " 23:59:59"]);
            })->sum("discount_amount");

        $totalGlobalDiscount = Transaction::whereBetween("created_at", [$startDate . " 00:00:00", $endDate . " 23:59:59"])
            ->sum("discount_amount");

        $totalDiscount = $totalItemDiscount + $totalGlobalDiscount;

        // Total pajak periode
        $totalTax = Transaction::whereBetween("created_at", [$startDate . " 00:00:00", $endDate . " 23:59:59"])
            ->sum("tax_amount");

        // Laba kotor: SUM over all transaction_items of (price - purchase_price) * quantity
        $profitData = TransactionItem::select(
                DB::raw("SUM((ti.price - p.purchase_price) * ti.quantity) as gross_profit"),
                DB::raw("SUM(ti.subtotal) as revenue"),
                DB::raw("SUM(p.purchase_price * ti.quantity) as total_cogs")
            )
            ->from("transaction_items as ti")
            ->join("products as p", "p.id", "=", "ti.product_id")
            ->whereHas("transaction", function($q) use ($startDate, $endDate) {
                $q->whereBetween("created_at", [$startDate . " 00:00:00", $endDate . " 23:59:59"]);
            })
            ->first();

        $grossProfit = $profitData ? (float) $profitData->gross_profit : 0;
        $totalCogs = $profitData ? (float) $profitData->total_cogs : 0;

        // Best sellers with profit
        $bestSellers = TransactionItem::select(
                "ti.product_id",
                DB::raw("SUM(ti.quantity) as total_qty"),
                DB::raw("SUM(ti.subtotal) as total_revenue"),
                DB::raw("SUM((ti.price - p.purchase_price) * ti.quantity) as total_profit"),
                DB::raw("SUM(ti.discount_amount) as total_item_discount")
            )
            ->from("transaction_items as ti")
            ->join("products as p", "p.id", "=", "ti.product_id")
            ->whereHas("transaction", function($q) use ($startDate, $endDate) {
                $q->whereBetween("created_at", [$startDate . " 00:00:00", $endDate . " 23:59:59"]);
            })
            ->groupBy("ti.product_id")
            ->orderByDesc("total_qty")
            ->limit(10)
            ->with("product")
            ->get();

        // Profit chart per day
        $profitChart = TransactionItem::select(
                DB::raw("DATE(t.created_at) as date"),
                DB::raw("SUM((ti.price - p.purchase_price) * ti.quantity) as profit"),
                DB::raw("SUM(ti.subtotal) as revenue"),
                DB::raw("SUM(ti.discount_amount) as item_discount"),
                DB::raw("SUM(t.discount_amount) as global_discount")
            )
            ->from("transaction_items as ti")
            ->join("products as p", "p.id", "=", "ti.product_id")
            ->join("transactions as t", "t.id", "=", "ti.transaction_id")
            ->where("t.created_at", ">=", now()->subDays(30))
            ->groupBy(DB::raw("DATE(t.created_at)"))
            ->orderBy("date")
            ->get();

        // Margin percentage
        $marginPercent = $totalRevenue > 0 ? round(($grossProfit / $totalRevenue) * 100, 1) : 0;

        return view("pos.reports.index", compact(
            "transactions", "totalRevenue", "totalTransactions", "bestSellers",
            "startDate", "endDate", "grossProfit", "totalCogs", "profitChart",
            "marginPercent", "totalDiscount", "totalTax", "totalItemDiscount",
            "totalGlobalDiscount"
        ));
    }
}
