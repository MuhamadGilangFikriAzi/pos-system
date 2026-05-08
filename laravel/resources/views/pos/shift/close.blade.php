@extends("layouts.app")
@section("title", "Tutup Shift")
@section("content")
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="text-center mb-6">
            <div class="text-5xl mb-3">🔒</div>
            <h3 class="text-xl font-bold text-gray-800">Tutup Shift #{{ $activeShift->id }}</h3>
            <p class="text-sm text-gray-400 mt-1">{{ $activeShift->opened_at->format('d/m/Y H:i') }}</p>
        </div>

        <!-- Ringkasan Shift -->
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-gray-50 rounded-xl p-4 text-center">
                <p class="text-xs text-gray-400 uppercase">Saldo Awal</p>
                <p class="text-xl font-bold text-gray-700 mt-1">Rp{{ number_format($activeShift->cash_initial, 0, ',', '.') }}</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 text-center">
                <p class="text-xs text-gray-400 uppercase">Total Penjualan</p>
                <p class="text-xl font-bold text-green-600 mt-1">Rp{{ number_format($activeShift->total_sales, 0, ',', '.') }}</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 text-center">
                <p class="text-xs text-gray-400 uppercase">Transaksi</p>
                <p class="text-xl font-bold text-indigo-600 mt-1">{{ $activeShift->transaction_count }}</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 text-center">
                <p class="text-xs text-gray-400 uppercase">Uang Seharusnya</p>
                <p class="text-xl font-bold text-amber-600 mt-1">Rp{{ number_format($activeShift->cash_expected, 0, ',', '.') }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('pos.shift.close.store') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="shift_id" value="{{ $activeShift->id }}">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Uang Fisik di Laci Kas</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-bold text-lg">Rp</span>
                    <input type="number" name="cash_actual" id="cash_actual" required min="0" step="500"
                           value="{{ old('cash_actual', $activeShift->cash_expected) }}"
                           class="w-full pl-12 pr-4 py-3 text-right text-xl font-bold border-2 border-amber-200 rounded-xl focus:border-amber-500 focus:ring-2 focus:ring-amber-200 outline-none transition"
                           placeholder="0">
                </div>
                @error('cash_actual')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-amber-50 rounded-xl p-4" id="differenceBox" style="display:none;">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium" id="diffLabel">Selisih Kas</span>
                    <span class="text-xl font-bold" id="diffValue">Rp0</span>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan (opsional)</label>
                <textarea name="notes" rows="2" class="w-full border-2 border-gray-200 rounded-xl p-3 text-sm focus:border-amber-500 outline-none transition" placeholder="Kendala atau catatan selama shift...">{{ old('notes') }}</textarea>
            </div>

            <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white font-bold py-3.5 px-6 rounded-xl transition shadow-lg shadow-amber-200">
                🔒 Tutup Shift
            </button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cashActual = document.getElementById('cash_actual');
    const diffBox = document.getElementById('differenceBox');
    const diffValue = document.getElementById('diffValue');
    const diffLabel = document.getElementById('diffLabel');
    const expected = {{ $activeShift->cash_expected }};

    function hitungSelisih() {
        const actual = parseFloat(cashActual.value) || 0;
        const diff = actual - expected;
        diffBox.style.display = 'block';
        diffValue.textContent = 'Rp' + new Intl.NumberFormat('id-ID').format(Math.abs(diff));
        diffValue.style.color = diff >= 0 ? '#16a34a' : '#dc2626';
        diffLabel.textContent = diff >= 0 ? 'Kelebihan Kas' : 'Kekurangan Kas';
    }

    cashActual.addEventListener('input', hitungSelisih);
    if (cashActual.value) hitungSelisih();
});
</script>
@endsection
