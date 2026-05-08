@extends("layouts.app")
@section("title", "Dashboard")
@section("content")
<div x-data>
    <!-- Shift Status Banner -->
    @if(isset($activeShift) && $activeShift)
    <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6 flex justify-between items-center">
        <div>
            <span class="text-green-700 font-semibold">🟢 Shift #{{ $activeShift->id }} Sedang Aktif</span>
            <span class="text-green-600 text-sm ml-3">Penjualan: Rp{{ number_format($myTodayRevenue ?? 0, 0, ',', '.') }}</span>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('pos.shift.close') }}" class="bg-amber-600 text-white px-3 py-1.5 rounded-lg text-xs hover:bg-amber-700 transition">🔒 Tutup Shift</a>
            <a href="{{ route('pos.kasir.dashboard') }}" class="bg-indigo-600 text-white px-3 py-1.5 rounded-lg text-xs hover:bg-indigo-700 transition">👤 Dashboard Kasir</a>
        </div>
    </div>
    @elseif(Auth::user()->role === 'kasir')
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 flex justify-between items-center">
        <div>
            <span class="text-amber-700 font-semibold">⚠️ Shift Belum Dibuka</span>
            <span class="text-amber-600 text-sm ml-3">Buka shift dulu untuk mulai bertransaksi</span>
        </div>
        <a href="{{ route('pos.shift.open') }}" class="bg-indigo-600 text-white px-3 py-1.5 rounded-lg text-xs hover:bg-indigo-700 transition">🚀 Buka Shift</a>
    </div>
    @else
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
        <span class="text-blue-700 font-semibold">📊 Selamat datang, {{ Auth::user()->name }}!</span>
    </div>
    @endif

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
            <p class="text-gray-500 text-sm">{{ $canViewAll ? 'Revenue Hari Ini' : 'Penjualan Saya Hari Ini' }}</p>
            <p class="text-3xl font-bold text-green-600">Rp{{ number_format($todayRevenue, 0, ",", ".") }}</p>
            <p class="text-xs text-gray-400 mt-1">Saya: Rp{{ number_format($myTodayRevenue, 0, ",", ".") }}</p>
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

    @if($lowStock > 0)
    <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4">
        <div class="flex items-center gap-2 mb-2">
            <span class="text-red-600 text-lg">&#9888;</span>
            <h3 class="font-semibold text-red-800">Stok Menipis ({{ $lowStock }} produk)</h3>
            <a href="{{ route('stock.create') }}" class="ml-auto text-sm text-red-600 hover:underline">+ Tambah Stok</a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
            @foreach($lowStockProducts as $p)
            <div class="bg-white rounded-lg px-3 py-2 flex justify-between items-center">
                <div>
                    <a href="{{ route('stock.show', $p) }}" class="text-sm font-medium text-gray-800 hover:text-red-600">{{ $p->name }}</a>
                    <p class="text-xs text-gray-400">{{ $p->category->name ?? '-' }}</p>
                </div>
                <span class="text-sm font-bold {{ $p->stock == 0 ? 'text-red-600' : 'text-orange-500' }}">{{ $p->stock }} {{ $p->unit }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

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
                        <p class="text-xs text-gray-400">{{ $t->created_at->diffForHumans() }} - {{ $t->user->name ?? '-' }}</p>
                    </div>
                    <span class="font-semibold">Rp{{ number_format($t->grand_total ?? $t->total, 0, ",", ".") }}</span>
                </div>
                @empty
                <p class="text-gray-400 text-sm">Belum ada transaksi</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
