@extends('layouts.app')
@section('title', 'Produk')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold">Produk</h2>
    <a href="{{ route('products.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">+ Tambah</a>
</div>
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">Kode</th><th class="px-4 py-3 text-left">Nama</th><th class="px-4 py-3 text-left">Kategori</th><th class="px-4 py-3 text-right">Harga Jual</th><th class="px-4 py-3 text-right">Stok</th><th class="px-4 py-3">Aksi</th></tr></thead>
        <tbody>
            @foreach(\ as \)
            <tr class="border-t">
                <td class="px-4 py-3 font-mono text-sm">{{ \->code }}</td>
                <td class="px-4 py-3">{{ \->name }}</td>
                <td class="px-4 py-3">{{ \->category->name }}</td>
                <td class="px-4 py-3 text-right">Rp {{ number_format(\->selling_price, 0, ',', '.') }}</td>
                <td class="px-4 py-3 text-right @if(\->stock <= 5) text-red-600 font-bold @endif">{{ \->stock }} {{ \->unit }}</td>
                <td class="px-4 py-3 text-center">
                    <a href="{{ route('products.edit', \) }}" class="text-blue-600 hover:text-blue-800 mr-2">Edit</a>
                    <form action="{{ route('products.destroy', \) }}" method="POST" class="inline">
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
