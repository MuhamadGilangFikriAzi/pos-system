@extends('layouts.app')
@section('title', 'Kategori')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold">Kategori</h2>
    <a href="{{ route('categories.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">+ Tambah</a>
</div>
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">Nama</th><th class="px-4 py-3 text-left">Deskripsi</th><th class="px-4 py-3 text-left">Produk</th><th class="px-4 py-3">Aksi</th></tr></thead>
        <tbody>
            @foreach(\ as \)
            <tr class="border-t">
                <td class="px-4 py-3">{{ \->name }}</td>
                <td class="px-4 py-3 text-gray-500">{{ \->description ?? '-' }}</td>
                <td class="px-4 py-3">{{ \->products->count() }}</td>
                <td class="px-4 py-3 text-center">
                    <a href="{{ route('categories.edit', \) }}" class="text-blue-600 hover:text-blue-800 mr-2">Edit</a>
                    <form action="{{ route('categories.destroy', \) }}" method="POST" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800" onclick="return confirm('Yakin?')">Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">{{ \->links() }}</div>
@endsection
