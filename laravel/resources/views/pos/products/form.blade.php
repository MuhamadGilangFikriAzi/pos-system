@extends("layouts.app")
@section("title", isset($product) ? "Edit Produk" : "Tambah Produk")
@section("content")
<div class="max-w-lg mx-auto bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold mb-4">@yield("title")</h3>
    <form method="POST" action="{{ isset($product) ? route("products.update", $product) : route("products.store") }}">
        @csrf @if(isset($product)) @method("PUT") @endif
        <div class="grid grid-cols-2 gap-4">
            <div class="mb-4"><label class="block text-gray-700 mb-2">Kode Produk</label><input type="text" name="code" value="{{ old("code", $product->code ?? "") }}" class="w-full border rounded px-3 py-2 @error("code") border-red-500 @enderror" required>@error("code")<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror</div>
            <div class="mb-4"><label class="block text-gray-700 mb-2">Nama Produk</label><input type="text" name="name" value="{{ old("name", $product->name ?? "") }}" class="w-full border rounded px-3 py-2 @error("name") border-red-500 @enderror" required>@error("name")<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror</div>
            <div class="mb-4"><label class="block text-gray-700 mb-2">Kategori</label><select name="category_id" class="w-full border rounded px-3 py-2 @error("category_id") border-red-500 @enderror" required>
                <option value="">- Pilih -</option>
                @foreach($categories as $c)<option value="{{ $c->id }}" @if(old("category_id", $product->category_id ?? "") == $c->id) selected @endif>{{ $c->name }}</option>@endforeach
            </select>@error("category_id")<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror</div>
            <div class="mb-4"><label class="block text-gray-700 mb-2">Satuan</label><input type="text" name="unit" value="{{ old("unit", $product->unit ?? "pcs") }}" class="w-full border rounded px-3 py-2" required></div>
            <div class="mb-4"><label class="block text-gray-700 mb-2">Harga Beli</label><input type="number" name="purchase_price" value="{{ old("purchase_price", $product->purchase_price ?? 0) }}" class="w-full border rounded px-3 py-2" required></div>
            <div class="mb-4"><label class="block text-gray-700 mb-2">Harga Jual</label><input type="number" name="selling_price" value="{{ old("selling_price", $product->selling_price ?? 0) }}" class="w-full border rounded px-3 py-2" required></div>
            <div class="mb-4"><label class="block text-gray-700 mb-2">Stok Awal</label><input type="number" name="stock" value="{{ old("stock", $product->stock ?? 0) }}" class="w-full border rounded px-3 py-2" required></div>
        </div>
        <div class="mb-4"><label class="block text-gray-700 mb-2">Deskripsi</label><textarea name="description" rows="2" class="w-full border rounded px-3 py-2">{{ old("description", $product->description ?? "") }}</textarea></div>
        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700">{{ isset($product) ? "Update" : "Simpan" }}</button>
        <a href="{{ route("products.index") }}" class="ml-2 text-gray-500 hover:underline">Batal</a>
    </form>
</div>
@endsection