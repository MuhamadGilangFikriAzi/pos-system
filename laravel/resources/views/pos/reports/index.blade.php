@extends("layouts.app")
@section("title", "Laporan")
@section("content")
<div class="bg-white rounded-lg shadow p-6">
    <form method="GET" class="flex gap-4 items-end mb-6">
        <div><label class="block text-sm text-gray-500 mb-1">Dari</label><input type="date" name="start_date" value="{{ $startDate }}" class="border rounded px-3 py-2"></div>
        <div><label class="block text-sm text-gray-500 mb-1">Sampai</label><input type="date" name="end_date" value="{{ $endDate }}" class="border rounded px-3 py-2"></div>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg">Filter</button>
    </form>
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="bg-green-50 p-4 rounded-lg"><p class="text-sm text-gray-500">Total Revenue</p><p class="text-2xl font-bold text-green-600">Rp {{ number_format($totalRevenue, 0, ",", ".") }}</p></div>
        <div class="bg-indigo-50 p-4 rounded-lg"><p class="text-sm text-gray-500">Total Transaksi</p><p class="text-2xl font-bold text-indigo-600">{{ $totalTransactions }}</p></div>
    </div>

    @if($bestSellers->count() > 0)
    <h4 class="font-semibold mb-3">Produk Terlaris</h4>
    <div class="space-y-2 mb-6">@foreach($bestSellers as $b)<div class="flex justify-between border-b pb-2"><span>{{ $b->product->name }}</span><span>{{ $b->total_qty }} terjual (Rp {{ number_format($b->total_revenue, 0, ",", ".") }})</span></div>@endforeach</div>
    @endif

    <h4 class="font-semibold mb-3">Riwayat Transaksi</h4>
    <table class="w-full"><thead><tr class="border-b text-left text-sm text-gray-500"><th class="py-2">Invoice</th><th class="py-2">Tanggal</th><th class="py-2">Total</th><th class="py-2">Kasir</th></tr></thead>
        <tbody>@forelse($transactions as $t)<tr class="border-b"><td class="py-2 text-sm">{{ $t->invoice_number }}</td><td class="py-2 text-sm">{{ $t->created_at->format("d/m/Y H:i") }}</td><td class="py-2 font-semibold">Rp {{ number_format($t->total, 0, ",", ".") }}</td><td class="py-2 text-sm">{{ $t->user->name }}</td></tr>
        @empty<tr><td colspan="4" class="text-center py-8 text-gray-400">Tidak ada transaksi</td></tr>@endforelse</tbody>
    </table>
    <div class="mt-4">{{ $transactions->links() }}</div>
</div>
@endsection