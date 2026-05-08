@extends("layouts.app")
@section("title", "Dashboard Inventory")
@section("content")
<div class="mb-6">
    <h3 class="font-semibold text-gray-800 text-lg">📦 Dashboard Inventory</h3>
    <p class="text-sm text-gray-400">Overview management stock dan inventory</p>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs text-gray-400 uppercase">Total Produk</p>
        <p class="text-2xl font-bold text-gray-800 mt-1">{{ $summary['total_products'] ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs text-gray-400 uppercase">Nilai Stock</p>
        <p class="text-lg font-bold text-green-600 mt-1">Rp{{ number_format($summary['total_stock_value'] ?? 0, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 @if(($summary['low_stock_count'] ?? 0) > 0) border-2 border-red-300 @endif">
        <p class="text-xs text-gray-400 uppercase">Stok Menipis</p>
        <p class="text-2xl font-bold text-red-600 mt-1">{{ $summary['low_stock_count'] ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs text-gray-400 uppercase">Mutasi Hari Ini</p>
        <p class="text-2xl font-bold text-indigo-600 mt-1">{{ $summary['today_mutations'] ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs text-gray-400 uppercase">Hampir Expired</p>
        <p class="text-2xl font-bold text-orange-600 mt-1">{{ $summary['near_expiry_qty'] ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs text-gray-400 uppercase">Gudang / Supplier</p>
        <p class="text-lg font-bold text-gray-800 mt-1">{{ $summary['warehouse_count'] ?? 0 }} / {{ $summary['supplier_count'] ?? 0 }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Quick Actions -->
    <div class="lg:col-span-1 space-y-3">
        <div class="bg-white rounded-xl shadow-sm p-4">
            <h4 class="font-semibold text-gray-800 mb-3 text-sm">⚡ Aksi Cepat</h4>
            <div class="grid grid-cols-2 gap-2">
                <a href="{{ route('inventory.stock.in') }}" class="bg-green-600 text-white rounded-lg p-3 text-center text-xs hover:bg-green-700 transition">➕ Stock Masuk</a>
                <a href="{{ route('inventory.stock.out') }}" class="bg-orange-600 text-white rounded-lg p-3 text-center text-xs hover:bg-orange-700 transition">➖ Stock Keluar</a>
                <a href="{{ route('inventory.stock.adjust') }}" class="bg-amber-600 text-white rounded-lg p-3 text-center text-xs hover:bg-amber-700 transition">🔄 Penyesuaian</a>
                <a href="{{ route('inventory.stock.transfer') }}" class="bg-blue-600 text-white rounded-lg p-3 text-center text-xs hover:bg-blue-700 transition">📤 Transfer</a>
                <a href="{{ route('inventory.purchase-orders.create') }}" class="bg-purple-600 text-white rounded-lg p-3 text-center text-xs hover:bg-purple-700 transition">📋 PO Baru</a>
                <a href="{{ route('inventory.opname.create') }}" class="bg-cyan-600 text-white rounded-lg p-3 text-center text-xs hover:bg-cyan-700 transition">📐 Stock Opname</a>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <h4 class="font-semibold text-gray-800 mb-3 text-sm">🏭 Gudang</h4>
            <a href="{{ route('inventory.warehouses.index') }}" class="text-indigo-600 text-sm hover:underline">Kelola Gudang →</a>
            <a href="{{ route('inventory.suppliers.index') }}" class="text-indigo-600 text-sm hover:underline block mt-2">Kelola Supplier →</a>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <h4 class="font-semibold text-gray-800 mb-3 text-sm">📊 Report</h4>
            <a href="{{ route('inventory.stock.export') }}" class="text-indigo-600 text-sm hover:underline block">📥 Export CSV Mutasi</a>
            <a href="{{ route('inventory.stock.index') }}" class="text-indigo-600 text-sm hover:underline block mt-1">📋 Riwayat Mutasi</a>
        </div>
    </div>

    <!-- Low Stock & Near Expiry -->
    <div class="lg:col-span-2 space-y-4">
        @if(count($lowStockAlerts) > 0)
        <div class="bg-red-50 border border-red-200 rounded-xl p-4">
            <h4 class="font-semibold text-red-800 text-sm mb-2">⚠️ Stok Menipis ({{ count($lowStockAlerts) }})</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                @foreach(array_slice($lowStockAlerts, 0, 6) as $alert)
                <div class="flex justify-between items-center bg-white rounded-lg px-3 py-2">
                    <span class="text-sm">{{ $alert['product']['name'] ?? 'Produk #'.$alert['product_id'] }}</span>
                    <span class="text-sm font-bold text-red-600">{{ (int)$alert['stock'] }} / {{ $alert['min_stock'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if(count($nearExpiry) > 0)
        <div class="bg-orange-50 border border-orange-200 rounded-xl p-4">
            <h4 class="font-semibold text-orange-800 text-sm mb-2">📅 Mendekati Expired ({{ count($nearExpiry) }})</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                @foreach(array_slice($nearExpiry, 0, 6) as $exp)
                <div class="flex justify-between bg-white rounded-lg px-3 py-2">
                    <span class="text-sm">{{ $exp['product']['name'] ?? '-' }} <span class="text-xs text-gray-400">{{ $exp['batch_number'] ?? '' }}</span></span>
                    <span class="text-xs font-bold text-orange-600">{{ $exp['expiry_date'] }} ({{ $exp['quantity'] }})</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Mutasi Hari Ini -->
        <div class="bg-white rounded-xl shadow-sm p-4">
            <h4 class="font-semibold text-gray-800 text-sm mb-3">📝 Mutasi Hari Ini</h4>
            @if($todayMutations->count() > 0)
            <div class="max-h-60 overflow-y-auto space-y-1">
                @foreach($todayMutations as $m)
                <div class="flex items-center gap-2 text-xs p-2 border-b border-gray-50">
                    <span class="w-6 text-center">
                        @switch($m->type)
                            @case('in') 🟢 @break
                            @case('out') 🔴 @break
                            @case('adjustment') 🔵 @break
                            @case('transfer_in') 📥 @break
                            @case('transfer_out') 📤 @break
                            @default 📝
                        @endswitch
                    </span>
                    <span class="font-medium">{{ $m->product->name ?? '-' }}</span>
                    <span class="text-gray-400 ml-auto">{{ $m->warehouse->name ?? '-' }}</span>
                    <span class="font-bold {{ $m->quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                        @if($m->quantity > 0)+@endif{{ (int)$m->quantity }}
                    </span>
                    <span class="text-gray-400">{{ $m->created_at->format('H:i') }}</span>
                </div>
                @endforeach
            </div>
            <a href="{{ route('inventory.stock.index') }}" class="text-indigo-600 text-xs mt-2 block hover:underline">Lihat semua →</a>
            @else
            <p class="text-center py-6 text-gray-400 text-sm">Belum ada mutasi hari ini</p>
            @endif
        </div>
    </div>
</div>
@endsection
