<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $publicRequest->request_code }} - Inventory ESDM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f0f4f8; font-family: 'Segoe UI', sans-serif; }
        .doc-card { background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .signature-img { max-width: 200px; max-height: 100px; border: 1px solid #dee2e6; border-radius: 6px; background: #f8f9fa; }

        /* ══════════════════════════════════════════
           PRINT STYLES — Match PDF template exactly
           ══════════════════════════════════════════ */
        @media print {
            @page { size: A4 portrait; margin: 20mm 30mm 20mm 30mm; }

            /* Hide screen-only elements */
            .no-print, .btn, a.btn, .screen-view { display: none !important; }

            /* Show print-only view */
            .print-view { display: block !important; }

            body {
                background-color: #fff !important;
                font-family: 'Segoe UI', DejaVu Sans, sans-serif;
                font-size: 11px;
                color: #000;
                padding: 0;
                margin: 0;
            }
            .container { max-width: 100% !important; padding: 0 !important; }

            /* Header */
            .print-header { text-align: center; margin-bottom: 14px; }
            .print-header .org { font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
            .print-header .sub { font-size: 10px; margin-top: 2px; }
            .print-header .doc-title { font-size: 13px; font-weight: bold; text-transform: uppercase; margin-top: 6px; letter-spacing: 1px; }
            .print-header .req-code { font-size: 11px; margin-top: 3px; }
            .print-header-line { border-top: 2px solid #000; border-bottom: 1px solid #000; margin-bottom: 12px; padding: 3px 0; }

            /* Info grid */
            .print-info-grid { width: 100%; margin-bottom: 12px; border-collapse: collapse; }
            .print-info-grid td { padding: 2px 4px; font-size: 11px; vertical-align: top; }
            .print-info-grid .lbl { width: 110px; color: #333; }
            .print-info-grid .sep { width: 10px; }

            /* Items table */
            table.print-items { width: 100%; border-collapse: collapse; margin-bottom: 0; }
            table.print-items th { border: 1px solid #000; padding: 5px 7px; text-align: center; font-size: 10px; font-weight: bold; background: #f2f2f2; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            table.print-items th.left { text-align: left; }
            table.print-items td { border: 1px solid #000; padding: 5px 7px; font-size: 10px; }
            table.print-items td.center { text-align: center; }

            /* Notes */
            .print-notes { border: 1px solid #bbb; padding: 6px 9px; font-size: 10px; margin-top: 10px; }

            /* Signature */
            .print-sig-section { page-break-inside: avoid; margin-top: 24px; }
            .print-sig-table { width: 100%; border-collapse: collapse; }
            .print-sig-table td { width: 50%; text-align: center; vertical-align: top; padding: 0 8px; font-size: 11px; }
            .print-sig-date { text-align: right; margin-bottom: 18px; font-size: 11px; }
            .print-sig-role { font-size: 10px; margin-bottom: 6px; }
            .print-sig-img { max-width: 160px; max-height: 70px; display: block; margin: 0 auto; }
            .print-sig-line { border-bottom: 1px solid #000; width: 140px; margin: 0 auto 4px auto; }
            .print-sig-name { font-weight: bold; font-size: 11px; margin-top: 4px; }
            .print-sig-stamp { font-size: 9px; color: #555; margin-top: 2px; }
            .print-sig-empty { height: 60px; }

            .print-footer { margin-top: 18px; border-top: 1px solid #bbb; padding-top: 5px; font-size: 9px; color: #666; text-align: center; }
        }

        /* Hide print view on screen */
        .print-view { display: none; }
    </style>
</head>
<body>
<div class="container py-4">
    {{-- ══ TOOLBAR (screen only) ══ --}}
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <a href="{{ route('public.request.status') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-printer me-1"></i>Print
            </button>
            @if($publicRequest->isCompleted())
                <a href="{{ route('public.request.pdf', $publicRequest->token) }}" class="btn btn-danger btn-sm">
                    <i class="bi bi-file-earmark-pdf me-1"></i>Download PDF
                </a>
            @endif
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         SCREEN VIEW (hidden when printing)
         ══════════════════════════════════════════ --}}
    <div class="screen-view">
        <div class="doc-card p-4 mb-4">
            {{-- Header --}}
            <div class="text-center border-bottom pb-3 mb-3">
                <h5 class="fw-bold text-primary">SISTEM INVENTORY ESDM</h5>
                <p class="text-muted small mb-1">Inspektorat Jenderal Kementerian Energi dan Sumber Daya Mineral</p>
                <h6 class="fw-bold mt-2">SURAT PERMINTAAN BARANG</h6>
                <h5 class="text-primary fw-bold">{{ $publicRequest->request_code }}</h5>
            </div>

            {{-- Status Badge --}}
            <div class="text-center mb-4">
                @php
                    $statusMap = [
                        'pending'   => ['label' => 'Menunggu Review', 'class' => 'warning'],
                        'approved'  => ['label' => 'Disetujui',       'class' => 'success'],
                        'partial'   => ['label' => 'Disetujui Sebagian', 'class' => 'info'],
                        'rejected'  => ['label' => 'Ditolak',         'class' => 'danger'],
                        'completed' => ['label' => 'Selesai',         'class' => 'primary'],
                    ];
                    $s = $statusMap[$publicRequest->status] ?? ['label' => ucfirst($publicRequest->status), 'class' => 'secondary'];
                @endphp
                <span class="badge bg-{{ $s['class'] }} fs-6 px-4 py-2">{{ $s['label'] }}</span>
                <div class="text-muted small mt-2">
                    Diajukan: {{ $publicRequest->created_at->format('d/m/Y H:i') }}
                    @if($publicRequest->completed_at)
                        · Selesai: {{ $publicRequest->completed_at->format('d/m/Y H:i') }}
                    @endif
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr><th class="text-muted fw-normal">Pemohon</th><td class="fw-semibold">{{ $publicRequest->requester_name }}</td></tr>
                        <tr><th class="text-muted fw-normal">Unit Tujuan</th><td>{{ $publicRequest->warehouse->name }}</td></tr>
                        <tr><th class="text-muted fw-normal">PIC</th><td>{{ $publicRequest->pic->name }}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    @if($publicRequest->notes)
                        <div class="bg-light rounded p-3">
                            <strong class="small text-muted">Catatan:</strong>
                            <p class="mb-0 small mt-1">{{ $publicRequest->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            @if($publicRequest->isRejected() && $publicRequest->rejection_reason)
                <div class="alert alert-danger mb-4">
                    <strong><i class="bi bi-x-circle me-1"></i>Alasan Penolakan:</strong>
                    <p class="mb-0 mt-1">{{ $publicRequest->rejection_reason }}</p>
                </div>
            @endif

            <h6 class="fw-bold border-bottom pb-2 mb-3">Daftar Barang yang Diminta</h6>
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nama Barang</th>
                            <th class="text-center">Diminta</th>
                            @if(!$publicRequest->isPending() && !$publicRequest->isRejected())
                                <th class="text-center">Disetujui</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($publicRequest->items as $i => $item)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $item->item->name }}</td>
                                <td class="text-center">{{ $item->quantity_requested }} {{ $item->item->unit }}</td>
                                @if(!$publicRequest->isPending() && !$publicRequest->isRejected())
                                    <td class="text-center">
                                        {{ $item->quantity_approved ?? '-' }} {{ $item->item->unit }}
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <h6 class="fw-bold border-bottom pb-2 mb-3">Tanda Tangan</h6>
            <div class="row">
                <div class="col-md-6 text-center mb-3">
                    <p class="text-muted small mb-1">Pemohon</p>
                    @if($publicRequest->requesterSignature)
                        <img src="{{ $publicRequest->requesterSignature->signature_data }}" alt="TTD Pemohon" class="signature-img d-block mx-auto mb-1">
                        <p class="fw-semibold small">{{ $publicRequest->requester_name }}</p>
                    @else
                        <div class="border rounded py-4 text-muted small">Belum ada tanda tangan</div>
                    @endif
                </div>
                <div class="col-md-6 text-center mb-3">
                    <p class="text-muted small mb-1">PIC / Admin Gudang</p>
                    @if($publicRequest->isCompleted() && $publicRequest->picSignature)
                        <img src="{{ $publicRequest->picSignature->signature_data }}" alt="TTD PIC" class="signature-img d-block mx-auto mb-1">
                        <p class="fw-semibold small">{{ $publicRequest->pic->name }}</p>
                    @else
                        <div class="border rounded py-4 text-muted small">Menunggu tanda tangan PIC</div>
                    @endif
                </div>
            </div>
        </div>

        @if($publicRequest->isCompleted())
            <div class="text-center no-print">
                <a href="{{ route('public.request.pdf', $publicRequest->token) }}" class="btn btn-danger">
                    <i class="bi bi-file-earmark-pdf me-2"></i>Download Dokumen PDF
                </a>
            </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════
         PRINT VIEW — Exact copy of PDF template
         (hidden on screen, shown only when printing)
         ══════════════════════════════════════════ --}}
    <div class="print-view">
        {{-- Header --}}
        <div class="print-header">
            <div class="org">Kementerian Energi dan Sumber Daya Mineral</div>
            <div class="sub">Inspektorat Jenderal</div>
            <div class="doc-title">Bon Permintaan Barang</div>
            <div class="req-code">No. {{ $publicRequest->request_code }}</div>
        </div>
        <div class="print-header-line"></div>

        @php
            $statusLabel = [
                'pending'   => 'Menunggu Review',
                'approved'  => 'Disetujui',
                'partial'   => 'Disetujui Sebagian',
                'rejected'  => 'Ditolak',
                'completed' => 'Selesai',
            ][$publicRequest->status] ?? ucfirst($publicRequest->status);
        @endphp

        {{-- Info Grid --}}
        <table class="print-info-grid">
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

        {{-- Items Table --}}
        <table class="print-items">
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

        {{-- Notes --}}
        @if($publicRequest->notes)
        <div class="print-notes"><strong>Catatan:</strong> {{ $publicRequest->notes }}</div>
        @endif

        {{-- Signatures --}}
        <div class="print-sig-section">
            <div class="print-sig-date">Jakarta, {{ ($publicRequest->completed_at ?? $publicRequest->created_at)->format('d/m/Y') }}</div>
            <table class="print-sig-table">
                <tr>
                    <td>
                        <div class="print-sig-role">Pemohon,</div>
                        @if($publicRequest->requesterSignature)
                            <img src="{{ $publicRequest->requesterSignature->signature_data }}" class="print-sig-img" alt="TTD Pemohon">
                        @else
                            <div class="print-sig-empty"></div>
                        @endif
                        <div class="print-sig-line"></div>
                        <div class="print-sig-name">{{ $publicRequest->requester_name }}</div>
                        @if($publicRequest->requesterSignature)
                            <div class="print-sig-stamp">Ditandatangani: {{ $publicRequest->requesterSignature->signed_at?->format('d/m/Y H:i') }}</div>
                        @endif
                    </td>
                    <td>
                        <div class="print-sig-role">Mengetahui,</div>
                        @if($publicRequest->picSignature)
                            <img src="{{ $publicRequest->picSignature->signature_data }}" class="print-sig-img" alt="TTD PIC">
                        @else
                            <div class="print-sig-empty"></div>
                        @endif
                        <div class="print-sig-line"></div>
                        <div class="print-sig-name">{{ $publicRequest->pic->name ?? '-' }}</div>
                        <div class="print-sig-stamp">PIC / Admin Gudang</div>
                        @if($publicRequest->picSignature)
                            <div class="print-sig-stamp">Ditandatangani: {{ $publicRequest->picSignature->signed_at?->format('d/m/Y H:i') }}</div>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        {{-- Footer --}}
        <div class="print-footer">
            Dicetak: {{ now()->format('d/m/Y H:i') }} &nbsp;&bull;&nbsp; Sistem Inventory ESDM &nbsp;&bull;&nbsp; Dokumen sah dengan tanda tangan digital
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
