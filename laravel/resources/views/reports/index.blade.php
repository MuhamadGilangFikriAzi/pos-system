@extends('layouts.app')
@section('title', 'Laporan')
@section('content')
<h2 class="text-2xl font-bold mb-6">Laporan Penjualan</h2>
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <form method="GET" class="flex gap-4 items-end">
        <div><label class="block text-sm text-gray-600 mb-1">Dari</label><input type="date" name="start" value="{{ \ }}" class="border rounded px-3 py-2"></div>
        <div><label class="block text-sm text-gray-600 mb-1">Sampai</label><input type="date" name="end" value="{{ \ }}" class="border rounded px-3 py-2"></div>
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">Tampilkan</button>
    </form>
</div>
<div class="grid grid-cols-2 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-6"><p class="text-gray-500 text-sm">Total Transaksi</p><p class="text-3xl font-bold">{{ \ }}</p></div>
    <div class="bg-white rounded-lg shadow p-6"><p class="text-gray-500 text-sm">Total Revenue</p><p class="text-3xl font-bold text-green-600">Rp {{ number_format(\, 0, ',', '.') }}</p></div>
</div>
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full"><thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">Invoice</th><th class="px-4 py-3 text-left">Tanggal</th><th class="px-4 py-3 text-right">Total</th><th class="px-4 py-3 text-center">Metode</th><th class="px-4 py-3">Kasir</th></tr></thead>
        <tbody>
            @forelse(\ as \)
            <tr class="border-t"><td class="px-4 py-3 font-mono text-sm">{{ \->invoice_number }}</td><td class="px-4 py-3">{{ \->created_at->format('d/m/Y H:i') }}</td><td class="px-4 py-3 text-right">Rp {{ number_format(\->total, 0, ',', '.') }}</td><td class="px-4 py-3 text-center">{{ ucfirst(\->payment_method) }}</td><td class="px-4 py-3">{{ \->user->name ?? 'Admin' }}</td></tr>
            @empty
            <tr><td colspan="5" class="text-center py-8 text-gray-400">Belum ada transaksi</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
