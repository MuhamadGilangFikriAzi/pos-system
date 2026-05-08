@extends("layouts.app")
@section("title", "Stock Keluar")
@section("content")
<div class="max-w-lg mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-800 text-lg mb-6">➖ Stock Keluar</h3>
        <form method="POST" action="{{ route('inventory.stock.out.store') }}" class="space-y-4">
            @csrf
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Produk</label>
                <select name="product_id" required class="w-full border-2 border-gray-200 rounded-lg px-4 py-3 focus:border-indigo-500 outline-none text-sm">
                    <option value="">— Pilih Produk —</option>
                    @foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }} ({{ $p->code }})</option>@endforeach
                </select></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Gudang</label>
                <select name="warehouse_id" required class="w-full border-2 border-gray-200 rounded-lg px-4 py-3 focus:border-indigo-500 outline-none text-sm">
                    @foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach
                </select></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Jumlah</label>
                <input type="number" name="quantity" min="0.01" step="1" required class="w-full border-2 border-gray-200 rounded-lg px-4 py-3 focus:border-indigo-500 outline-none text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Referensi</label>
                <input type="text" name="reference" class="w-full border-2 border-gray-200 rounded-lg px-4 py-3 focus:border-indigo-500 outline-none text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                <textarea name="notes" rows="2" class="w-full border-2 border-gray-200 rounded-lg px-4 py-3 focus:border-indigo-500 outline-none text-sm"></textarea></div>
            <button type="submit" class="w-full bg-orange-600 hover:bg-orange-700 text-white font-semibold py-3 px-6 rounded-lg transition">✅ Simpan Stock Keluar</button>
            <a href="{{ route('inventory.stock.index') }}" class="block text-center text-sm text-gray-500 hover:text-gray-700 mt-2">Kembali</a>
        </form>
    </div>
</div>
@endsection
