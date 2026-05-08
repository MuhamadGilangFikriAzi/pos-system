@extends("layouts.app")
@section("title", "PO #" . $po->po_number)
@section("content")
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex justify-between items-start mb-4">
            <div><h3 class="font-bold text-gray-800 text-lg">PO #{{ $po->po_number }}</h3>
                <p class="text-sm text-gray-400 mt-1">Supplier: <strong>{{ $po->supplier->name ?? '-' }}</strong> | Gudang: {{ $po->warehouse->name ?? '-' }}</p>
            </div>
            <div>
                @php $rc = ['draft'=>'gray','submitted'=>'blue','partial'=>'amber','received'=>'green','cancelled'=>'red']; @endphp
                <span class="bg-{{ $rc[$po->status] ?? 'gray' }}-100 text-{{ $rc[$po->status] ?? 'gray' }}-700 px-3 py-1 rounded-full text-sm font-medium">{{ ucfirst($po->status) }}</span>
            </div>
        </div>

        <div class="overflow-x-auto mb-4">
            <table class="w-full text-sm"><thead><tr class="bg-gray-50"><th class="px-3 py-2">Produk</th><th class="px-3 py-2">Varian</th>
                <th class="px-3 py-2 text-right">Qty</th><th class="px-3 py-2 text-right">Diterima</th><th class="px-3 py-2 text-right">Harga</th>
                <th class="px-3 py-2 text-right">Subtotal</th></tr></thead>
                <tbody>@foreach($po->items as $item)
                <tr class="border-b">
                    <td class="px-3 py-2">{{ $item->product->name ?? '-' }}</td>
                    <td class="px-3 py-2 text-xs text-gray-400">{{ $item->variant->name ?? '-' }}</td>
                    <td class="px-3 py-2 text-right">{{ $item->quantity }}</td>
                    <td class="px-3 py-2 text-right">{{ $item->received_qty }}</td>
                    <td class="px-3 py-2 text-right">Rp{{ number_format($item->price, 0, ',', '.') }}</td>
                    <td class="px-3 py-2 text-right font-semibold">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach</tbody>
                <tfoot><tr class="font-bold bg-gray-50"><td colspan="5" class="px-3 py-2 text-right">Subtotal</td><td class="px-3 py-2 text-right">Rp{{ number_format($po->subtotal, 0, ',', '.') }}</td></tr>
                <tr class="bg-gray-50"><td colspan="5" class="px-3 py-2 text-right">Pajak</td><td class="px-3 py-2 text-right">{{ $po->tax }}%</td></tr>
                <tr class="font-bold bg-gray-50 text-green-700"><td colspan="5" class="px-3 py-2 text-right">Total</td><td class="px-3 py-2 text-right">Rp{{ number_format($po->total, 0, ',', '.') }}</td></tr>
            </tfoot></table>
        </div>

        @if($po->notes)<div class="bg-gray-50 p-3 rounded-lg mb-4"><p class="text-xs text-gray-400">Catatan</p><p class="text-sm">{{ $po->notes }}</p></div>@endif

        <div class="flex gap-2">
            @if(in_array($po->status, ['submitted', 'partial']))
            <a href="{{ route('inventory.purchase-orders.receive', $po) }}" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700 transition">📦 Terima Barang</a>
            <form method="POST" action="{{ route('inventory.purchase-orders.cancel', $po) }}" onsubmit="return confirm('Batalkan PO ini?')">@csrf<button class="bg-red-100 text-red-600 px-4 py-2 rounded-lg text-sm hover:bg-red-200 transition">Batal</button></form>
            @endif
            <a href="{{ route('inventory.purchase-orders.index') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Kembali</a>
        </div>
        <p class="text-xs text-gray-400 mt-4">Dibuat oleh: {{ $po->user->name ?? '-' }} | {{ $po->created_at->format('d/m/Y H:i') }} @if($po->received_at) | Diterima: {{ $po->received_at->format('d/m/Y H:i') }} @endif</p>
    </div>
</div>
@endsection
