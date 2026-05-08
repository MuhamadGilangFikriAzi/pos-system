@extends("layouts.app")
@section("title", "Buat Purchase Order")
@section("content")
<div class="max-w-4xl mx-auto" x-data="poForm()">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-800 text-lg mb-6">📋 Purchase Order Baru</h3>
        <form method="POST" action="{{ route('inventory.purchase-orders.store') }}">
            @csrf
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                    <select name="supplier_id" required class="w-full border-2 border-gray-200 rounded-lg px-4 py-3 focus:border-indigo-500 outline-none text-sm">
                        <option value="">— Pilih Supplier —</option>
                        @foreach($suppliers as $s)<option value="{{ $s->id }}">{{ $s->name }} ({{ $s->code }})</option>@endforeach
                    </select></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Gudang Tujuan</label>
                    <select name="warehouse_id" required class="w-full border-2 border-gray-200 rounded-lg px-4 py-3 focus:border-indigo-500 outline-none text-sm">
                        <option value="">— Pilih Gudang —</option>
                        @foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach
                    </select></div>
            </div>

            <div class="mb-4"><label class="block text-sm font-medium text-gray-700 mb-1">Pajak (%)</label>
                <input type="number" name="tax" value="0" min="0" step="0.01" class="w-32 border-2 border-gray-200 rounded-lg px-4 py-3 focus:border-indigo-500 outline-none text-sm"></div>

            <div class="mb-4">
                <div class="flex justify-between items-center mb-2">
                    <label class="text-sm font-medium text-gray-700">Item Barang</label>
                    <button type="button" @click="addRow()" class="text-indigo-600 text-sm font-medium">+ Tambah Barang</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm"><thead><tr class="bg-gray-50">
                        <th class="px-2 py-2 text-left">Produk</th><th class="px-2 py-2 w-20">Varian</th>
                        <th class="px-2 py-2 w-20 text-right">Qty</th><th class="px-2 py-2 w-28 text-right">Harga</th>
                        <th class="px-2 py-2 w-24 text-right">Subtotal</th><th class="px-2 py-2 w-10"></th>
                    </tr></thead><tbody>
                        <template x-for="(item, idx) in items" :key="idx">
                            <tr>
                                <td class="px-2 py-1">
                                    <select :name="'items['+idx+'][product_id]'" x-model="item.product_id" @change="fetchProduct(item, idx)" required class="w-full border rounded px-2 py-2 text-xs">
                                        <option value="">— Pilih —</option>
                                        @foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                                    </select>
                                </td>
                                <td class="px-2 py-1">
                                    <select :name="'items['+idx+'][variant_id]'" x-model="item.variant_id" class="w-full border rounded px-2 py-2 text-xs">
                                        <option value="">—</option>
                                        <template x-for="v in item.variants" :key="v.id">
                                            <option :value="v.id" x-text="v.name"></option>
                                        </template>
                                    </select>
                                </td>
                                <td class="px-2 py-1"><input type="number" :name="'items['+idx+'][quantity]'" x-model="item.quantity" min="1" required class="w-full border rounded px-2 py-2 text-xs text-right" @input="calcRow(item)"></td>
                                <td class="px-2 py-1"><input type="number" :name="'items['+idx+'][price]'" x-model="item.price" min="0" step="100" class="w-full border rounded px-2 py-2 text-xs text-right" @input="calcRow(item)"></td>
                                <td class="px-2 py-1 text-right font-semibold" x-text="'Rp' + format(item.subtotal)"></td>
                                <td class="px-2 py-1"><button type="button" @click="items.splice(idx, 1)" class="text-red-500 text-xs" x-show="items.length > 1">✕</button></td>
                            </tr>
                        </template>
                    </tbody>
                    <tfoot><tr class="font-bold bg-gray-50">
                        <td colspan="4" class="px-2 py-2 text-right">Total</td>
                        <td class="px-2 py-2 text-right text-green-600" x-text="'Rp' + format(total())"></td>
                        <td></td>
                    </tr></tfoot>
                    </table>
                </div>
            </div>

            <div><label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                <textarea name="notes" rows="2" class="w-full border-2 border-gray-200 rounded-lg px-4 py-3 focus:border-indigo-500 outline-none text-sm"></textarea></div>

            <div class="flex gap-3 mt-6">
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-6 rounded-lg transition">Buat PO</button>
                <a href="{{ route('inventory.purchase-orders.index') }}" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm">Batal</a>
            </div>
        </form>
    </div>
</div>
<script>
function poForm() {
    return {
        items: [{ product_id: '', variant_id: '', quantity: 1, price: 0, subtotal: 0, variants: [] }],
        products: @json($products->map(fn($p)=>['id'=>$p->id,'name'=>$p->name,'purchase_price'=>$p->purchase_price])),
        addRow() { this.items.push({ product_id: '', variant_id: '', quantity: 1, price: 0, subtotal: 0, variants: [] }); },
        fetchProduct(item, idx) {
            if (!item.product_id) { item.variants = []; item.price = 0; return; }
            const p = this.products.find(x => x.id == item.product_id);
            if (p) item.price = parseFloat(p.purchase_price) || 0;
            item.variant_id = '';
            // AJAX untuk variants
            fetch('/inventory/purchase-orders/product/' + item.product_id)
                .then(r => r.json())
                .then(d => { item.variants = d.variants || []; })
                .catch(() => {});
            this.calcRow(item);
        },
        calcRow(item) { item.subtotal = (parseInt(item.quantity)||0) * (parseFloat(item.price)||0); },
        total() { return this.items.reduce((s,i) => s + (i.subtotal||0), 0); },
        format(n) { return new Intl.NumberFormat('id-ID').format(n||0); },
    }
}
</script>
@endsection
