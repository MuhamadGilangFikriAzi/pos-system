<?php
namespace App\Http\Controllers\Pos;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->get("start_date", now()->startOfMonth()->format("Y-m-d"));
        $endDate = $request->get("end_date", now()->format("Y-m-d"));
        $transactions = Transaction::whereBetween("created_at", [$startDate . " 00:00:00", $endDate . " 23:59:59"])
            ->latest()->paginate(20);
        $totalRevenue = Transaction::whereBetween("created_at", [$startDate . " 00:00:00", $endDate . " 23:59:59"])->sum("total");
        $totalTransactions = $transactions->total();
        $bestSellers = TransactionItem::select("product_id", DB::raw("SUM(quantity) as total_qty"), DB::raw("SUM(subtotal) as total_revenue"))
            ->whereHas("transaction", function($q) use ($startDate, $endDate) {
                $q->whereBetween("created_at", [$startDate . " 00:00:00", $endDate . " 23:59:59"]);
            })
            ->groupBy("product_id")->orderByDesc("total_qty")->limit(10)
            ->with("product")->get();
        return view("pos.reports.index", compact("transactions", "totalRevenue", "totalTransactions", "bestSellers", "startDate", "endDate"));
    }
}
