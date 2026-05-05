@extends("layouts.app")
@section("title", isset($category) ? "Edit Kategori" : "Tambah Kategori")
@section("content")
<div class="max-w-md mx-auto bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold mb-4">@yield("title")</h3>
    <form method="POST" action="{{ isset($category) ? route("categories.update", $category) : route("categories.store") }}">
        @csrf @if(isset($category)) @method("PUT") @endif
        <div class="mb-4">
            <label class="block text-gray-700 mb-2">Nama Kategori</label>
            <input type="text" name="name" value="{{ old("name", $category->name ?? "") }}" class="w-full border rounded px-3 py-2 @error("name") border-red-500 @enderror" required>
            @error("name")<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 mb-2">Deskripsi</label>
            <textarea name="description" rows="3" class="w-full border rounded px-3 py-2">{{ old("description", $category->description ?? "") }}</textarea>
        </div>
        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700">{{ isset($category) ? "Update" : "Simpan" }}</button>
        <a href="{{ route("categories.index") }}" class="ml-2 text-gray-500 hover:underline">Batal</a>
    </form>
</div>
@endsection