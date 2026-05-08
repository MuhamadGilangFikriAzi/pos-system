@extends("layouts.app")
@section("title", "Buka Shift")
@section("content")
<div class="max-w-lg mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="text-center mb-6">
            <div class="text-5xl mb-3">🕐</div>
            <h3 class="text-xl font-bold text-gray-800">Buka Shift</h3>
            <p class="text-sm text-gray-400 mt-1">Masukkan saldo awal kas sebelum memulai transaksi</p>
        </div>

        <form method="POST" action="{{ route('pos.shift.open.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Saldo Awal Kas</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-bold text-lg">Rp</span>
                    <input type="number" name="cash_initial" id="cash_initial" required min="0" step="500" value="{{ old('cash_initial', 0) }}"
                           class="w-full pl-12 pr-4 py-3 text-right text-xl font-bold border-2 border-indigo-200 rounded-xl focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition"
                           placeholder="0">
                </div>
                @error('cash_initial')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-400 mt-2">Masukkan jumlah uang yang ada di laci kas awal shift</p>
            </div>

            <div class="bg-blue-50 rounded-xl p-4">
                <p class="text-sm text-blue-700">
                    <strong>💡 Info:</strong> Setelah buka shift, Anda bisa langsung bertransaksi.
                    Jangan lupa tutup shift di akhir sesi.
                </p>
            </div>

            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 px-6 rounded-xl transition shadow-lg shadow-indigo-200">
                🚀 Buka Shift Sekarang
            </button>
        </form>
    </div>

    <!-- Riwayat shift terakhir -->
    @php
        $lastShift = \App\Models\Shift::where('user_id', auth()->id())->latest()->first();
    @endphp
    @if($lastShift)
    <div class="bg-white rounded-xl shadow-sm p-4 mt-4">
        <p class="text-xs text-gray-400 uppercase tracking-wide">Shift Terakhir</p>
        <div class="flex justify-between mt-1 text-sm">
            <span>{{ $lastShift->status === 'open' ? '🟢 Sedang aktif' : '🔴 Ditutup' }}</span>
            <span class="text-gray-500">{{ $lastShift->created_at->diffForHumans() }}</span>
        </div>
        @if($lastShift->status === 'closed')
        <div class="flex justify-between text-xs text-gray-400 mt-1">
            <span>Penjualan: Rp{{ number_format($lastShift->total_sales, 0, ',', '.') }}</span>
            <span>Selisih: Rp{{ number_format($lastShift->cash_difference, 0, ',', '.') }}</span>
        </div>
        @endif
    </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('cash_initial');
    input.addEventListener('focus', function() { this.select(); });
});
</script>
@endsection
