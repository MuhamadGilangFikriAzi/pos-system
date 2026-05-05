@extends('layouts.app')
@section('title', 'Form Kategori')
@section('content')
<div class="max-w-lg mx-auto bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold mb-6">{{ isset(\) ? 'Edit' : 'Tambah' }} Kategori</h2>
    <form action="{{ isset(\) ? route('categories.update', \) : route('categories.store') }}" method="POST">
        @csrf @if(isset(\)) @method('PUT') @endif
        <div class="mb-4">
            <label class="block text-gray-700 mb-2">Nama Kategori</label>
            <input type="text" name="name" value="{{ old('name', \->name ?? '') }}" class="w-full border rounded px-3 py-2 @error('name') border-red-500 @enderror" required>
            @error('name')<p class="text-red-500 text-sm mt-1">{{ \ }}</p>@enderror
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 mb-2">Deskripsi</label>
            <textarea name="description" rows="3" class="w-full border rounded px-3 py-2">{{ old('description', \->description ?? '') }}</textarea>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">Simpan</button>
            <a href="{{ route('categories.index') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400">Batal</a>
        </div>
    </form>
</div>
@endsection
