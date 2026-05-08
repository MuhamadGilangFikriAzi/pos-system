@extends("layouts.app")
@section("title", "Stock Opname")
@section("content")
<div class="bg-white rounded-xl shadow-sm p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="font-semibold text-gray-800 text-lg">📐 Stock Opname</h3>
        <a href="{{ route('inventory.opname.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">+ Opname Baru</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm"><thead><tr class="bg-gray-50 text-left">
            <th class="px-3 py-2">No. Opname</th><th class="px-3 py-2">Gudang</th><th class="px-3 py-2">User</th>
            <th class="px-3 py-2">Tanggal</th><th class="px-3 py-2 text-right">Items</th><th class="px-3 py-2">Status</th><th class="px-3 py-2"></th>
        </tr></thead><tbody>
            @forelse($opnames as $o)
            @php $rc = ['draft'=>'gray','in_progress'=>'blue','completed'=>'green','cancelled'=>'red']; @endphp
            <tr class="border-b hover:bg-gray-50">
                <td class="px-3 py-2 font-mono text-xs font-medium">{{ $o->opname_number }}</td>
                <td class="px-3 py-2 text-xs">{{ $o->warehouse->name ?? '-' }}</td>
                <td class="px-3 py-2 text-xs">{{ $o->user->name ?? '-' }}</td>
                <td class="px-3 py-2 text-xs">{{ $o->opname_date }}</td>
                <td class="px-3 py-2 text-right text-xs">{{ $o->items->count() }}</td>
                <td class="px-3 py-2"><span class="bg-{{ $rc[$o->status] ?? 'gray' }}-100 text-{{ $rc[$o->status] ?? 'gray' }}-700 px-2 py-0.5 rounded-full text-xs font-medium">{{ ucfirst(str_replace('_',' ',$o->status)) }}</span></td>
                <td class="px-3 py-2"><a href="{{ route('inventory.opname.show', $o) }}" class="text-indigo-600 text-xs hover:underline">Detail</a></td>
            </tr>
            @empty <tr><td colspan="7" class="text-center py-8 text-gray-400">Belum ada opname</td></tr>
            @endforelse
        </tbody></table>
    </div>
    <div class="mt-4">{{ $opnames->links() }}</div>
</div>
@endsection
