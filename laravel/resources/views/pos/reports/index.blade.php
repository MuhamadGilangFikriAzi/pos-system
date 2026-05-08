@extends("layouts.app")
@section("title", "Laporan & Laba")
@section("content")
<div x-data="{ tab: 'overview' }">
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-sm text-gray-500 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm text-gray-500 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="border rounded-lg px-3 py-2 text-sm">
            </div>
            <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">Filter</button>
        </form>
    </div>

    <!-- Tab Navigation -->
    <div class="flex gap-2 mb-6">
        <button @click="tab = 'overview'" :class="tab === 'overview' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100'" class="px-5 py-2.5 rounded-lg text-sm font-medium transition shadow-sm">Overview</button>
        <button @click="tab = 'profit'" :class="tab === 'profit' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100'" class="px-5 py-2.5 rounded-lg text-sm font-medium transition shadow-sm">Laba Rugi</button>
        <button @click="tab = 'products'" :class="tab === 'products' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100'" class="px-5 py-2.5 rounded-lg text-sm font-medium transition shadow-sm">Per Produk</button>
        <button @click="tab = 'transactions'" :class="tab === 'transactions' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100'" class="px-5 py-2.5 rounded-lg text-sm font-medium transition shadow-sm">Transaksi</button>
    </div>

    <!-- TAB 1: Overview -->
    <div x-show="tab === 'overview'">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm p-5">
                <p class="text-xs text-gray-400 uppercase tracking-wide">Revenue</p>
                <p class="text-2xl font-bold text-green-600 mt-1">Rp {{ number_format($totalRevenue, 0, ",", ".") }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5">
                <p class="text-xs text-gray-400 uppercase tracking-wide">Laba Kotor</p>
                <p class="text-2xl font-bold text-blue-600 mt-1">Rp {{ number_format($grossProfit, 0, ",", ".") }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5">
                <p class="text-xs text-gray-400 uppercase tracking-wide">HPP (Modal)</p>
                <p class="text-2xl font-bold text-orange-600 mt-1">Rp {{ number_format($totalCogs, 0, ",", ".") }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5">
                <p class="text-xs text-gray-400 uppercase tracking-wide">Margin Laba</p>
                <p class="text-2xl font-bold {{ $marginPercent >= 30 ? 'text-green-600' : ($marginPercent >= 15 ? 'text-yellow-600' : 'text-red-600') }} mt-1">{{ $marginPercent }}%</p>
            </div>
        </div>

        <!-- Extra Stats: Discount & Tax -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm p-4">
                <p class="text-xs text-gray-400 uppercase tracking-wide">Total Diskon</p>
                <p class="text-xl font-bold text-red-600 mt-1">-Rp {{ number_format($totalDiscount ?? 0, 0, ",", ".") }}</p>
                <p class="text-xs text-gray-400 mt-1">Item: -Rp{{ number_format($totalItemDiscount ?? 0,0,",",".") }} | Global: -Rp{{ number_format($totalGlobalDiscount ?? 0,0,",",".") }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4">
                <p class="text-xs text-gray-400 uppercase tracking-wide">Total Pajak</p>
                <p class="text-xl font-bold text-indigo-600 mt-1">+Rp {{ number_format($totalTax ?? 0, 0, ",", ".") }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4">
                <p class="text-xs text-gray-400 uppercase tracking-wide">Total Transaksi</p>
                <p class="text-xl font-bold text-gray-700 mt-1">{{ $totalTransactions }}</p>
                <p class="text-xs text-gray-400 mt-1">Rata-rata: Rp{{ $totalTransactions > 0 ? number_format($totalRevenue / $totalTransactions,0,',','.') : 0 }}</p>
            </div>
        </div>

        <!-- Profit Chart (last 30 days) -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Laba & Revenue 30 Hari Terakhir</h3>
            @if($profitChart->count() > 0)
            <div class="space-y-1">
                @foreach($profitChart as $pc)
                <div class="flex items-center justify-between py-1.5 border-b border-gray-50 text-sm">
                    <span class="text-gray-500 w-32">{{ \Carbon\Carbon::parse($pc->date)->isoFormat("D MMM") }}</span>
                    <div class="flex-1 mx-3">
                        <div class="w-full bg-gray-100 rounded-full h-2.5">
                            <div class="bg-green-500 h-2.5 rounded-full" style="width: {{ $pc->revenue > 0 ? min(($pc->revenue / $profitChart->max('revenue')) * 100, 100) : 0 }}%"></div>
                        </div>
                    </div>
                    <span class="font-semibold text-green-600 w-28 text-right text-xs">Rp {{ number_format($pc->revenue, 0, ",", ".") }}</span>
                    <span class="text-blue-600 w-24 text-right text-xs {{ $pc->profit < 0 ? 'text-red-600' : '' }}">Rp {{ number_format($pc->profit, 0, ",", ".") }}</span>
                </div>
                @endforeach
            </div>
            <div class="flex justify-between text-xs text-gray-400 mt-2">
                <span>Revenue</span>
                <span>Laba</span>
            </div>
            @else
            <p class="text-gray-400 text-center py-6">Belum ada data transaksi 30 hari terakhir</p>
            @endif
        </div>
    </div>

    <!-- TAB 2: Laba Rugi Detail -->
    <div x-show="tab === 'profit'" x-cloak>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-6 text-lg">Laporan Laba Rugi</h3>
            <p class="text-sm text-gray-400 mb-4">{{ $startDate }} s/d {{ $endDate }}</p>
            
            <div class="space-y-3">
                <div class="flex justify-between py-3 border-b">
                    <span class="font-medium text-gray-700">A. Pendapatan Kotor (Revenue)</span>
                    <span class="font-bold text-green-600">Rp {{ number_format($totalRevenue, 0, ",", ".") }}</span>
                </div>
                <div class="flex justify-between py-3 border-b pl-4 text-red-600">
                    <span class="text-gray-600">Diskon Item</span>
                    <span class="font-semibold">-Rp {{ number_format($totalItemDiscount ?? 0, 0, ",", ".") }}</span>
                </div>
                <div class="flex justify-between py-3 border-b pl-4 text-red-600">
                    <span class="text-gray-600">Diskon Global Transaksi</span>
                    <span class="font-semibold">-Rp {{ number_format($totalGlobalDiscount ?? 0, 0, ",", ".") }}</span>
                </div>
                <div class="flex justify-between py-3 border-b pl-4 text-indigo-600">
                    <span class="text-gray-600">Total Pajak</span>
                    <span class="font-semibold">+Rp {{ number_format($totalTax ?? 0, 0, ",", ".") }}</span>
                </div>
                <div class="flex justify-between py-3 border-b">
                    <span class="text-gray-600">B. Harga Pokok Penjualan (HPP)</span>
                    <span class="font-semibold text-orange-600">Rp {{ number_format($totalCogs, 0, ",", ".") }}</span>
                </div>
                <div class="flex justify-between py-3 border-b border-dashed bg-green-50 px-3 rounded">
                    <span class="font-bold text-gray-800">Laba Kotor (A - B)</span>
                    <span class="font-bold text-blue-600 text-lg">Rp {{ number_format($grossProfit, 0, ",", ".") }}</span>
                </div>
                <div class="flex justify-between py-3 border-b">
                    <span class="text-gray-600">Margin Laba</span>
                    <span class="font-semibold {{ $marginPercent >= 30 ? 'text-green-600' : ($marginPercent >= 15 ? 'text-yellow-600' : 'text-red-600') }}">{{ $marginPercent }}%</span>
                </div>
                <div class="flex justify-between py-3">
                    <span class="text-gray-500 text-sm">Total Transaksi</span>
                    <span class="text-gray-700">{{ $totalTransactions }}</span>
                </div>
            </div>

            @if($profitChart->count() > 0)
            <hr class="my-6">
            <h4 class="font-semibold text-gray-700 mb-3">Rincian per Hari (30 hari terakhir)</h4>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left">
                            <th class="px-3 py-2">Tanggal</th>
                            <th class="px-3 py-2 text-right">Revenue</th>
                            <th class="px-3 py-2 text-right">Diskon</th>
                            <th class="px-3 py-2 text-right">Laba</th>
                            <th class="px-3 py-2 text-right">Margin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($profitChart as $pc)
                        <tr class="border-b">
                            <td class="px-3 py-2">{{ \Carbon\Carbon::parse($pc->date)->isoFormat("D MMM YYYY") }}</td>
                            <td class="px-3 py-2 text-right">Rp {{ number_format($pc->revenue, 0, ",", ".") }}</td>
                            <td class="px-3 py-2 text-right text-red-500">-Rp {{ number_format(($pc->item_discount ?? 0) + ($pc->global_discount ?? 0), 0, ",", ".") }}</td>
                            <td class="px-3 py-2 text-right font-medium {{ $pc->profit >= 0 ? 'text-green-600' : 'text-red-600' }}">Rp {{ number_format($pc->profit, 0, ",", ".") }}</td>
                            <td class="px-3 py-2 text-right">{{ $pc->revenue > 0 ? round(($pc->profit / $pc->revenue) * 100, 1) : 0 }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    <!-- TAB 3: Per Produk -->
    <div x-show="tab === 'products'" x-cloak>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Produk Terlaris & Laba per Produk</h3>
            @if($bestSellers->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left">
                            <th class="px-3 py-2">Produk</th>
                            <th class="px-3 py-2 text-right">Terjual</th>
                            <th class="px-3 py-2 text-right">Revenue</th>
                            <th class="px-3 py-2 text-right">Diskon</th>
                            <th class="px-3 py-2 text-right">Laba</th>
                            <th class="px-3 py-2 text-right">Margin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bestSellers as $b)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-3 py-2 font-medium">{{ $b->product->name ?? 'Produk#' . $b->product_id }}</td>
                            <td class="px-3 py-2 text-right">{{ $b->total_qty }}</td>
                            <td class="px-3 py-2 text-right text-green-600">Rp {{ number_format($b->total_revenue, 0, ",", ".") }}</td>
                            <td class="px-3 py-2 text-right text-red-500">-Rp {{ number_format($b->total_item_discount ?? 0, 0, ",", ".") }}</td>
                            <td class="px-3 py-2 text-right font-medium {{ $b->total_profit >= 0 ? 'text-blue-600' : 'text-red-600' }}">Rp {{ number_format($b->total_profit, 0, ",", ".") }}</td>
                            <td class="px-3 py-2 text-right">{{ $b->total_revenue > 0 ? round(($b->total_profit / $b->total_revenue) * 100, 1) : 0 }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-gray-400 text-center py-6">Belum ada data penjualan</p>
            @endif
        </div>
    </div>

    <!-- TAB 4: Transaksi -->
    <div x-show="tab === 'transactions'" x-cloak>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h4 class="font-semibold text-gray-800 mb-4">Riwayat Transaksi</h4>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left">
                            <th class="px-3 py-2">Invoice</th>
                            <th class="px-3 py-2">Tanggal</th>
                            <th class="px-3 py-2">Item</th>
                            <th class="px-3 py-2 text-right">Total</th>
                            <th class="px-3 py-2 text-right">Diskon</th>
                            <th class="px-3 py-2 text-right">Grand Total</th>
                            <th class="px-3 py-2 text-right">Laba</th>
                            <th class="px-3 py-2">Kasir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $t)
                        @php
                            $tProfit = $t->items->sum(function($item) {
                                return ($item->price - ($item->product->purchase_price ?? 0)) * $item->quantity;
                            });
                            $totalDisc = $t->items->sum('discount_amount') + ($t->discount_amount ?? 0);
                        @endphp
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-3 py-2 font-medium">{{ $t->invoice_number }}</td>
                            <td class="px-3 py-2 text-gray-500">{{ $t->created_at->format("d/m/Y H:i") }}</td>
                            <td class="px-3 py-2">{{ $t->items->count() }}</td>
                            <td class="px-3 py-2 text-right text-green-600">Rp {{ number_format($t->total, 0, ",", ".") }}</td>
                            <td class="px-3 py-2 text-right text-red-500">-Rp {{ number_format($totalDisc, 0, ",", ".") }}</td>
                            <td class="px-3 py-2 text-right font-semibold text-indigo-600">Rp {{ number_format($t->grand_total ?? $t->total, 0, ",", ".") }}</td>
                            <td class="px-3 py-2 text-right font-medium {{ $tProfit >= 0 ? 'text-blue-600' : 'text-red-600' }}">Rp {{ number_format($tProfit, 0, ",", ".") }}</td>
                            <td class="px-3 py-2 text-gray-500">{{ $t->user->name ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center py-8 text-gray-400">Tidak ada transaksi</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $transactions->links() }}</div>
        </div>
    </div>
</div>

<style>[x-cloak]{display:none!important}</style>
@endsection
