@extends("layouts.app")
@section("title", "Monitor Kasir")
@section("content")
<div class="mb-6">
    <h3 class="font-semibold text-gray-800 text-lg mb-2">📋 Monitor Kasir</h3>
    <p class="text-sm text-gray-400">Pantau aktivitas seluruh kasir secara realtime</p>
</div>

<!-- Stats overview -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-5">
        <p class="text-xs text-gray-400 uppercase">Total Kasir</p>
        <p class="text-2xl font-bold text-gray-700 mt-1">{{ $total_kasir ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5">
        <p class="text-xs text-gray-400 uppercase">Aktif Hari Ini</p>
        <p class="text-2xl font-bold text-green-600 mt-1">{{ $active_kasir ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5">
        <p class="text-xs text-gray-400 uppercase">Penjualan Hari Ini</p>
        <p class="text-2xl font-bold text-indigo-600 mt-1">Rp{{ number_format($todayTotal ?? 0, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5">
        <p class="text-xs text-gray-400 uppercase">Shift Aktif</p>
        <p class="text-2xl font-bold text-amber-600 mt-1">{{ $openShifts ?? 0 }}</p>
    </div>
</div>

<!-- Kasir List -->
<div class="bg-white rounded-xl shadow-sm p-6">
    <h4 class="font-semibold text-gray-800 mb-4">Performa Kasir Hari Ini</h4>

    @if(isset($kasirs) && count($kasirs) > 0)
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-left">
                    <th class="px-3 py-2">#</th>
                    <th class="px-3 py-2">Kasir</th>
                    <th class="px-3 py-2">Status</th>
                    <th class="px-3 py-2">Shift</th>
                    <th class="px-3 py-2 text-right">Transaksi</th>
                    <th class="px-3 py-2 text-right">Penjualan</th>
                    <th class="px-3 py-2">Aktivitas Terakhir</th>
                </tr>
            </thead>
            <tbody>
                @foreach($kasirs as $i => $k)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-3 py-2 font-bold text-gray-400">{{ $i + 1 }}</td>
                    <td class="px-3 py-2">
                        <span class="font-medium">{{ $k['name'] }}</span>
                        <span class="text-xs text-gray-400 block">{{ $k['email'] }}</span>
                    </td>
                    <td class="px-3 py-2">
                        @if($k['is_active'])
                        <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs font-medium">Aktif</span>
                        @else
                        <span class="bg-red-100 text-red-500 px-2 py-0.5 rounded-full text-xs font-medium">Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-3 py-2">
                        @if($k['has_active_shift'])
                        <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs font-medium">🟢 Buka</span>
                        @if($k['shift_id'])
                        <a href="{{ route('pos.shift.show', $k['shift_id']) }}" class="text-indigo-600 text-xs ml-1">#{{ $k['shift_id'] }}</a>
                        @endif
                        @else
                        <span class="bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full text-xs font-medium">🔴 Tutup</span>
                        @endif
                    </td>
                    <td class="px-3 py-2 text-right font-semibold">{{ $k['today_count'] }}</td>
                    <td class="px-3 py-2 text-right font-semibold text-green-600">Rp{{ number_format($k['today_sales'], 0, ',', '.') }}</td>
                    <td class="px-3 py-2 text-xs text-gray-400">{{ $k['last_activity'] ? \Carbon\Carbon::parse($k['last_activity'])->diffForHumans() : '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="text-center py-10 text-gray-400">Belum ada data kasir</div>
    @endif
</div>
@endsection
