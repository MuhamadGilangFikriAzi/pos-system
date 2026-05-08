@extends("layouts.app")
@section("title", "Terima Barang - PO #" . $po->po_number)
@section("content")
<div class="max-w-4xl mx-auto" x-data="receiveForm()">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="mb-4"><h3 class="font-bold text-gray-800 text-lg">📦 Terima Barang — PO #{{ $po->po_number }}</h3>
            <p class="text-sm text-gray-400">Supplier: {{ $po->supplier->name ?? '-' }} | Gudang: {{ $po->warehouse->name ?? '-' }}</p>
        </div>

        <form method="POST" action="{{ route('inventory.purchase-orders.receive-store', $po) }}">
            @csrf
            <div class="overflow-x-auto">
                <table class="w-full text-sm"><thead><tr class="bg-gray-50">
                    <th class="px-3 py-2">Produk</th><th class="px-3 py-2 text-right">Dipesan</th>
                    <th class="px-3 py-2 text-right">Sudah Diterima</th><th class="px-3 py-2 text-right w-28">Diterima Sekarang</th>
                </tr></thead><tbody>
                    @foreach($po->items as $item)
                    @php $remaining = $item->quantity - $item->received_qty; @endphp
                    <tr class="border-b">
                        <td class="px-3 py-2">{{ $item->product->name ?? '-' }} @if($item->variant) <span class="text-xs text-gray-400">({{ $item->variant->name }})</span>@endif</td>
                        <td class="px-3 py-2 text-right">{{ $item->quantity }}</td>
                        <td class="px-3 py-2 text-right">{{ $item->received_qty }}</td>
                        <td class="px-3 py-2 text-right"><input type="number" name="items[{{ $loop->index }}][id]" value="{{ $item->id }}" hidden>
                            <input type="number" name="items[{{ $loop->index }}][receive_qty]" value="{{ $remaining }}" min="0" max="{{ $remaining }}" class="w-24 border rounded px-3 py-2 text-right text-sm"></td>
                    </tr>
                    @endforeach
                </tbody></table>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition">✅ Konfirmasi Penerimaan</button>
                <a href="{{ route('inventory.purchase-orders.show', $po) }}" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm">Kembali</a>
            </div>
        </form>
    </div>
</div>
@endsection
