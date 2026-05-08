@extends("layouts.app")
@section("title", "Detail Mutasi")
@section("content")
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex justify-between items-start mb-4">
            <h3 class="font-bold text-gray-800 text-lg">Detail Mutasi #{{ $mutation->id }}</h3>
            <a href="{{ route('inventory.stock.index') }}" class="text-sm text-gray-500 hover:underline">Kembali</a>
        </div>

        <div class="grid grid-cols-2 gap-4 p-4 bg-gray-50 rounded-lg mb-4">
            <div><p class="text-xs text-gray-400">Tanggal</p><p class="font-medium">{{ $mutation->created_at->format('d/m/Y H:i:s') }}</p></div>
            <div><p class="text-xs text-gray-400">Tipe</p><span class="px-2 py-0.5 rounded-full text-xs font-medium
                @if($mutation->type=='in') bg-green-100 text-green-700
                @elseif($mutation->type=='out') bg-red-100 text-red-600
                @else bg-amber-100 text-amber-700 @endif">{{ ucfirst($mutation->type) }}</span></div>
            <div><p class="text-xs text-gray-400">Produk</p><p class="font-medium">{{ $mutation->product->name ?? '-' }}</p></div>
            <div><p class="text-xs text-gray-400">Varian</p><p>{{ $mutation->variant->name ?? '-' }}</p></div>
            <div><p class="text-xs text-gray-400">Gudang</p><p>{{ $mutation->warehouse->name ?? '-' }}</p></div>
            <div><p class="text-xs text-gray-400">User</p><p>{{ $mutation->user->name ?? '-' }}</p></div>
            <div><p class="text-xs text-gray-400">Stok Sebelum</p><p class="font-mono">{{ number_format($mutation->stock_before, 0) }}</p></div>
            <div><p class="text-xs text-gray-400">Jumlah Mutasi</p><p class="font-bold {{ $mutation->quantity > 0 ? 'text-green-600' : 'text-red-600' }}">{{ $mutation->quantity > 0 ? '+':'' }}{{ number_format($mutation->quantity, 0) }}</p></div>
            <div><p class="text-xs text-gray-400">Stok Setelah</p><p class="font-mono font-bold">{{ number_format($mutation->stock_after, 0) }}</p></div>
            <div><p class="text-xs text-gray-400">Harga Satuan</p><p>Rp{{ number_format($mutation->unit_cost, 0, ',', '.') }}</p></div>
        </div>

        @if($mutation->reference)
        <div class="mb-2"><p class="text-xs text-gray-400">Referensi</p><p class="text-sm">{{ $mutation->reference }}</p></div>
        @endif
        @if($mutation->notes)
        <div class="mb-2"><p class="text-xs text-gray-400">Catatan</p><p class="text-sm">{{ $mutation->notes }}</p></div>
        @endif
    </div>
</div>
@endsection
