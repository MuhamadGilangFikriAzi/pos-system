@extends("layouts.app")
@section("title", "Riwayat Transaksi")
@section("content")
<div class="bg-white rounded-xl shadow-sm p-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="font-semibold text-gray-800 text-lg">Riwayat Transaksi</h3>
        <a href="{{ route('pos.kasir.export') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700 transition">📥 Export CSV</a>
    </div>

    <!-- Filter -->
    <form method="GET" class="flex flex-wrap gap-3 mb-6 p-4 bg-gray-50 rounded-lg">
        @if(isset($kasirs) && $kasirs->count() > 0)
        <div>
            <label class="block text-xs text-gray-400 mb-1">Kasir</label>
            <select name="user_id" class="border rounded-lg px-3 py-2 text-sm">
                <option value="">Semua Kasir</option>
                @foreach($kasirs as $k)
                <option value="{{ $k->id }}" {{ request('user_id') == $k->id ? 'selected' : '' }}>{{ $k->name }} ({{ $k->role }})</option>
                @endforeach
            </select>
        </div>
        @endif
        <div>
            <label class="block text-xs text-gray-400 mb-1">Dari</label>
            <input type="date" name="start_date" value="{{ request('start_date', today()->startOfMonth()->format('Y-m-d')) }}" class="border rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-400 mb-1">Sampai</label>
            <input type="date" name="end_date" value="{{ request('end_date', today()->format('Y-m-d')) }}" class="border rounded-lg px-3 py-2 text-sm">
        </div>
        <div class="flex items-end">
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">Filter</button>
        </div>
    </form>

    <!-- Tabel -->
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-left">
                    @if(isset($kasirs) && $kasirs->count() > 0)
                    <th class="px-3 py-2">Kasir</th>
                    @endif
                    <th class="px-3 py-2">Invoice</th>
                    <th class="px-3 py-2">Tanggal</th>
                    <th class="px-3 py-2">Item</th>
                    <th class="px-3 py-2 text-right">Total</th>
                    <th class="px-3 py-2 text-right">Diskon</th>
                    <th class="px-3 py-2 text-right">Grand Total</th>
                    <th class="px-3 py-2">Metode</th>
                    <th class="px-3 py-2">Shift</th>
                    <th class="px-3 py-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $t)
                <tr class="border-b hover:bg-gray-50">
                    @if(isset($kasirs) && $kasirs->count() > 0)
                    <td class="px-3 py-2">{{ $t->user->name ?? '-' }}</td>
                    @endif
                    <td class="px-3 py-2 font-medium">{{ $t->invoice_number }}</td>
                    <td class="px-3 py-2 text-xs text-gray-500">{{ $t->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-3 py-2">{{ $t->items->count() }}</td>
                    <td class="px-3 py-2 text-right">Rp{{ number_format($t->total, 0, ',', '.') }}</td>
                    <td class="px-3 py-2 text-right text-red-500">-Rp{{ number_format($t->items->sum('discount_amount') + ($t->discount_amount ?? 0), 0, ',', '.') }}</td>
                    <td class="px-3 py-2 text-right font-bold text-green-600">Rp{{ number_format($t->grand_total ?? $t->total, 0, ',', '.') }}</td>
                    <td class="px-3 py-2 text-xs">{{ $t->payment_method ?? 'cash' }}</td>
                    <td class="px-3 py-2 text-xs">{{ $t->shift_id ? '#'.$t->shift_id : '-' }}</td>
                    <td class="px-3 py-2">
                        <a href="{{ route('pos.receipt', $t->id) }}" class="text-indigo-600 hover:underline text-xs">Cetak</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center py-8 text-gray-400">Tidak ada transaksi</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $transactions->links() }}</div>
</div>
@endsection
