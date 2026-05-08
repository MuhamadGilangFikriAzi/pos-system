@extends("layouts.app")
@section("title", "Buat Stock Opname")
@section("content")
<div class="max-w-lg mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-800 text-lg mb-6">📐 Stock Opname Baru</h3>
        <p class="text-xs text-gray-400 mb-4">Opname akan otomatis mengambil data stock terkini dari gudang yang dipilih.</p>
        <form method="POST" action="{{ route('inventory.opname.store') }}" class="space-y-4">
            @csrf
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Gudang</label>
                <select name="warehouse_id" required class="w-full border-2 border-gray-200 rounded-lg px-4 py-3 focus:border-indigo-500 outline-none text-sm">
                    @foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach
                </select></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Opname</label>
                <input type="date" name="opname_date" value="{{ date('Y-m-d') }}" required class="w-full border-2 border-gray-200 rounded-lg px-4 py-3 focus:border-indigo-500 outline-none text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                <textarea name="notes" rows="2" class="w-full border-2 border-gray-200 rounded-lg px-4 py-3 focus:border-indigo-500 outline-none text-sm"></textarea></div>
            <button type="submit" class="w-full bg-cyan-600 hover:bg-cyan-700 text-white font-semibold py-3 px-6 rounded-lg transition">✅ Buat & Mulai Opname</button>
            <a href="{{ route('inventory.opname.index') }}" class="block text-center text-sm text-gray-500 hover:text-gray-700 mt-2">Kembali</a>
        </form>
    </div>
</div>
@endsection
