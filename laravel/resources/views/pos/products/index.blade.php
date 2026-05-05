@extends("layouts.app")
@section("title", "Produk")
@section("content")
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold">Daftar Produk</h3>
        <a href="{{ route("products.create") }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">+ Tambah</a>
    </div>
    <div class="overflow-x-auto">
    <table class="w-full">
        <thead><tr class="border-b text-left text-sm text-gray-500"><th class="py-3">Kode</th><th class="py-3">Nama</th><th class="py-3">Kategori</th><th class="py-3">Harga Beli</th><th class="py-3">Harga Jual</th><th class="py-3">Stok</th><th class="py-3">Aksi</th></tr></thead>
        <tbody>
            @forelse($products as $p)
            <tr class="border-b"><td class="py-3 text-sm">{{ $p->code }}</td><td class="py-3">{{ $p->name }}</td><td class="py-3 text-sm">{{ $p->category->name ?? "-" }}</td>
                <td class="py-3 text-sm">Rp {{ number_format($p->purchase_price, 0, ",", ".") }}</td><td class="py-3 text-sm">Rp {{ number_format($p->selling_price, 0, ",", ".") }}</td>
                <td class="py-3"><span class="px-2 py-1 rounded text-sm @if($p->stock <= 5) bg-red-100 text-red-600 @else bg-green-100 text-green-600 @endif">{{ $p->stock }} {{ $p->unit }}</span></td>
                <td class="py-3 text-sm">
                    <a href="{{ route("products.edit", $p) }}" class="text-indigo-600 hover:underline">Edit</a>
                    <form method="POST" action="{{ route("products.destroy", $p) }}" class="inline" onsubmit="return confirm('Yakin?')">@csrf @method("DELETE")<button type="submit" class="text-red-600 ml-2 hover:underline">Hapus</button></form>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center py-8 text-gray-400">Belum ada produk</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection