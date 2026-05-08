@extends("layouts.app")
@section("title", "Riwayat Shift")
@section("content")
<div class="bg-white rounded-xl shadow-sm p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="font-semibold text-gray-800 text-lg">Riwayat Shift</h3>
        <div class="flex gap-2">
            <a href="{{ route('pos.shift.open') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">+ Buka Shift</a>
            @php $activeShift = \App\Models\Shift::getActiveShift(auth()->id(), auth()->user()->outlet_id ?? 1); @endphp
            @if($activeShift)
            <a href="{{ route('pos.shift.close') }}" class="bg-amber-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-amber-700 transition">🔒 Tutup Shift</a>
            @endif
        </div>
    </div>

    <!-- Summary -->
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-gray-50 rounded-xl p-4 text-center">
            <p class="text-xs text-gray-400 uppercase">Total Shift</p>
            <p class="text-2xl font-bold text-gray-700 mt-1">{{ $summary['total_shifts'] }}</p>
        </div>
        <div class="bg-gray-50 rounded-xl p-4 text-center">
            <p class="text-xs text-gray-400 uppercase">Total Penjualan</p>
            <p class="text-2xl font-bold text-green-600 mt-1">Rp{{ number_format($summary['total_sales'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-gray-50 rounded-xl p-4 text-center">
            <p class="text-xs text-gray-400 uppercase">Total Selisih Kas</p>
            <p class="text-2xl font-bold {{ $summary['total_difference'] >= 0 ? 'text-green-600' : 'text-red-600' }} mt-1">Rp{{ number_format($summary['total_difference'], 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- Tabel -->
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-left">
                    <th class="px-3 py-2">#Shift</th>
                    <th class="px-3 py-2">Buka</th>
                    <th class="px-3 py-2">Tutup</th>
                    <th class="px-3 py-2 text-right">Saldo Awal</th>
                    <th class="px-3 py-2 text-right">Penjualan</th>
                    <th class="px-3 py-2 text-right">Transaksi</th>
                    <th class="px-3 py-2 text-right">Uang Fisik</th>
                    <th class="px-3 py-2 text-right">Selisih</th>
                    <th class="px-3 py-2">Status</th>
                    <th class="px-3 py-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($shifts as $shift)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-3 py-2 font-medium">#{{ $shift->id }}</td>
                    <td class="px-3 py-2 text-gray-500 text-xs">{{ $shift->opened_at ? $shift->opened_at->format('d/m/y H:i') : '-' }}</td>
                    <td class="px-3 py-2 text-gray-500 text-xs">{{ $shift->closed_at ? $shift->closed_at->format('d/m/y H:i') : '-' }}</td>
                    <td class="px-3 py-2 text-right">Rp{{ number_format($shift->cash_initial, 0, ',', '.') }}</td>
                    <td class="px-3 py-2 text-right text-green-600">Rp{{ number_format($shift->total_sales, 0, ',', '.') }}</td>
                    <td class="px-3 py-2 text-right">{{ $shift->transaction_count }}</td>
                    <td class="px-3 py-2 text-right">{{ $shift->cash_actual > 0 ? 'Rp'.number_format($shift->cash_actual,0,',','.') : '-' }}</td>
                    <td class="px-3 py-2 text-right font-medium {{ $shift->cash_difference >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $shift->cash_actual > 0 ? 'Rp'.number_format($shift->cash_difference,0,',','.') : '-' }}
                    </td>
                    <td class="px-3 py-2">
                        @if($shift->status === 'open')
                        <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs font-medium">Buka</span>
                        @else
                        <span class="bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full text-xs font-medium">Tutup</span>
                        @endif
                    </td>
                    <td class="px-3 py-2">
                        <a href="{{ route('pos.shift.show', $shift->id) }}" class="text-indigo-600 hover:text-indigo-800 text-xs">Detail</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center py-8 text-gray-400">Belum ada shift</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $shifts->links() }}</div>
</div>
@endsection
