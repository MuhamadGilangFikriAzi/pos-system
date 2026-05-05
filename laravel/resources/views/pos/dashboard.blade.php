@extends("layouts.app")
@section("title", "Dashboard")
@section("content")
<div x-data>
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-500 text-sm">Total Produk</p>
            <p class="text-3xl font-bold">{{ $totalProducts }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-500 text-sm">Kategori</p>
            <p class="text-3xl font-bold">{{ $totalCategories }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-500 text-sm">Transaksi Hari Ini</p>
            <p class="text-3xl font-bold">Rp {{ number_format($todayRevenue, 0, ",", ".") }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-500 text-sm">Total Transaksi</p>
            <p class="text-3xl font-bold">{{ $totalTransactions }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6 @if($lowStock > 0) border-2 border-red-400 @endif">
            <p class="text-gray-500 text-sm">Stok Menipis</p>
            <p class="text-3xl font-bold @if($lowStock > 0) text-red-600 @endif">{{ $lowStock }}</p>
        </div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold mb-4">Revenue 7 Hari Terakhir</h3>
            <div class="space-y-2">
                @forelse($revenueChart as $r)
                <div class="flex items-center justify-between">
                    <span class="text-sm">{{ \Carbon\Carbon::parse($r->date)->isoFormat("dddd, D MMM") }}</span>
                    <span class="font-semibold text-green-600">Rp {{ number_format($r->revenue, 0, ",", ".") }}</span>
                </div>
                @empty
                <p class="text-gray-400 text-sm">Belum ada transaksi</p>
                @endforelse
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold mb-4">Transaksi Terbaru</h3>
            <div class="space-y-2">
                @forelse($recentTransactions as $t)
                <div class="flex items-center justify-between border-b pb-2">
                    <div>
                        <p class="text-sm font-medium">{{ $t->invoice_number }}</p>
                        <p class="text-xs text-gray-400">{{ $t->created_at->diffForHumans() }}</p>
                    </div>
                    <span class="font-semibold">Rp {{ number_format($t->total, 0, ",", ".") }}</span>
                </div>
                @empty
                <p class="text-gray-400 text-sm">Belum ada transaksi</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection