@extends('layouts.app')
@section('title', 'Form Produk')
@section('content')
<div class="max-w-lg mx-auto bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold mb-6">{{ isset(\) ? 'Edit' : 'Tambah' }} Produk</h2>
    <form action="{{ isset(\) ? route('products.update', \) : route('products.store') }}" method="POST">
        @csrf @if(isset(\)) @method('PUT') @endif
        <div class="grid grid-cols-2 gap-4">
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Kode Produk</label>
                <input type="text" name="code" value="{{ old('code', \->code ?? '') }}" class="w-full border rounded px-3 py-2 @error('code') border-red-500 @enderror" required>
                @error('code')<p class="text-red-500 text-sm mt-1">{{ \ }}</p>@enderror
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Kategori</label>
                <select name="category_id" class="w-full border rounded px-3 py-2" required>
                    @foreach(\ as \)
                    <option value="{{ \->id }}" {{ (old('category_id', \->category_id ?? '') == \->id) ? 'selected' : '' }}>{{ \->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 mb-2">Nama Produk</label>
            <input type="text" name="name" value="{{ old('name', \->name ?? '') }}" class="w-full border rounded px-3 py-2 @error('name') border-red-500 @enderror" required>
            @error('name')<p class="text-red-500 text-sm mt-1">{{ \ }}</p>@enderror
        </div>
        <div class="grid grid-cols-3 gap-4">
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Harga Beli</label>
                <input type="number" name="purchase_price" value="{{ old('purchase_price', \->purchase_price ?? 0) }}" class="w-full border rounded px-3 py-2" min="0">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Harga Jual</label>
                <input type="number" name="selling_price" value="{{ old('selling_price', \->selling_price ?? 0) }}" class="w-full border rounded px-3 py-2" min="0" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Stok Awal</label>
                <input type="number" name="stock" value="{{ old('stock', \->stock ?? 0) }}" class="w-full border rounded px-3 py-2" min="0">
            </div>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 mb-2">Satuan</label>
            <select name="unit" class="w-full border rounded px-3 py-2">
                <option value="pcs" {{ old('unit', \->unit ?? '') == 'pcs' ? 'selected' : '' }}>Pcs</option>
                <option value="kg" {{ old('unit', \->unit ?? '') == 'kg' ? 'selected' : '' }}>Kg</option>
                <option value="liter" {{ old('unit', \->unit ?? '') == 'liter' ? 'selected' : '' }}>Liter</option>
                <option value="pack" {{ old('unit', \->unit ?? '') == 'pack' ? 'selected' : '' }}>Pack</option>
                <option value="box" {{ old('unit', \->unit ?? '') == 'box' ? 'selected' : '' }}>Box</option>
            </select>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 mb-2">Deskripsi</label>
            <textarea name="description" rows="2" class="w-full border rounded px-3 py-2">{{ old('description', \->description ?? '') }}</textarea>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">Simpan</button>
            <a href="{{ route('products.index') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400">Batal</a>
        </div>
    </form>
</div>
@endsection
