<!DOCTYPE html>
<html>
<head>
    <title>Bukti Pembayaran - Billing Krama</title>
    <style>
        body { font-family: sans-serif; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #444; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 24px; color: #2c3e50; }
        .header p { margin: 2px 0; font-size: 12px; color: #777; }
        
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 5px; }
        .label { font-weight: bold; width: 130px; }

        .invoice-box { border: 1px solid #ddd; padding: 15px; border-radius: 5px; background: #f9f9f9; }
        .total { font-size: 18px; font-weight: bold; color: #27ae60; margin-top: 10px; text-align: right; }
        
        .footer { margin-top: 40px; text-align: center; font-size: 10px; color: #aaa; }
        .stamp { border: 2px dashed #e74c3c; color: #e74c3c; display: inline-block; padding: 5px 20px; font-weight: bold; transform: rotate(-5deg); margin-top: 10px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>DESA ADAT BILLING KRAMA</h1>
        <p>Jalan Raya Desa No. 1, Bali, Indonesia</p>
        <p>Telp: (0361) 123-4567 | Email: admin@billingdesa.id</p>
    </div>

    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="margin: 0;">BUKTI PEMBAYARAN</h2>
        <p style="margin: 0;">No. Ref: #{{ $pembayaran->pembayaran_id }}/{{ date('Ymd') }}</p>
    </div>

    <div class="invoice-box">
        <table class="info-table">
            <tr>
                <td class="label">Nama Krama</td>
                <td>: {{ $pembayaran->tagihan->krama->name }}</td>
            </tr>
            <tr>
                <td class="label">NIK</td>
                <td>: {{ $pembayaran->tagihan->krama->nik }}</td>
            </tr>
            <tr>
                <td class="label">Banjar</td>
                <td>: {{ $pembayaran->tagihan->krama->banjar->nama_banjar ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Bulan Tagihan</td>
                <td>: {{ \Carbon\Carbon::parse($pembayaran->tagihan->tanggal)->format('F Y') }}</td>
            </tr>
            <tr>
                <td class="label">Tanggal Bayar</td>
                <td>: {{ \Carbon\Carbon::parse($pembayaran->tgl_bayar)->format('d F Y H:i') }}</td>
            </tr>
            <tr>
                <td class="label">Metode Bayar</td>
                <td>: {{ $pembayaran->pembayar->name ?? 'Sistem' }} ({{ ucfirst($pembayaran->pembayar->role ?? 'user') }})</td>
            </tr>
        </table>

        <hr style="border: 0; border-top: 1px solid #ddd;">

        <table class="info-table">
            <tr>
                <td>Iuran Wajib</td>
                <td style="text-align: right;">Rp {{ number_format($pembayaran->tagihan->iuran, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Dedosan</td>
                <td style="text-align: right;">Rp {{ number_format($pembayaran->tagihan->dedosan, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Peturuhan</td>
                <td style="text-align: right;">Rp {{ number_format($pembayaran->tagihan->peturuhan, 0, ',', '.') }}</td>
            </tr>
        </table>

        <div class="total">
            TOTAL: Rp {{ number_format($pembayaran->jumlah, 0, ',', '.') }}
        </div>
    </div>

    <div style="text-align: center;">
        <div class="stamp">LUNAS</div>
    </div>

    <div class="footer">
        Dokumen ini diterbitkan secara otomatis oleh Sistem Billing Krama.<br>
        Disimpan otomatis di Arsip Digital Desa pada {{ date('d-m-Y H:i:s') }}
    </div>

</body>
</html>