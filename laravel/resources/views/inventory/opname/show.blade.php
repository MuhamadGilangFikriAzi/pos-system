@extends("layouts.app")
@section("title", "Detail Opname #" . $opname->opname_number)
@section("content")
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex justify-between items-start mb-4">
            <div><h3 class="font-bold text-gray-800 text-lg">📐 Opname #{{ $opname->opname_number }}</h3>
                <p class="text-sm text-gray-400 mt-1">Gudang: <strong>{{ $opname->warehouse->name }}</strong> | Tanggal: {{ $opname->opname_date }}</p>
            </div>
            @php $rc = ['draft'=>'gray','in_progress'=>'blue','completed'=>'green','cancelled'=>'red']; @endphp
            <span class="bg-{{ $rc[$opname->status] ?? 'gray' }}-100 text-{{ $rc[$opname->status] ?? 'gray' }}-700 px-3 py-1 rounded-full text-sm font-medium">{{ ucfirst(str_replace('_',' ',$opname->status)) }}</span>
        </div>

        <form method="POST" action="{{ $opname->status === 'in_progress' ? route('inventory.opname.update', $opname) : '#' }}" x-data>
            @csrf @if($opname->status === 'in_progress') @method('PUT') @endif

            <div class="overflow-x-auto">
                <table class="w-full text-sm"><thead><tr class="bg-gray-50">
                    <th class="px-3 py-2">Produk</th><th class="px-3 py-2 text-right">System</th>
                    <th class="px-3 py-2 text-right">Aktual</th><th class="px-3 py-2 text-right">Selisih</th>
                    @if($opname->status === 'in_progress')<th class="px-3 py-2">Catatan</th>@endif
                </tr></thead><tbody>
                    @foreach($opname->items as $item)
                    <tr class="border-b">
                        <td class="px-3 py-2 text-xs">{{ $item->product->name ?? '-' }} @if($item->variant) <span class="text-gray-400">({{ $item->variant->name }})</span>@endif</td>
                        <td class="px-3 py-2 text-right font-mono">{{ number_format($item->system_stock, 0) }}</td>
                        <td class="px-3 py-2 text-right">
                            @if($opname->status === 'in_progress')
                            <input type="hidden" name="items[{{ $loop->index }}][id]" value="{{ $item->id }}">
                            <input type="number" name="items[{{ $loop->index }}][actual_stock]" value="{{ $item->actual_stock }}" min="0" step="1"
                                   class="w-24 border rounded px-2 py-1 text-right text-sm" @input="$el.closest('tr').querySelector('.diff').textContent = ($el.value - {{ $item->system_stock }})">
                            @else
                            <span class="font-mono">{{ number_format($item->actual_stock, 0) }}</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-right font-mono font-bold {{ $item->difference > 0 ? 'text-green-600' : ($item->difference < 0 ? 'text-red-600' : 'text-gray-400') }}">
                            <span class="diff">{{ $item->difference > 0 ? '+':'' }}{{ number_format($item->difference, 0) }}</span>
                        </td>
                        @if($opname->status === 'in_progress')
                        <td class="px-3 py-2"><input type="text" name="items[{{ $loop->index }}][notes]" value="{{ $item->notes }}" class="w-full border rounded px-2 py-1 text-xs"></td>
                        @endif
                    </tr>
                    @endforeach
                </tbody></table>
            </div>

            @if($opname->notes)
            <div class="mt-4 bg-gray-50 p-3 rounded-lg"><p class="text-xs text-gray-400">Catatan</p><p class="text-sm">{{ $opname->notes }}</p></div>
            @endif

            <div class="flex gap-3 mt-6">
                @if($opname->status === 'in_progress')
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition">💾 Simpan Sementara</button>
                <button type="button" onclick="if(confirm('Finalkan opname? Stock akan disesuaikan.')) document.getElementById('completeForm').submit()" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700 transition">✅ Selesaikan Opname</button>
                <form method="POST" action="{{ route('inventory.opname.cancel', $opname) }}" onsubmit="return confirm('Batalkan opname?')" class="inline">@csrf<button class="bg-red-100 text-red-600 px-4 py-2 rounded-lg text-sm hover:bg-red-200 transition">Batal</button></form>
                @endif
                <a href="{{ route('inventory.opname.index') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Kembali</a>
            </div>
        </form>

        <form method="POST" action="{{ route('inventory.opname.complete', $opname) }}" id="completeForm">@csrf</form>

        <p class="text-xs text-gray-400 mt-4">Dibuat oleh: {{ $opname->user->name ?? '-' }} | {{ $opname->created_at->format('d/m/Y H:i') }}</p>
    </div>
</div>
@endsection
