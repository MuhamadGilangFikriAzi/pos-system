@extends("layouts.app")
@section("title", "Kategori")
@section("content")
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold">Daftar Kategori</h3>
        <a href="{{ route("categories.create") }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">+ Tambah</a>
    </div>
    <table class="w-full">
        <thead><tr class="border-b text-left text-sm text-gray-500"><th class="py-3">Nama</th><th class="py-3">Deskripsi</th><th class="py-3">Aksi</th></tr></thead>
        <tbody>
            @forelse($categories as $c)
            <tr class="border-b"><td class="py-3">{{ $c->name }}</td><td class="py-3 text-gray-500">{{ $c->description ?? "-" }}</td>
                <td class="py-3">
                    <a href="{{ route("categories.edit", $c) }}" class="text-indigo-600 text-sm hover:underline">Edit</a>
                    <form method="POST" action="{{ route("categories.destroy", $c) }}" class="inline" onsubmit="return confirm('Yakin?')">@csrf @method("DELETE")<button type="submit" class="text-red-600 text-sm ml-2 hover:underline">Hapus</button></form>
                </td>
            </tr>
            @empty
            <tr><td colspan="3" class="text-center py-8 text-gray-400">Belum ada kategori</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection