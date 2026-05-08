@extends('layouts.app')

@section('title', 'Tambah Mutasi Stok')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('stock.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Kembali</a>
        <h1 class="text-2xl font-bold text-gray-800 mt-2">Tambah Mutasi Stok</h1>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('stock.store') }}" id="stockForm">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Produk</label>
                <select name="product_id" id="product_id" class="w-full border rounded-lg px-3 py-2 text-sm @error('product_id') border-red-500 @enderror" required>
                    <option value="">-- Pilih Produk --</option>
                    @foreach($products as $p)
                    <option value="{{ $p->id }}" data-stock="{{ $p->stock }}" {{ old('product_id') == $p->id ? 'selected' : '' }}>
                        [{{ $p->code }}] {{ $p->name }} (Stok: {{ $p->stock }})
                    </option>
                    @endforeach
                </select>
                @error('product_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div id="stockInfo" class="hidden mb-4 p-3 bg-gray-50 rounded-lg text-sm text-gray-600">
                Stok saat ini: <span id="currentStock" class="font-bold">0</span>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Mutasi</label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="type" value="in" {{ old('type', 'in') == 'in' ? 'checked' : '' }} class="text-green-600">
                        <span class="text-sm">Stok Masuk</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="type" value="out" {{ old('type') == 'out' ? 'checked' : '' }} class="text-red-600">
                        <span class="text-sm">Stok Keluar</span>
                    </label>
                </div>
                @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah</label>
                <input type="number" name="quantity" value="{{ old('quantity') }}" min="1" class="w-full border rounded-lg px-3 py-2 text-sm @error('quantity') border-red-500 @enderror" required>
                @error('quantity') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Referensi <span class="text-gray-400">(opsional)</span></label>
                <input type="text" name="reference" value="{{ old('reference') }}" placeholder="Contoh: PO-001, Retur-001" class="w-full border rounded-lg px-3 py-2 text-sm @error('reference') border-red-500 @enderror">
                @error('reference') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan <span class="text-gray-400">(opsional)</span></label>
                <textarea name="note" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm @error('note') border-red-500 @enderror" placeholder="Catatan tambahan...">{{ old('note') }}</textarea>
                @error('note') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-3 rounded-lg hover:bg-blue-700 transition">
                Simpan Mutasi Stok
            </button>
        </form>
    </div>
</div>

<script>
document.getElementById('product_id').addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    const stock = opt ? opt.dataset.stock : 0;
    document.getElementById('currentStock').textContent = stock;
    document.getElementById('stockInfo').classList.toggle('hidden', !this.value);
});
// Trigger on load if old value
window.addEventListener('DOMContentLoaded', function() {
    const e = document.getElementById('product_id');
    if (e.value) { e.dispatchEvent(new Event('change')); }
});
</script>
@endsection
