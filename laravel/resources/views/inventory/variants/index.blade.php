@extends("layouts.app")
@section("title", "Varian - " . $product->name)
@section("content")
<div class="max-w-2xl mx-auto" x-data="{ showForm: false, vname: '', vsku: '', vbarcode: '', vprice: 0 }">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex justify-between items-center mb-4">
            <div><h3 class="font-semibold text-gray-800 text-lg">Variasi Produk</h3>
                <p class="text-sm text-gray-400">{{ $product->name }} ({{ $product->code }})</p>
            </div>
            <button @click="showForm = !showForm" class="bg-indigo-600 text-white px-3 py-2 rounded-lg text-xs hover:bg-indigo-700 transition">+ Tambah Varian</button>
        </div>

        <form x-show="showForm" method="POST" action="{{ route('inventory.variants.store') }}" class="mb-6 p-4 bg-gray-50 rounded-lg space-y-3" x-cloak>
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <div class="grid grid-cols-2 gap-3">
                <div><label class="text-xs text-gray-400">Nama Varian</label>
                    <input type="text" name="name" x-model="vname" required class="w-full border rounded px-3 py-2 text-sm"></div>
                <div><label class="text-xs text-gray-400">SKU (kosongkan untuk auto)</label>
                    <input type="text" name="sku" x-model="vsku" class="w-full border rounded px-3 py-2 text-sm"></div>
                <div><label class="text-xs text-gray-400">Barcode</label>
                    <input type="text" name="barcode" x-model="vbarcode" class="w-full border rounded px-3 py-2 text-sm"></div>
                <div><label class="text-xs text-gray-400">Price Modifier</label>
                    <input type="number" name="price_modifier" x-model="vprice" step="100" class="w-full border rounded px-3 py-2 text-sm"></div>
            </div>
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">Simpan</button>
        </form>

        <div class="space-y-2">
            @forelse($variants as $v)
            <div class="flex items-center justify-between p-3 border rounded-lg">
                <div>
                    <span class="font-medium text-sm">{{ $v->name }}</span>
                    <span class="text-xs text-gray-400 ml-2">SKU: {{ $v->sku }}</span>
                    @if($v->barcode)<span class="text-xs text-gray-400 ml-2">Barcode: {{ $v->barcode }}</span>@endif
                    @if($v->price_modifier != 0)<span class="text-xs text-{{ $v->price_modifier > 0 ? 'green' : 'red' }}-600 ml-2">{{ $v->price_modifier > 0 ? '+' : '' }}Rp{{ number_format($v->price_modifier, 0, ',', '.') }}</span>@endif
                </div>
                <div class="flex gap-2">
                    <form method="POST" action="{{ route('inventory.variants.update', $v) }}" class="inline">
                        @csrf @method('PUT')
                        <input type="hidden" name="is_active" value="{{ $v->is_active ? '0' : '1' }}">
                        <button class="text-xs {{ $v->is_active ? 'text-green-600' : 'text-gray-400' }}">{{ $v->is_active ? 'Aktif' : 'Nonaktif' }}</button>
                    </form>
                    <form method="POST" action="{{ route('inventory.variants.destroy', $v) }}" onsubmit="return confirm('Hapus varian {{ $v->name }}?')" class="inline">@csrf @method('DELETE')<button class="text-red-500 text-xs">Hapus</button></form>
                </div>
            </div>
            @empty
            <div class="text-center py-6 text-gray-400">Belum ada varian</div>
            @endforelse
        </div>
    </div>
</div>
<style>[x-cloak]{display:none!important}</style>
@endsection
