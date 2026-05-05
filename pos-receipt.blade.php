<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Struk - {{ $transaction->invoice_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* ===== PRINT STYLES ===== */
        @media print {
            @page { margin: 0; size: 80mm auto; }
            body { background: white; margin: 0; padding: 0; width: 80mm; }
            .no-print, .action-bar { display: none !important; }
            #receipt-area { width: 80mm; margin: 0; padding: 4mm; border: none !important; box-shadow: none !important; }
        }

        /* ===== ACTION BAR (tidak ke-print) ===== */
        .action-bar {
            background: #f8fafc; border-bottom: 1px solid #e2e8f0;
            padding: 12px 16px; display: flex; gap: 10px; align-items: center;
            position: sticky; top: 0; z-index: 50;
        }
        .action-bar button, .action-bar a {
            padding: 8px 20px; border-radius: 8px; font-weight: 600; font-size: 14px;
            border: none; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
        }
        .btn-print { background: #4f46e5; color: white; }
        .btn-print:hover { background: #4338ca; }
        .btn-back { background: #e2e8f0; color: #475569; }
        .btn-back:hover { background: #cbd5e1; }

        /* ===== STRUK ===== */
        #receipt-area {
            font-family: 'Courier New', 'Consolas', monospace;
            width: 320px; margin: 20px auto; background: white;
            padding: 16px 20px; border: 1px solid #e2e8f0; border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        /* Kop */
        .r-head { text-align: center; margin-bottom: 6px; }
        .r-head .title { font-size: 18px; font-weight: bold; letter-spacing: 2px; }
        .r-head .sub { font-size: 9px; color: #888; letter-spacing: 3px; text-transform: uppercase; margin-top: 2px; }
        .r-head .inv { font-size: 13px; font-weight: bold; letter-spacing: 1px; margin-top: 8px; }
        .r-line { display: flex; justify-content: space-between; font-size: 10px; color: #555; padding: 1px 0; }
        .r-hr { border: none; border-top: 1px solid #333; margin: 8px 0; }
        .r-hr-dash { border: none; border-top: 1px dashed #aaa; margin: 6px 0; }

        /* Kolom barang */
        .r-col-hdr { display: flex; font-weight: bold; font-size: 10px; border-bottom: 1px dashed #aaa; padding-bottom: 4px; margin-bottom: 2px; }
        .r-col-hdr .cqty { width: 32px; text-align: center; }
        .r-col-hdr .cname { flex: 1; }
        .r-col-hdr .cprice { width: 90px; text-align: right; }

        .r-item { display: flex; font-size: 11px; padding: 2px 0; }
        .r-item .iqty { width: 32px; text-align: center; font-weight: bold; }
        .r-item .iname { flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; padding-left: 2px; }
        .r-item .iprice { width: 90px; text-align: right; font-weight: bold; }

        /* Total */
        .r-total-wrap { margin-top: 2px; }
        .r-total-row { display: flex; justify-content: flex-end; padding: 1px 0; font-size: 11px; }
        .r-total-row .rl { width: 80px; text-align: right; padding-right: 6px; }
        .r-total-row .rv { width: 100px; text-align: right; font-weight: bold; }
        .r-grand { display: flex; justify-content: flex-end; padding: 4px 0; }
        .r-grand .rl { width: 80px; text-align: right; padding-right: 6px; font-size: 12px; }
        .r-grand .rv { width: 100px; text-align: right; font-weight: bold; font-size: 18px; color: #2563eb; }

        /* Footer */
        .r-footer { text-align: center; margin-top: 10px; }
        .r-footer .thanks { font-size: 13px; font-weight: bold; }
        .r-footer .info { font-size: 9px; color: #999; line-height: 1.6; margin-top: 4px; }
        .r-footer .kasir { font-size: 10px; color: #666; margin-top: 6px; border-top: 1px dashed #ddd; padding-top: 8px; }
    </style>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;">

    <!-- ACTION BAR -->
    <div class="action-bar">
        <button onclick="window.print()" class="btn-print">🖨️ Cetak Struk</button>
        <a href="/pos" class="btn-back">⬅ Kembali ke Kasir</a>
        <span style="font-size:12px;color:#94a3b8;margin-left:auto;">
            Tekan <kbd style="background:#e2e8f0;padding:2px 8px;border-radius:4px;font-size:11px;">Ctrl+P</kbd> untuk cetak
        </span>
    </div>

    <!-- STRUK -->
    <div id="receipt-area">
        <div class="r-head">
            <div class="title">🧾 POS WARUNG</div>
            <div class="sub">Point of Sale</div>
            <div class="inv">{{ $transaction->invoice_number }}</div>
        </div>

        <hr class="r-hr">

        <div class="r-line">
            <span>Tanggal</span>
            <span>{{ $transaction->created_at->format('d/m/Y') }}</span>
        </div>
        <div class="r-line">
            <span>Jam</span>
            <span>{{ $transaction->created_at->format('H:i:s') }}</span>
        </div>
        <div class="r-line">
            <span>Kasir</span>
            <span>{{ $transaction->user->name ?? 'Admin' }}</span>
        </div>

        <hr class="r-hr-dash">

        <div class="r-col-hdr">
            <span class="cqty">Qty</span>
            <span class="cname">Nama Barang</span>
            <span class="cprice">Subtotal</span>
        </div>

        @foreach($transaction->items as $item)
        <div class="r-item">
            <span class="iqty">{{ $item->quantity }}x</span>
            <span class="iname">{{ $item->product->name ?? 'Produk' }}</span>
            <span class="iprice">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</span>
        </div>
        @endforeach

        <hr class="r-hr-dash">

        <div class="r-total-wrap">
            <div class="r-grand">
                <span class="rl">TOTAL</span>
                <span class="rv">Rp{{ number_format($transaction->total, 0, ',', '.') }}</span>
            </div>
            <div class="r-total-row">
                <span class="rl">Tunai</span>
                <span class="rv">Rp{{ number_format($transaction->payment, 0, ',', '.') }}</span>
            </div>
            <div class="r-total-row">
                <span class="rl">Kembali</span>
                <span class="rv">Rp{{ number_format($transaction->change, 0, ',', '.') }}</span>
            </div>
        </div>

        <hr class="r-hr">

        <div class="r-footer">
            <div class="thanks">Terima Kasih</div>
            <div class="info">
                Barang yang sudah dibeli tidak dapat dikembalikan<br>
                Kecuali ada kerusakan produksi
            </div>
            <div class="kasir">Dilayani oleh: {{ $transaction->user->name ?? 'Admin' }}</div>
        </div>
    </div>

    <script>
    // Auto-print jika parameter ?auto=1 ada di URL
    if (window.location.search.includes('auto=1')) {
        window.onload = () => setTimeout(() => window.print(), 500);
    }
    </script>
</body>
</html>
