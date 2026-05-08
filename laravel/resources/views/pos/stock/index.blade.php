@extends('layouts.app')

@section('title', 'Manajemen Stok')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Manajemen Stok</h1>
        <a href="{{ route('stock.create') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
            + Tambah Mutasi Stok
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left text-sm font-semibold text-gray-600">
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Produk</th>
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
                            <a href="{{ route('stock.show', $m->product) }}" class="text-blue-600 hover:underline font-medium">{{ $m->product->name }}</a>
                        </td>
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
                        <td class="px-4 py-3 text-gray-500 max-w-[200px] truncate">{{ $m->note ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $m->user->name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-10 text-center text-gray-400">Belum ada mutasi stok</td>
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
