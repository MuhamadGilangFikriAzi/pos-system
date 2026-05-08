@extends("layouts.app")
@section("title", "Detail Shift #" . $shift->id)
@section("content")
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h3 class="font-bold text-gray-800 text-lg">Shift #{{ $shift->id }}</h3>
                <p class="text-sm text-gray-400">Kasir: {{ $shift->user->name ?? '-' }}</p>
            </div>
            <div>
                @if($shift->status === 'open')
                <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-medium">🟢 Sedang Buka</span>
                @else
                <span class="bg-gray-100 text-gray-500 px-3 py-1 rounded-full text-sm font-medium">🔴 Ditutup</span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-gray-50 rounded-lg p-3 text-center">
                <p class="text-xs text-gray-400">Buka</p>
                <p class="font-bold text-gray-700 mt-1">{{ $shift->opened_at ? $shift->opened_at->format('d/m/Y H:i') : '-' }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3 text-center">
                <p class="text-xs text-gray-400">Tutup</p>
                <p class="font-bold text-gray-700 mt-1">{{ $shift->closed_at ? $shift->closed_at->format('d/m/Y H:i') : '-' }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3 text-center">
                <p class="text-xs text-gray-400">Lama Shift</p>
                <p class="font-bold text-gray-700 mt-1">
                    @if($shift->opened_at)
                        @php $diff = ($shift->closed_at ?? now())->diff($shift->opened_at); @endphp
                        {{ $diff->h }}j {{ $diff->i }}m
                    @else -
                    @endif
                </p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3 text-center">
                <p class="text-xs text-gray-400">Outlet</p>
                <p class="font-bold text-gray-700 mt-1">#{{ $shift->outlet_id }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
            <div class="bg-blue-50 rounded-lg p-3 text-center">
                <p class="text-xs text-blue-400 uppercase">Saldo Awal</p>
                <p class="text-lg font-bold text-blue-700 mt-1">Rp{{ number_format($shift->cash_initial, 0, ',', '.') }}</p>
            </div>
            <div class="bg-green-50 rounded-lg p-3 text-center">
                <p class="text-xs text-green-400 uppercase">Penjualan</p>
                <p class="text-lg font-bold text-green-700 mt-1">Rp{{ number_format($shift->total_sales, 0, ',', '.') }}</p>
            </div>
            <div class="bg-{{ $shift->cash_difference >= 0 ? 'green' : 'red' }}-50 rounded-lg p-3 text-center">
                <p class="text-xs text-{{ $shift->cash_difference >= 0 ? 'green' : 'red' }}-400 uppercase">Uang Fisik</p>
                <p class="text-lg font-bold text-{{ $shift->cash_difference >= 0 ? 'green' : 'red' }}-700 mt-1">
                    @if($shift->cash_actual > 0)
                        Rp{{ number_format($shift->cash_actual, 0, ',', '.') }}
                    @else -
                    @endif
                </p>
            </div>
            <div class="bg-{{ $shift->cash_difference >= 0 ? 'green' : 'red' }}-50 rounded-lg p-3 text-center">
                <p class="text-xs text-{{ $shift->cash_difference >= 0 ? 'green' : 'red' }}-400 uppercase">Selisih</p>
                <p class="text-lg font-bold text-{{ $shift->cash_difference >= 0 ? 'green' : 'red' }}-700 mt-1">
                    @if($shift->cash_actual > 0)
                        Rp{{ number_format($shift->cash_difference, 0, ',', '.') }}
                    @else -
                    @endif
                </p>
            </div>
        </div>

        @if($shift->notes)
        <div class="mt-4 bg-gray-50 rounded-lg p-3">
            <p class="text-xs text-gray-400">Catatan</p>
            <p class="text-sm text-gray-700 mt-1">{{ $shift->notes }}</p>
        </div>
        @endif
    </div>

    <!-- Transaksi shift ini -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h4 class="font-semibold text-gray-800 mb-4">Transaksi Shift Ini ({{ $shift->transactions->count() }})</h4>

        @if($shift->transactions->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left">
                        <th class="px-3 py-2">Invoice</th>
                        <th class="px-3 py-2">Waktu</th>
                        <th class="px-3 py-2">Item</th>
                        <th class="px-3 py-2 text-right">Total</th>
                        <th class="px-3 py-2 text-right">Diskon</th>
                        <th class="px-3 py-2 text-right">Grand Total</th>
                        <th class="px-3 py-2">Metode</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($shift->transactions as $t)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-3 py-2 font-medium">{{ $t->invoice_number }}</td>
                        <td class="px-3 py-2 text-xs text-gray-500">{{ $t->created_at->format('H:i') }}</td>
                        <td class="px-3 py-2">{{ $t->items->count() }}</td>
                        <td class="px-3 py-2 text-right">Rp{{ number_format($t->total, 0, ',', '.') }}</td>
                        <td class="px-3 py-2 text-right text-red-500">-Rp{{ number_format($t->items->sum('discount_amount') + ($t->discount_amount ?? 0), 0, ',', '.') }}</td>
                        <td class="px-3 py-2 text-right font-bold text-green-600">Rp{{ number_format($t->grand_total ?? $t->total, 0, ',', '.') }}</td>
                        <td class="px-3 py-2 text-xs">{{ $t->payment_method ?? 'cash' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-center py-8 text-gray-400">Belum ada transaksi di shift ini</p>
        @endif
    </div>
</div>
@endsection
