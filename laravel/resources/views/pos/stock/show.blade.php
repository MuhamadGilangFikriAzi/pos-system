@extends('layouts.app')

@section('title', 'Riwayat Stok: ' . $product->name)

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('stock.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Riwayat Stok</a>
        <h1 class="text-2xl font-bold text-gray-800 mt-2">{{ $product->name }}</h1>
        <p class="text-sm text-gray-500">
            Kode: {{ $product->code }} | 
            Kategori: {{ $product->category->name ?? '-' }} | 
            Stok Saat Ini: <span class="font-bold {{ $product->stock <= $product->min_stock ? 'text-red-600' : 'text-green-600' }}">{{ $product->stock }} {{ $product->unit }}</span> | 
            Min. Stok: {{ $product->min_stock }}
        </p>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left text-sm font-semibold text-gray-600">
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Tipe</th>
                        <th class="px-4 py-3">Jumlah</th>
                        <th class="px-4 py-3">Stok Awal</th>
                        <th class="px-4 py-3">Stok Akhir</th>
                        <th class="px-4 py-3">Referensi</th>
                        <th class="px-4 py-3">Keterangan</th>
                        <th class="px-4 py-3">Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($mutations as $m)
                    <tr class="hover:bg-gray-50 text-sm">
                        <td class="px-4 py-3 text-gray-500">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">
                            @if($m->type === 'in')
                                <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs font-medium">Masuk</span>
                            @else
                                <span class="bg-red-100 text-red-700 px-2 py-0.5 rounded-full text-xs font-medium">Keluar</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-medium">{{ $m->quantity }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $m->stock_before }}</td>
                        <td class="px-4 py-3 font-medium">{{ $m->stock_after }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $m->reference ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-500 max-w-[300px]">{{ $m->note ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $m->user->name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-gray-400">Belum ada mutasi stok</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $mutations->links() }}
    </div>
</div>
@endsection
