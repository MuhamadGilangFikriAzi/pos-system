<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>POS Kasir</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100" style="margin:0;padding:0;">
    <!-- HEADER -->
    <header class="bg-white shadow-sm px-4 py-3 flex items-center justify-between" style="height:56px;">
        <div class="flex items-center gap-3">
            <a href="/dashboard" class="text-gray-500 hover:text-gray-700 text-xl">⬅</a>
            <h1 class="text-lg font-bold">🧾 POS Kasir</h1>
        </div>
        <span class="text-sm text-gray-600">{{ Auth::user()->name ?? 'Admin' }}</span>
    </header>

    <!-- MAIN: flex row kiri-kanan -->
    <div x-data="posApp()" style="display:flex;height:calc(100vh - 56px);gap:8px;padding:8px;">

        <!-- KIRI: Produk -->
        <div style="flex:1;display:flex;flex-direction:column;min-width:0;overflow:hidden;">
            <div style="flex-shrink:0;margin-bottom:8px;">
                <input type="text" x-model="search" @input="filterProduk()" 
                       placeholder="🔍 Cari produk..." 
                       style="width:100%;padding:10px 14px;border:1px solid #ddd;border-radius:8px;font-size:14px;background:white;">
            </div>
            <div id="grid-produk" style="flex:1;overflow-y:auto;display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:8px;align-content:start;">
                @foreach($products as $p)
                <div x-show="!search || '{{ $p->name }}'.toLowerCase().includes(search.toLowerCase()) || '{{ $p->code }}'.toLowerCase().includes(search.toLowerCase())"
                     @click="tambah($event, {{ $p->id }}, '{{ $p->name }}', {{ $p->selling_price }}, {{ $p->stock }})"
                     style="background:white;border-radius:10px;padding:12px;cursor:pointer;border:2px solid transparent;box-shadow:0 1px 3px rgba(0,0,0,0.08);transition:0.15s;"
                     :style="adaDiCart({{ $p->id }}) ? 'background:#eef2ff;border-color:#6366f1;' : ''">
                    <div style="font-weight:600;font-size:13px;color:#333;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $p->name }}</div>
                    <div style="font-size:10px;color:#999;">{{ $p->code }}</div>
                    <div style="color:#6366f1;font-weight:700;font-size:15px;margin-top:6px;">Rp{{ number_format($p->selling_price,0,',','.') }}</div>
                    <div style="font-size:11px;{{ $p->stock <= 5 ? 'color:#ef4444;font-weight:700;' : 'color:#999;' }}">Stok: {{ $p->stock }} {{ $p->unit }}</div>
                </div>
                @endforeach
                <div x-show="produkKosong" style="grid-column:1/-1;text-align:center;padding:40px 0;color:#999;">Produk tidak ditemukan</div>
            </div>
        </div>

        <!-- KANAN: Keranjang -->
        <div style="width:360px;flex-shrink:0;background:white;border-radius:10px;padding:16px;display:flex;flex-direction:column;box-shadow:0 1px 3px rgba(0,0,0,0.08);">
            <div style="font-weight:600;font-size:16px;margin-bottom:12px;display:flex;align-items:center;gap:8px;">
                🛒 Keranjang
                <span x-show="cart.length>0" style="font-size:12px;font-weight:400;color:#999;" x-text="'('+cart.length+' item)'"></span>
            </div>

            <!-- List item keranjang -->
            <div style="flex:1;overflow-y:auto;min-height:100px;">
                <template x-for="(item, i) in cart" :key="i">
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f0f0f0;">
                        <div style="flex:1;min-width:0;margin-right:8px;">
                            <div style="font-size:13px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" x-text="item.n"></div>
                            <div style="display:flex;align-items:center;gap:6px;margin-top:4px;">
                                <button @click="item.q=Math.max(1,item.q-1);hitung()" 
                                        style="width:26px;height:26px;background:#f3f4f6;border-radius:50%;font-size:14px;font-weight:bold;border:none;cursor:pointer;">−</button>
                                <span style="font-size:14px;font-weight:700;min-width:20px;text-align:center;" x-text="item.q"></span>
                                <button @click="if(item.q<item.stk)item.q++;hitung()" 
                                        style="width:26px;height:26px;background:#f3f4f6;border-radius:50%;font-size:14px;font-weight:bold;border:none;cursor:pointer;">+</button>
                                <span style="font-size:11px;color:#999;" x-text="'× Rp'+format(item.h)"></span>
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size:13px;font-weight:600;">Rp<span x-text="format(item.q*item.h)"></span></div>
                            <button @click="cart.splice(i,1);hitung()" style="font-size:10px;color:#f87171;border:none;background:none;cursor:pointer;">hapus</button>
                        </div>
                    </div>
                </template>
                <div x-show="cart.length===0" style="text-align:center;padding:40px 0;color:#bbb;">
                    Keranjang kosong<br>
                    <span style="font-size:12px;">Klik produk di samping</span>
                </div>
            </div>

            <!-- Total & Bayar -->
            <div style="border-top:1px solid #e5e7eb;padding-top:12px;margin-top:8px;">
                <div style="display:flex;justify-content:space-between;font-size:18px;font-weight:700;">
                    <span>Total</span>
                    <span style="color:#6366f1;">Rp<span x-text="format(total)"></span></span>
                </div>
                <div style="margin-top:8px;">
                    <label style="font-size:11px;color:#888;display:block;margin-bottom:4px;">Nominal bayar</label>
                    <input type="number" x-model="bayar" @input="kembali=Math.max(0,(parseFloat(bayar)||0)-total)"
                           style="width:100%;border:1px solid #ddd;border-radius:8px;padding:10px 12px;text-align:right;font-size:16px;font-weight:700;" placeholder="0">
                </div>
                <div style="display:flex;justify-content:space-between;font-weight:700;margin-top:6px;">
                    <span style="color:#16a34a;">Kembali</span>
                    <span style="color:#16a34a;">Rp<span x-text="format(kembali)"></span></span>
                </div>
                <button @click="checkout()" :disabled="!bisaBayar"
                        style="width:100%;padding:14px;border-radius:10px;font-size:16px;font-weight:700;border:none;margin-top:10px;cursor:pointer;transition:0.2s;color:white;"
                        :style="bisaBayar ? 'background:#16a34a;' : 'background:#d1d5db;'">
                    💳 Bayar Sekarang
                </button>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener("alpine:init", () => {
        Alpine.data("posApp", () => ({
            cart: [],
            search: "",
            bayar: 0,
            kembali: 0,
            total: 0,
            get bisaBayar() { return this.cart.length > 0 && parseFloat(this.bayar||0) >= this.total; },
            get produkKosong() { 
                if (!this.search) return false;
                return document.querySelectorAll("#grid-produk > div[style*='display']:not([style*='none'])").length === 0;
            },
            filterProduk() {
                // Alpine x-show handles visibility
            },
            adaDiCart(id) {
                return this.cart.some(x => x.id === id);
            },
            tambah(e, id, nama, harga, stok) {
                const i = this.cart.findIndex(x => x.id === id);
                if (i >= 0) {
                    if (this.cart[i].q < stok) this.cart[i].q++;
                } else {
                    this.cart.push({ id, n: nama, h: harga, stk: stok, q: 1 });
                }
                this.hitung();
            },
            hitung() {
                this.total = this.cart.reduce((s, x) => s + x.h * x.q, 0);
                this.kembali = Math.max(0, (parseFloat(this.bayar) || 0) - this.total);
            },
            format(v) { return new Intl.NumberFormat("id-ID").format(v); },
            async checkout() {
                if (!this.bisaBayar) return;
                try {
                    const resp = await fetch("/pos/checkout", {
                        method: "POST",
                        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": document.querySelector("meta[name=csrf-token]")?.content || "" },
                        body: JSON.stringify({ items: this.cart.map(i => ({ product_id: i.id, quantity: i.q, price: i.h })), payment: this.bayar })
                    });
                    const r = await resp.json();
                    if (r.success) { this.cart = []; this.bayar = 0; this.kembali = 0; this.total = 0; window.location.href = "/pos/receipt/" + r.transaction_id; }
                    else alert("Gagal: " + (r.message || ""));
                } catch(e) { alert("Error: " + e); }
            }
        }));
    });
    </script>
</body>
</html>
