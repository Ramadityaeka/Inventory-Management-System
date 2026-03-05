<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { size: A4 portrait; margin: 20mm 30mm 20mm 30mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #000; padding: 0 2mm; }

        /* ── Header ── */
        .header { text-align: center; margin-bottom: 14px; }
        .header .org { font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
        .header .sub { font-size: 10px; margin-top: 2px; }
        .header .doc-title { font-size: 13px; font-weight: bold; text-transform: uppercase; margin-top: 6px; letter-spacing: 1px; }
        .header .req-code { font-size: 11px; margin-top: 3px; }
        .header-line { border-top: 2px solid #000; border-bottom: 1px solid #000; margin-bottom: 12px; padding: 3px 0; }

        /* ── Info ── */
        .info-grid { width: 100%; margin-bottom: 12px; border-collapse: collapse; }
        .info-grid td { padding: 2px 4px; font-size: 11px; vertical-align: top; }
        .info-grid .lbl { width: 110px; color: #333; }
        .info-grid .sep { width: 10px; }

        /* ── Items Table ── */
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 0; }
        table.items th { border: 1px solid #000; padding: 5px 7px; text-align: center; font-size: 10px; font-weight: bold; background: #f2f2f2; }
        table.items th.left { text-align: left; }
        table.items td { border: 1px solid #000; padding: 5px 7px; font-size: 10px; }
        table.items td.center { text-align: center; }

        /* ── Notes ── */
        .notes { border: 1px solid #bbb; padding: 6px 9px; font-size: 10px; margin-top: 10px; }

        /* ── Signature ── */
        
        .sig-section { page-break-inside: avoid; margin-top: 24px; }
        .sig-table { width: 100%; border-collapse: collapse; }
        .sig-table td { width: 50%; text-align: center; vertical-align: top; padding: 0 8px; font-size: 11px; }
        .sig-date { text-align: right; margin-bottom: 18px; font-size: 11px; }
        .sig-role { font-size: 10px; margin-bottom: 6px; }
        .sig-img { max-width: 160px; max-height: 70px; display: block; margin: 0 auto; }
        .sig-line { border-bottom: 1px solid #000; width: 140px; margin: 0 auto 4px auto; }
        .sig-name { font-weight: bold; font-size: 11px; margin-top: 4px; }
        .sig-stamp { font-size: 9px; color: #555; margin-top: 2px; }
        .sig-empty { height: 60px; }

        .footer { margin-top: 18px; border-top: 1px solid #bbb; padding-top: 5px; font-size: 9px; color: #666; text-align: center; }
    </style>
</head>
<body>

{{-- ══ HEADER ══ --}}
<div class="header">
    <div class="org">Kementerian Energi dan Sumber Daya Mineral</div>
    <div class="sub">Inspektorat Jenderal</div>
    <div class="doc-title">Bon Permintaan Barang</div>
    <div class="req-code">No. {{ $publicRequest->request_code }}</div>
</div>
<div class="header-line"></div>

@php
    $statusLabel = [
        'pending'   => 'Menunggu Review',
        'approved'  => 'Disetujui',
        'partial'   => 'Disetujui Sebagian',
        'rejected'  => 'Ditolak',
        'completed' => 'Selesai',
    ][$publicRequest->status] ?? ucfirst($publicRequest->status);
@endphp
<table class="info-grid">
    <tr>
        <td class="lbl">Nama Pemohon</td><td class="sep">:</td>
        <td><strong>{{ $publicRequest->requester_name }}</strong></td>
        <td class="lbl">Tanggal Pengajuan</td><td class="sep">:</td>
        <td>{{ $publicRequest->created_at->format('d/m/Y') }}</td>
    </tr>
    <tr>
        <td class="lbl">Unit / Gudang</td><td class="sep">:</td>
        <td>{{ $publicRequest->warehouse->name }}</td>
        <td class="lbl">Status</td><td class="sep">:</td>
        <td><strong>{{ $statusLabel }}</strong></td>
    </tr>
    <tr>
        <td class="lbl">PIC</td><td class="sep">:</td>
        <td>{{ $publicRequest->pic->name }}</td>
        @if($publicRequest->completed_at)
            <td class="lbl">Tanggal Selesai</td><td class="sep">:</td>
            <td>{{ $publicRequest->completed_at->format('d/m/Y') }}</td>
        @else
            <td colspan="3"></td>
        @endif
    </tr>
</table>

<table class="items">
    <thead>
        <tr>
            <th style="width:6%">NO.</th>
            <th class="left">NAMA BARANG</th>
            <th style="width:14%">SATUAN</th>
            <th style="width:14%">DIMINTA</th>
            <th style="width:14%">DISETUJUI</th>
        </tr>
    </thead>
    <tbody>
        @foreach($publicRequest->items as $i => $item)
        <tr>
            <td class="center">{{ $i + 1 }}.</td>
            <td>{{ $item->item->name }}</td>
            <td class="center">{{ $item->item->unit }}</td>
            <td class="center">{{ $item->quantity_requested }}</td>
            <td class="center">{{ $item->quantity_approved ?? '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

@if($publicRequest->notes)
<div class="notes"><strong>Catatan:</strong> {{ $publicRequest->notes }}</div>
@endif

<div class="sig-section">
    <div class="sig-date">Jakarta, {{ ($publicRequest->completed_at ?? $publicRequest->created_at)->format('d/m/Y') }}</div>
    <table class="sig-table">
        <tr>
            <td>
                <div class="sig-role">Pemohon,</div>
                @if($publicRequest->requesterSignature)
                    <img src="{{ $publicRequest->requesterSignature->signature_data }}" class="sig-img" alt="TTD Pemohon">
                @else
                    <div class="sig-empty"></div>
                @endif
                <div class="sig-line"></div>
                <div class="sig-name">{{ $publicRequest->requester_name }}</div>
                @if($publicRequest->requesterSignature)
                    <div class="sig-stamp">Ditandatangani: {{ $publicRequest->requesterSignature->signed_at?->format('d/m/Y H:i') }}</div>
                @endif
            </td>
            <td>
                <div class="sig-role">Mengetahui,</div>
                @if($publicRequest->picSignature)
                    <img src="{{ $publicRequest->picSignature->signature_data }}" class="sig-img" alt="TTD PIC">
                @else
                    <div class="sig-empty"></div>
                @endif
                <div class="sig-line"></div>
                <div class="sig-name">{{ $publicRequest->pic->name ?? '-' }}</div>
                <div class="sig-stamp">PIC / Admin Gudang</div>
                @if($publicRequest->picSignature)
                    <div class="sig-stamp">Ditandatangani: {{ $publicRequest->picSignature->signed_at?->format('d/m/Y H:i') }}</div>
                @endif
            </td>
        </tr>
    </table>
</div>

<div class="footer">
    Dicetak: {{ now()->format('d/m/Y H:i') }} &nbsp;&bull;&nbsp; Sistem Inventory ESDM &nbsp;&bull;&nbsp; Dokumen sah dengan tanda tangan digital
</div>

</body>
</html>
