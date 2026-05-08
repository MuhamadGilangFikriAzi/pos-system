@extends("layouts.app")
@section("title", "Penyesuaian Stock")
@section("content")
<div class="max-w-lg mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-800 text-lg mb-6">🔄 Penyesuaian Stock</h3>
        <p class="text-xs text-gray-400 mb-4">Gunakan nilai positif (+) untuk menambah stock, nilai negatif (-) untuk mengurangi stock.</p>
        <form method="POST" action="{{ route('inventory.stock.adjust.store') }}" class="space-y-4">
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
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Penyesuaian (+ / -)</label>
                <input type="number" name="quantity" step="1" required class="w-full border-2 border-gray-200 rounded-lg px-4 py-3 focus:border-indigo-500 outline-none text-sm" placeholder="contoh: +10 atau -5"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Alasan / Catatan <span class="text-red-500">*</span></label>
                <textarea name="notes" rows="3" required class="w-full border-2 border-gray-200 rounded-lg px-4 py-3 focus:border-indigo-500 outline-none text-sm" placeholder="Jelaskan alasan penyesuaian..."></textarea></div>
            <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white font-semibold py-3 px-6 rounded-lg transition">✅ Simpan Penyesuaian</button>
            <a href="{{ route('inventory.stock.index') }}" class="block text-center text-sm text-gray-500 hover:text-gray-700 mt-2">Kembali</a>
        </form>
    </div>
</div>
@endsection
