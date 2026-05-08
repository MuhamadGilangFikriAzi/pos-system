@extends("layouts.app")
@section("title", "Purchase Orders")
@section("content")
<div class="bg-white rounded-xl shadow-sm p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="font-semibold text-gray-800 text-lg">📋 Purchase Orders</h3>
        <a href="{{ route('inventory.purchase-orders.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">+ PO Baru</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="bg-gray-50 text-left">
                <th class="px-3 py-2">PO Number</th><th class="px-3 py-2">Supplier</th><th class="px-3 py-2">Gudang</th>
                <th class="px-3 py-2">Items</th><th class="px-3 py-2 text-right">Total</th><th class="px-3 py-2">Status</th>
                <th class="px-3 py-2">Tanggal</th><th class="px-3 py-2"></th>
            </tr></thead>
            <tbody>
                @forelse($pos as $po)
                @php $rc = ['draft'=>'gray','submitted'=>'blue','partial'=>'amber','received'=>'green','cancelled'=>'red']; @endphp
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-3 py-2 font-mono text-xs font-medium">{{ $po->po_number }}</td>
                    <td class="px-3 py-2 text-xs">{{ $po->supplier->name ?? '-' }}</td>
                    <td class="px-3 py-2 text-xs">{{ $po->warehouse->name ?? '-' }}</td>
                    <td class="px-3 py-2 text-xs">{{ $po->items->sum('quantity') }} / {{ $po->items->sum('received_qty') }}</td>
                    <td class="px-3 py-2 text-right text-xs font-semibold">Rp{{ number_format($po->total, 0, ',', '.') }}</td>
                    <td class="px-3 py-2"><span class="bg-{{ $rc[$po->status] ?? 'gray' }}-100 text-{{ $rc[$po->status] ?? 'gray' }}-700 px-2 py-0.5 rounded-full text-xs font-medium">{{ ucfirst($po->status) }}</span></td>
                    <td class="px-3 py-2 text-xs text-gray-400">{{ $po->created_at->format('d/m/Y') }}</td>
                    <td class="px-3 py-2"><a href="{{ route('inventory.purchase-orders.show', $po) }}" class="text-indigo-600 text-xs hover:underline">Detail</a></td>
                </tr>
                @empty <tr><td colspan="8" class="text-center py-8 text-gray-400">Belum ada PO</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $pos->links() }}</div>
</div>
@endsection
