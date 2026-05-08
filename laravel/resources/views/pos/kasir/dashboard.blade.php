@extends("layouts.app")
@section("title", "Dashboard Kasir")
@section("content")
@php $user = Auth::user(); @endphp

<!-- Shift Status Banner -->
@if(isset($activeShift) && $activeShift)
<div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6 flex justify-between items-center">
    <div>
        <span class="text-green-700 font-semibold">🟢 Shift #{{ $activeShift->id }} Sedang Aktif</span>
        <span class="text-green-600 text-sm ml-3">Buka: {{ $activeShift->opened_at->format('H:i') }}</span>
        <span class="text-green-600 text-sm ml-3">Saldo awal: Rp{{ number_format($activeShift->cash_initial, 0, ',', '.') }}</span>
    </div>
    <a href="{{ route('pos.shift.close') }}" class="bg-amber-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-amber-700 transition">🔒 Tutup Shift</a>
</div>
@else
<div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 flex justify-between items-center">
    <div>
        <span class="text-amber-700 font-semibold">⚠️ Shift Belum Dibuka</span>
        <span class="text-amber-600 text-sm ml-3">Anda harus buka shift sebelum bisa bertransaksi</span>
    </div>
    <a href="{{ route('pos.shift.open') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">🚀 Buka Shift</a>
</div>
@endif

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-5">
        <p class="text-xs text-gray-400 uppercase tracking-wide">Penjualan Hari Ini</p>
        <p class="text-2xl font-bold text-green-600 mt-1">Rp{{ number_format($today_sales ?? 0, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5">
        <p class="text-xs text-gray-400 uppercase tracking-wide">Transaksi Hari Ini</p>
        <p class="text-2xl font-bold text-indigo-600 mt-1">{{ $today_transactions ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5">
        <p class="text-xs text-gray-400 uppercase tracking-wide">Penjualan 7 Hari</p>
        <p class="text-2xl font-bold text-orange-600 mt-1">Rp{{ number_format($week_sales ?? 0, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5">
        <p class="text-xs text-gray-400 uppercase tracking-wide">Tunai Hari Ini</p>
        <p class="text-2xl font-bold text-blue-600 mt-1">Rp{{ number_format($today_cash ?? 0, 0, ',', '.') }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Transactions -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h4 class="font-semibold text-gray-800 mb-4">Transaksi Hari Ini</h4>
        @if(isset($todayTransactions) && $todayTransactions->count() > 0)
        <div class="space-y-2 max-h-80 overflow-y-auto">
            @foreach($todayTransactions as $t)
            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                <div>
                    <span class="font-medium text-sm">{{ $t->invoice_number }}</span>
                    <span class="text-xs text-gray-400 ml-2">{{ $t->created_at->format('H:i') }}</span>
                    <div class="text-xs text-gray-400">{{ $t->items->count() }} item</div>
                </div>
                <div class="text-right">
                    <span class="font-bold text-green-600">Rp{{ number_format($t->grand_total ?? $t->total, 0, ',', '.') }}</span>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-10 text-gray-400">
            <div class="text-4xl mb-3">🛒</div>
            Belum ada transaksi hari ini
        </div>
        @endif
    </div>

    <!-- Recent Activity -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h4 class="font-semibold text-gray-800 mb-4">Aktivitas Terbaru</h4>
        @if(isset($recentActivities) && $recentActivities->count() > 0)
        <div class="space-y-2 max-h-80 overflow-y-auto">
            @foreach($recentActivities as $log)
            <div class="flex items-start gap-3 p-2 border-b border-gray-50">
                <div class="text-lg">
                    @switch($log->action)
                        @case('login') 🔓 @break
                        @case('logout') 🔒 @break
                        @case('open_shift') 🟢 @break
                        @case('close_shift') 🔴 @break
                        @default 📝
                    @endswitch
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-gray-700 truncate">{{ $log->description ?? $log->action }}</p>
                    <p class="text-xs text-gray-400">{{ $log->created_at->diffForHumans() }}</p>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-10 text-gray-400">
            <div class="text-4xl mb-3">📝</div>
            Belum ada aktivitas
        </div>
        @endif
        <div class="mt-4">
            <a href="{{ route('pos.kasir.activity') }}" class="text-indigo-600 text-sm hover:underline">Lihat semua aktivitas →</a>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
    <a href="{{ route('pos.index') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl p-4 text-center transition">
        <div class="text-3xl mb-2">🧾</div>
        <div class="text-sm font-semibold">POS Kasir</div>
    </a>
    <a href="{{ route('pos.kasir.transactions') }}" class="bg-blue-600 hover:bg-blue-700 text-white rounded-xl p-4 text-center transition">
        <div class="text-3xl mb-2">📋</div>
        <div class="text-sm font-semibold">Riwayat Transaksi</div>
    </a>
    <a href="{{ route('pos.shift.history') }}" class="bg-amber-600 hover:bg-amber-700 text-white rounded-xl p-4 text-center transition">
        <div class="text-3xl mb-2">🕐</div>
        <div class="text-sm font-semibold">Riwayat Shift</div>
    </a>
    <a href="{{ route('pos.kasir.export') }}" class="bg-green-600 hover:bg-green-700 text-white rounded-xl p-4 text-center transition">
        <div class="text-3xl mb-2">📥</div>
        <div class="text-sm font-semibold">Export CSV</div>
    </a>
</div>
@endsection
