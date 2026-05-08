<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>POS Kasir</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        *{box-sizing:border-box;-webkit-tap-highlight-color:transparent}
        .cart-item-enter{animation:fadeInUp 0.2s ease-out}
        @keyframes fadeInUp{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
        input[type=number]::-webkit-inner-spin-button,input[type=number]::-webkit-outer-spin-button{-webkit-appearance:none;margin:0}
        input[type=number]{-moz-appearance:textfield}
        .badge-discount{background:#fef3c7;color:#92400e;font-size:10px;padding:1px 6px;border-radius:4px;white-space:nowrap}
        .scroll-soft{scrollbar-width:thin;scrollbar-color:#e5e7eb transparent}
        .scroll-soft::-webkit-scrollbar{width:4px}
        .scroll-soft::-webkit-scrollbar-thumb{background:#e5e7eb;border-radius:4px}
    </style>
</head>
<body class="bg-gray-100" style="margin:0;padding:0;">
    <!-- HEADER -->
    <header class="bg-white shadow-sm px-4 py-3 flex items-center justify-between" style="height:56px;box-shadow:0 1px 3px rgba(0,0,0,0.06);">
        <div class="flex items-center gap-3">
            <a href="/dashboard" class="text-gray-500 hover:text-gray-700 text-xl">&#x2B05;</a>
            <h1 class="text-lg font-bold">&#x1F9FE; POS Kasir</h1>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs px-2 py-1 bg-amber-100 text-amber-700 rounded-full font-medium" x-text="'Item: '+cart.length"></span>
            <span class="text-sm text-gray-600">{{ Auth::user()->name ?? 'Admin' }}</span>
        </div>
    </header>

    <!-- MAIN -->
    <div x-data="posApp()" style="display:flex;height:calc(100vh - 56px);gap:8px;padding:8px;">

        <!-- KIRI: Produk Grid -->
        <div style="flex:1;display:flex;flex-direction:column;min-width:0;overflow:hidden;">
            <div style="flex-shrink:0;margin-bottom:8px;">
                <input type="text" x-model="search" @input="filterProduk()" 
                       placeholder="&#x1F50D; Cari produk..." 
                       style="width:100%;padding:10px 14px;border:1px solid #ddd;border-radius:8px;font-size:14px;background:white;outline:none;">
            </div>
            <div id="grid-produk" class="scroll-soft" style="flex:1;overflow-y:auto;display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:8px;align-content:start;">
                @foreach($products as $p)
                <div x-show="!search || '{{ $p->name }}'.toLowerCase().includes(search.toLowerCase()) || '{{ $p->code }}'.toLowerCase().includes(search.toLowerCase())"
                     @click="tambah($event, {{ $p->id }}, '{{ addslashes($p->name) }}', {{ $p->selling_price }}, {{ $p->purchase_price }}, {{ $p->stock }})"
                     style="background:white;border-radius:10px;padding:12px;cursor:pointer;border:2px solid transparent;box-shadow:0 1px 3px rgba(0,0,0,0.08);transition:0.15s;"
                     :style="adaDiCart({{ $p->id }}) ? 'background:#eef2ff;border-color:#6366f1;' : ''">
                    <div style="font-weight:600;font-size:13px;color:#333;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $p->name }}</div>
                    <div style="font-size:10px;color:#999;">{{ $p->code }}</div>
                    <div style="color:#6366f1;font-weight:700;font-size:15px;margin-top:6px;">Rp{{ number_format($p->selling_price,0,',','.') }}</div>
                    <div style="font-size:11px;{{ $p->stock <= 5 ? 'color:#ef4444;font-weight:700;' : 'color:#999;' }}">Stok: {{ $p->stock }} {{ $p->unit }}</div>
                    <div x-show="adaDiCart({{ $p->id }})" style="margin-top:4px;display:flex;gap:2px;flex-wrap:wrap;">
                        <span class="badge-discount">&#x2705; di cart</span>
                    </div>
                </div>
                @endforeach
                <div x-show="produkKosong" style="grid-column:1/-1;text-align:center;padding:40px 0;color:#999;">Produk tidak ditemukan</div>
            </div>
        </div>

        <!-- KANAN: Keranjang -->
        <div class="scroll-soft" style="width:400px;flex-shrink:0;background:white;border-radius:12px;padding:16px;display:flex;flex-direction:column;box-shadow:0 2px 8px rgba(0,0,0,0.06);">
            <div style="font-weight:700;font-size:16px;margin-bottom:12px;display:flex;align-items:center;gap:8px;">
                &#x1F6D2; Keranjang
                <span x-show="cart.length>0" style="font-size:12px;font-weight:400;color:#999;" x-text="'('+cart.length+' item)'"></span>
                <button x-show="cart.length>0" @click="cart=[];hitung()" style="margin-left:auto;font-size:11px;color:#f87171;background:none;border:none;cursor:pointer;">&#x1F5D1; Kosongkan</button>
            </div>

            <!-- List cart -->
            <div class="scroll-soft" style="flex:1;overflow-y:auto;min-height:100px;">
                <template x-for="(item, i) in cart" :key="item.id">
                    <div class="cart-item-enter" style="padding:10px 0;border-bottom:1px solid #f3f4f6;">
                        <!-- Nama + diskon item -->
                        <div style="display:flex;justify-content:space-between;align-items:start;">
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:13px;font-weight:600;" x-text="item.n"></div>
                                <div style="display:flex;align-items:center;gap:4px;margin-top:2px;">
                                    <button @click="item.q=Math.max(1,item.q-1);hitung();calcServer()" 
                                            style="width:24px;height:24px;background:#f3f4f6;border-radius:6px;font-size:14px;font-weight:bold;border:none;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;">-</button>
                                    <span style="font-size:14px;font-weight:700;min-width:20px;text-align:center;" x-text="item.q"></span>
                                    <button @click="if(item.q<item.stk)item.q++;hitung();calcServer()" 
                                            style="width:24px;height:24px;background:#f3f4f6;border-radius:6px;font-size:14px;font-weight:bold;border:none;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;">+</button>
                                    <span style="font-size:11px;color:#999;" x-text="'&#xD7; Rp'+format(item.h)"></span>
                                </div>
                            </div>
                            <div style="text-align:right;margin-left:8px;">
                                <div style="font-size:13px;font-weight:600;">Rp<span x-text="format(item.q*item.h)"></span></div>
                                <button @click="cart.splice(i,1);hitung();calcServer()" style="font-size:10px;color:#f87171;border:none;background:none;cursor:pointer;">hapus</button>
                            </div>
                        </div>
                        <!-- Input diskon per item -->
                        <div style="display:flex;gap:6px;margin-top:6px;" x-show="item.q*item.h>0">
                            <div style="flex:1;display:flex;align-items:center;gap:4px;">
                                <span style="font-size:10px;color:#888;white-space:nowrap;">Diskon</span>
                                <input type="number" x-model="item.dp" @input="if(parseFloat(item.dp||0)>100)item.dp=100;toNominal(item);calcServer()"
                                       placeholder="%" style="width:42px;padding:3px 4px;border:1px solid #e5e7eb;border-radius:4px;font-size:11px;text-align:center;">
                                <span style="font-size:10px;color:#888;">%</span>
                            </div>
                            <div style="flex:1;display:flex;align-items:center;gap:4px;">
                                <input type="number" x-model="item.da" @input="toPersen(item);calcServer()"
                                       placeholder="Rp" style="width:60px;padding:3px 4px;border:1px solid #e5e7eb;border-radius:4px;font-size:11px;text-align:center;">
                            </div>
                            <div x-show="item.dp>0||item.da>0">
                                <span class="badge-discount">-<span x-text="item.dp>0?item.dp+'%':format(item.da)"></span></span>
                            </div>
                        </div>
                        <!-- Harga setelah diskon -->
                        <div x-show="item.diskon>0" style="margin-top:4px;font-size:11px;color:#16a34a;">
                            Setelah diskon: Rp<span x-text="format(Math.max(0,item.q*item.h-item.diskon))"></span>
                        </div>
                    </div>
                </template>
                <div x-show="cart.length===0" style="text-align:center;padding:60px 0;color:#bbb;">
                    <div style="font-size:40px;margin-bottom:8px;">&#x1F6D2;</div>
                    Keranjang kosong<br>
                    <span style="font-size:12px;">Klik produk di samping</span>
                </div>
            </div>

            <!-- Diskon Global + Pajak -->
            <div x-show="cart.length>0" style="border-top:1px solid #e5e7eb;padding-top:10px;margin-top:6px;">
                <div style="display:flex;gap:8px;margin-bottom:6px;">
                    <div style="flex:1;">
                        <label style="font-size:10px;color:#888;">Diskon Global</label>
                        <div style="display:flex;gap:4px;">
                            <input type="number" x-model="globalDp" @input="if(parseFloat(globalDp||0)>100)globalDp=100;globalDa='';calcServer()" 
                                   placeholder="%" style="width:50px;padding:5px 6px;border:1px solid #ddd;border-radius:6px;font-size:12px;text-align:center;">
                            <input type="number" x-model="globalDa" @input="globalDp='';calcServer()"
                                   placeholder="Rp" style="flex:1;padding:5px 6px;border:1px solid #ddd;border-radius:6px;font-size:12px;text-align:right;">
                        </div>
                    </div>
                    <div style="width:100px;">
                        <label style="font-size:10px;color:#888;">Pajak (%)</label>
                        <input type="number" x-model="taxP" @input="if(parseFloat(taxP||0)>100)taxP=100;calcServer()" 
                               placeholder="PPN 11" style="width:100%;padding:5px 6px;border:1px solid #ddd;border-radius:6px;font-size:12px;text-align:center;">
                    </div>
                </div>
            </div>

            <!-- Summary -->
            <div style="border-top:1px solid #e5e7eb;padding-top:10px;margin-top:4px;">
                <!-- Total per item (after item discount) -->
                <div style="display:flex;justify-content:space-between;font-size:12px;color:#666;">
                    <span>Subtotal</span>
                    <span>Rp<span x-text="format(subtotalItem)"></span></span>
                </div>
                <div x-show="globalDiskon>0" style="display:flex;justify-content:space-between;font-size:12px;color:#dc2626;">
                    <span>Diskon Global</span>
                    <span>-Rp<span x-text="format(globalDiskon)"></span></span>
                </div>
                <div x-show="pajak>0" style="display:flex;justify-content:space-between;font-size:12px;color:#6366f1;">
                    <span>Pajak <span x-text="taxP>0?'('+taxP+'%)':''"></span></span>
                    <span>+Rp<span x-text="format(pajak)"></span></span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:20px;font-weight:800;margin-top:6px;padding-top:6px;border-top:2px solid #6366f1;">
                    <span>Grand Total</span>
                    <span style="color:#6366f1;">Rp<span x-text="format(grandTotal)"></span></span>
                </div>

                <!-- Bayar -->
                <div style="margin-top:8px;">
                    <label style="font-size:11px;color:#888;display:block;margin-bottom:4px;">Nominal Bayar</label>
                    <input type="number" x-model="bayar" @input="kembali=Math.max(0,(parseFloat(bayar)||0)-grandTotal)"
                           style="width:100%;border:2px solid #e5e7eb;border-radius:8px;padding:10px 12px;text-align:right;font-size:18px;font-weight:700;outline:none;transition:0.2s;"
                           :style="parseFloat(bayar||0)>=grandTotal ? 'border-color:#16a34a;' : ''" placeholder="0">
                </div>
                <div style="display:flex;justify-content:space-between;font-weight:700;margin-top:6px;">
                    <span style="color:#16a34a;">Kembali</span>
                    <span style="color:#16a34a;font-size:18px;">Rp<span x-text="format(kembali)"></span></span>
                </div>
                <button @click="checkout()" :disabled="!bisaBayar"
                        style="width:100%;padding:14px;border-radius:10px;font-size:16px;font-weight:700;border:none;margin-top:10px;cursor:pointer;transition:0.2s;color:white;"
                        :style="bisaBayar ? 'background:linear-gradient(135deg,#16a34a,#15803d);box-shadow:0 2px 8px rgba(22,163,74,0.3);' : 'background:#d1d5db;'">
                    &#x1F4B3; Bayar Rp<span x-text="format(grandTotal)"></span>
                </button>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener("alpine:init", () => {
        // Menyimpan last calculation untuk quick calc
        let calcTimer = null;

        Alpine.data("posApp", () => ({
            cart: [],
            search: "",
            bayar: 0,
            kembali: 0,
            // Server-side calc results
            subtotalItem: 0,
            globalDiskon: 0,
            pajak: 0,
            grandTotal: 0,
            // Global discount
            globalDp: "",
            globalDa: "",
            taxP: "",

            get bisaBayar() {
                return this.cart.length > 0 && parseFloat(this.bayar||0) >= this.grandTotal && this.grandTotal > 0;
            },
            get produkKosong() {
                if (!this.search) return false;
                return document.querySelectorAll("#grid-produk > div[x-show]:not([style*='display: none'])").length === 0;
            },
            filterProduk() {},
            adaDiCart(id) {
                return this.cart.some(x => x.id === id);
            },
            tambah(e, id, nama, harga, hpp, stok) {
                const i = this.cart.findIndex(x => x.id === id);
                if (i >= 0) {
                    if (this.cart[i].q < stok) {
                        this.cart[i].q++;
                        this.cart[i].stk = stok;
                    }
                } else {
                    this.cart.push({
                        id, n: nama, h: harga, stk: stok, q: 1,
                        dp: 0, da: 0, diskon: 0
                    });
                }
                this.hitung();
                this.calcServer();
            },
            hitung() {
                // Quick frontend calc
                this.subtotalItem = this.cart.reduce((s, x) => s + x.h * x.q, 0);
                // Item discount calc happens on server
            },
            // Convert nominal input -> persen (when user types Rp)
            toPersen(item) {
                const subtotal = item.q * item.h;
                const da = parseFloat(item.da) || 0;
                if (da > 0 && da < subtotal) {
                    item.dp = 0; // using nominal
                }
                if (da >= subtotal) item.da = subtotal;
            },
            // Convert persen -> nominal approximation
            toNominal(item) {
                const dp = parseFloat(item.dp) || 0;
                if (dp > 0) item.da = 0; // using percent
            },
            // Hitung via server untuk accuracy + pajak
            calcServer() {
                if (calcTimer) clearTimeout(calcTimer);
                calcTimer = setTimeout(() => this._doCalc(), 150);
            },
            async _doCalc() {
                const payload = {
                    items: this.cart.map(i => ({
                        product_id: i.id,
                        quantity: i.q,
                        price: i.h,
                        discount_percent: parseFloat(i.dp) || 0,
                        discount_amount: parseFloat(i.da) || 0,
                    })),
                    global_discount_percent: parseFloat(this.globalDp) || 0,
                    global_discount_amount: parseFloat(this.globalDa) || 0,
                    tax_percent: parseFloat(this.taxP) || 0,
                };

                try {
                    const resp = await fetch("/pos/calculate", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector("meta[name=csrf-token]")?.content || ""
                        },
                        body: JSON.stringify(payload)
                    });
                    const r = await resp.json();

                    // Update display from server calculation
                    this.subtotalItem = r.subtotal || 0;
                    this.globalDiskon = r.global_discount_amount || 0;
                    this.pajak = r.tax_amount || 0;
                    this.grandTotal = r.grand_total || 0;

                    // Sync item discount values from server
                    if (r.items) {
                        r.items.forEach((si, idx) => {
                            if (this.cart[idx]) {
                                this.cart[idx].diskon = si.discount_amount;
                            }
                        });
                    }
                    this.kembali = Math.max(0, (parseFloat(this.bayar) || 0) - this.grandTotal);
                } catch(e) {
                    // Fallback to client-side calc
                    this.grandTotal = this.cart.reduce((s, x) => s + x.h * x.q, 0);
                }
            },
            format(v) { return new Intl.NumberFormat("id-ID").format(v); },
            async checkout() {
                if (!this.bisaBayar) return;
                const btn = event.target;
                btn.disabled = true;
                btn.textContent = "Memproses...";

                try {
                    const resp = await fetch("/pos/checkout", {
                        method: "POST",
                        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": document.querySelector("meta[name=csrf-token]")?.content || "" },
                        body: JSON.stringify({
                            items: this.cart.map(i => ({
                                product_id: i.id,
                                quantity: i.q,
                                price: i.h,
                                discount_percent: parseFloat(i.dp) || 0,
                                discount_amount: parseFloat(i.da) || 0,
                            })),
                            payment: this.bayar,
                            global_discount_percent: parseFloat(this.globalDp) || 0,
                            global_discount_amount: parseFloat(this.globalDa) || 0,
                            tax_percent: parseFloat(this.taxP) || 0,
                        })
                    });
                    const r = await resp.json();
                    if (r.success) {
                        window.location.href = "/pos/receipt/" + r.transaction_id;
                    } else {
                        alert("Gagal: " + (r.message || ""));
                        btn.disabled = false;
                        btn.innerHTML = "&#x1F4B3; Bayar Rp" + this.format(this.grandTotal);
                    }
                } catch(e) {
                    alert("Error: " + e);
                    btn.disabled = false;
                    btn.innerHTML = "&#x1F4B3; Bayar Rp" + this.format(this.grandTotal);
                }
            }
        }));
    });
    </script>
</body>
</html>
