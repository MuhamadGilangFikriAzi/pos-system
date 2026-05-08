@extends("layouts.app")
@section("title", "Supplier")
@section("content")
<div class="bg-white rounded-xl shadow-sm p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="font-semibold text-gray-800 text-lg">🏢 Manajemen Supplier</h3>
        <a href="{{ route('inventory.suppliers.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">+ Tambah Supplier</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="bg-gray-50 text-left">
                <th class="px-3 py-2">Kode</th><th class="px-3 py-2">Nama</th><th class="px-3 py-2">Kontak</th>
                <th class="px-3 py-2">Telepon</th><th class="px-3 py-2">Email</th>
                <th class="px-3 py-2">Status</th><th class="px-3 py-2"></th>
            </tr></thead>
            <tbody>
                @forelse($suppliers as $s)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-3 py-2 font-mono text-xs">{{ $s->code }}</td>
                    <td class="px-3 py-2 font-medium">{{ $s->name }}</td>
                    <td class="px-3 py-2 text-xs">{{ $s->contact_person }}</td>
                    <td class="px-3 py-2 text-xs">{{ $s->phone }}</td>
                    <td class="px-3 py-2 text-xs">{{ $s->email }}</td>
                    <td class="px-3 py-2">@if($s->is_active)<span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">Aktif</span>@else<span class="bg-red-100 text-red-500 px-2 py-0.5 rounded-full text-xs">Nonaktif</span>@endif</td>
                    <td class="px-3 py-2"><div class="flex gap-2"><a href="{{ route('inventory.suppliers.edit', $s) }}" class="text-indigo-600 text-xs">Edit</a>
                        <form method="POST" action="{{ route('inventory.suppliers.destroy', $s) }}" onsubmit="return confirm('Hapus supplier {{ $s->name }}?')">@csrf @method('DELETE')<button class="text-red-500 text-xs">Hapus</button></form>
                    </div></td>
                </tr>
                @empty <tr><td colspan="7" class="text-center py-8 text-gray-400">Belum ada supplier</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $suppliers->links() }}</div>
</div>
@endsection
