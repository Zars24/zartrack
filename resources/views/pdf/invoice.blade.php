<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #1d2740; margin: 0; padding: 32px; }
        .head { display: flex; justify-content: space-between; border-bottom: 3px solid #1d4fd8; padding-bottom: 16px; margin-bottom: 24px; }
        .brand { font-size: 22px; font-weight: 800; color: #1d4fd8; letter-spacing: -.5px; }
        .brand small { display: block; font-size: 9px; font-weight: 500; color: #55627d; letter-spacing: 1px; text-transform: uppercase; }
        h1 { font-size: 18px; margin: 0; text-align: right; color: #1d2740; }
        .muted { color: #7b879e; }
        table { width: 100%; border-collapse: collapse; }
        .parties { margin-bottom: 24px; }
        .parties td { vertical-align: top; padding-right: 20px; }
        .label { font-size: 9px; text-transform: uppercase; letter-spacing: .8px; color: #7b879e; margin-bottom: 4px; }
        .items th { background: #f4f7fc; text-align: left; padding: 8px; font-size: 10px; text-transform: uppercase; letter-spacing: .5px; color: #55627d; border-bottom: 1px solid #e9edf5; }
        .items td { padding: 9px 8px; border-bottom: 1px solid #e9edf5; }
        .right { text-align: right; }
        .totals { margin-top: 12px; width: 300px; margin-left: auto; }
        .totals td { padding: 5px 8px; }
        .totals .grand td { border-top: 2px solid #1d2740; font-weight: 700; font-size: 14px; padding-top: 9px; }
        .badge { display: inline-block; padding: 3px 9px; border-radius: 10px; background: #eef5ff; color: #1d4fd8; font-size: 9px; font-weight: 700; text-transform: uppercase; }
        .foot { margin-top: 40px; font-size: 9px; color: #a8b2c5; text-align: center; border-top: 1px solid #e9edf5; padding-top: 12px; }
    </style>
</head>
<body>
    <div class="head">
        <div>
            <div class="brand">ZarTrack<small>Container Trucking &amp; Logistics</small></div>
            <div class="muted" style="margin-top:8px;font-size:10px;">Jl. Pelabuhan Raya No. 12, Jakarta Utara<br>Telp 021-555-0199 · billing@zartrack.id</div>
        </div>
        <div>
            <h1>INVOICE</h1>
            <div class="muted" style="text-align:right;margin-top:4px;">{{ $invoice->invoice_number }}</div>
            <div style="text-align:right;margin-top:8px;"><span class="badge">{{ $invoice->status }}</span></div>
        </div>
    </div>

    <table class="parties">
        <tr>
            <td style="width:50%">
                <div class="label">Ditagihkan kepada</div>
                <strong>{{ $invoice->order->customer->name ?? '-' }}</strong><br>
                <span class="muted">{{ $invoice->order->customer->address ?? '' }}</span><br>
                <span class="muted">{{ $invoice->order->customer->email ?? '' }}</span>
            </td>
            <td style="width:50%" class="right">
                <div class="label">Tanggal terbit</div>{{ $invoice->issue_date->format('d F Y') }}<br>
                <div class="label" style="margin-top:8px;">Jatuh tempo</div>{{ $invoice->due_date->format('d F Y') }}<br>
                <div class="label" style="margin-top:8px;">Order</div>{{ $invoice->order->order_number }}
            </td>
        </tr>
    </table>

    <table class="items">
        <thead><tr><th>Deskripsi</th><th class="right">Jumlah</th></tr></thead>
        <tbody>
            <tr>
                <td>
                    <strong>{{ config('zartrack.service_types.'.$invoice->order->service_type) }}</strong><br>
                    <span class="muted">{{ $invoice->order->origin }} → {{ $invoice->order->destination }}</span><br>
                    <span class="muted">{{ $invoice->order->containers->count() }} container: {{ $invoice->order->containers->pluck('container_number')->join(', ') }}</span>
                </td>
                <td class="right">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="right">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td></tr>
        <tr><td>Pajak</td><td class="right">Rp {{ number_format($invoice->tax, 0, ',', '.') }}</td></tr>
        <tr class="grand"><td>Total</td><td class="right">Rp {{ number_format($invoice->total, 0, ',', '.') }}</td></tr>
        <tr><td>Dibayar</td><td class="right">Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</td></tr>
        <tr><td><strong>Sisa tagihan</strong></td><td class="right"><strong>Rp {{ number_format($invoice->balance, 0, ',', '.') }}</strong></td></tr>
    </table>

    <div class="foot">Terima kasih atas kepercayaan Anda. Pembayaran melalui transfer ke rekening perusahaan.<br>Dokumen ini dibuat otomatis oleh sistem ZarTrack.</div>
</body>
</html>
