@extends("layouts.app")
@section("title", "Riwayat Mutasi Stock")
@section("content")
<div class="bg-white rounded-xl shadow-sm p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="font-semibold text-gray-800 text-lg">📋 Riwayat Mutasi Stock</h3>
        <div class="flex gap-2">
            <a href="{{ route('inventory.stock.in') }}" class="bg-green-600 text-white px-3 py-2 rounded-lg text-xs hover:bg-green-700 transition">➕ Masuk</a>
            <a href="{{ route('inventory.stock.out') }}" class="bg-orange-600 text-white px-3 py-2 rounded-lg text-xs hover:bg-orange-700 transition">➖ Keluar</a>
            <a href="{{ route('inventory.stock.adjust') }}" class="bg-amber-600 text-white px-3 py-2 rounded-lg text-xs hover:bg-amber-700 transition">🔄 Adjustment</a>
            <a href="{{ route('inventory.stock.transfer') }}" class="bg-blue-600 text-white px-3 py-2 rounded-lg text-xs hover:bg-blue-700 transition">📤 Transfer</a>
            <a href="{{ route('inventory.stock.export') }}?{{ request()->getQueryString() }}" class="bg-gray-100 text-gray-600 px-3 py-2 rounded-lg text-xs hover:bg-gray-200 transition">📥 Export</a>
        </div>
    </div>

    <form method="GET" class="flex flex-wrap gap-3 mb-6 p-4 bg-gray-50 rounded-lg">
        <div><label class="block text-xs text-gray-400 mb-1">Tipe</label>
            <select name="type" class="border rounded-lg px-3 py-2 text-sm"><option value="">Semua</option>
                @foreach($types as $t)<option value="{{ $t }}" {{ request('type')==$t ? 'selected':'' }}>{{ ucfirst($t) }}</option>@endforeach
            </select></div>
        <div><label class="block text-xs text-gray-400 mb-1">Gudang</label>
            <select name="warehouse_id" class="border rounded-lg px-3 py-2 text-sm"><option value="">Semua</option>
                @foreach($warehouses as $w)<option value="{{ $w->id }}" {{ request('warehouse_id')==$w->id ? 'selected':'' }}>{{ $w->name }}</option>@endforeach
            </select></div>
        <div><label class="block text-xs text-gray-400 mb-1">Dari</label><input type="date" name="start_date" value="{{ request('start_date', today()->startOfMonth()->format('Y-m-d')) }}" class="border rounded-lg px-3 py-2 text-sm"></div>
        <div><label class="block text-xs text-gray-400 mb-1">Sampai</label><input type="date" name="end_date" value="{{ request('end_date', today()->format('Y-m-d')) }}" class="border rounded-lg px-3 py-2 text-sm"></div>
        <div class="flex items-end"><button class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">Filter</button></div>
        <div class="flex items-end"><a href="{{ route('inventory.stock.index') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Reset</a></div>
    </form>

    <div class="overflow-x-auto">
        <table class="w-full text-sm"><thead><tr class="bg-gray-50 text-left">
            <th class="px-3 py-2">Waktu</th><th class="px-3 py-2">Produk</th><th class="px-3 py-2">Tipe</th>
            <th class="px-3 py-2 text-right">Qty</th><th class="px-3 py-2 text-right">Stok Awal</th><th class="px-3 py-2 text-right">Stok Akhir</th>
            <th class="px-3 py-2">Gudang</th><th class="px-3 py-2">User</th><th class="px-3 py-2">Referensi</th><th class="px-3 py-2"></th>
        </tr></thead><tbody>
            @forelse($mutations as $m)
            <tr class="border-b hover:bg-gray-50">
                <td class="px-3 py-2 text-xs text-gray-400 whitespace-nowrap">{{ $m->created_at->format('d/m H:i') }}</td>
                <td class="px-3 py-2 text-xs font-medium">{{ Str::limit($m->product->name ?? '-', 20) }}</td>
                <td class="px-3 py-2"><span class="text-xs px-2 py-0.5 rounded-full
                    @if($m->type=='in') bg-green-100 text-green-700
                    @elseif($m->type=='out') bg-red-100 text-red-600
                    @elseif($m->type=='adjustment') bg-amber-100 text-amber-700
                    @else bg-gray-100 text-gray-600 @endif">{{ ucfirst($m->type) }}</span></td>
                <td class="px-3 py-2 text-right font-semibold {{ $m->quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                    @if($m->quantity > 0)+@endif{{ number_format($m->quantity, 0) }}</td>
                <td class="px-3 py-2 text-right text-xs">{{ number_format($m->stock_before, 0) }}</td>
                <td class="px-3 py-2 text-right text-xs font-semibold">{{ number_format($m->stock_after, 0) }}</td>
                <td class="px-3 py-2 text-xs">{{ $m->warehouse->name ?? '-' }}</td>
                <td class="px-3 py-2 text-xs">{{ $m->user->name ?? '-' }}</td>
                <td class="px-3 py-2 text-xs text-gray-400 max-w-[120px] truncate">{{ $m->reference ?? '' }}</td>
                <td class="px-3 py-2"><a href="{{ route('inventory.stock.show', $m) }}" class="text-indigo-600 text-xs hover:underline">Detail</a></td>
            </tr>
            @empty <tr><td colspan="10" class="text-center py-8 text-gray-400">Belum ada mutasi</td></tr>
            @endforelse
        </tbody></table>
    </div>
    <div class="mt-4">{{ $mutations->appends(request()->query())->links() }}</div>
</div>
@endsection
