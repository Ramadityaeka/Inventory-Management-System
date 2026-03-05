@extends('layouts.app')

@section('page-title', 'Detail Permintaan - ' . $publicRequest->request_code)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="{{ route('gudang.public-requests.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
    @if($publicRequest->isCompleted())
        <a href="{{ route('public.request.pdf', $publicRequest->token) }}"
           class="btn btn-danger" target="_blank">
            <i class="bi bi-file-earmark-pdf me-2"></i>Download PDF
        </a>
    @endif
</div>

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@php
    $statusMap = [
        'pending'   => ['label' => 'Menunggu Review', 'class' => 'warning'],
        'approved'  => ['label' => 'Disetujui',       'class' => 'success'],
        'partial'   => ['label' => 'Disetujui Sebagian', 'class' => 'info'],
        'rejected'  => ['label' => 'Ditolak',         'class' => 'danger'],
        'completed' => ['label' => 'Selesai',         'class' => 'primary'],
    ];
    $s = $statusMap[$publicRequest->status] ?? ['label' => $publicRequest->status, 'class' => 'secondary'];
@endphp

<div class="row">
    {{-- Detail Permintaan --}}
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-file-text me-2 text-primary"></i>{{ $publicRequest->request_code }}
                </h6>
                <span class="badge bg-{{ $s['class'] }} fs-6">{{ $s['label'] }}</span>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr><th class="text-muted fw-normal w-40">Pemohon</th><td class="fw-semibold">{{ $publicRequest->requester_name }}</td></tr>
                            <tr><th class="text-muted fw-normal">Unit Tujuan</th><td>{{ $publicRequest->warehouse->name }}</td></tr>
                            <tr><th class="text-muted fw-normal">PIC</th><td>{{ $publicRequest->pic->name }}</td></tr>
                            <tr><th class="text-muted fw-normal">Tanggal</th><td>{{ $publicRequest->created_at->format('d/m/Y H:i') }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        @if($publicRequest->notes)
                            <div class="bg-light rounded p-3">
                                <strong class="small text-muted">Catatan Pemohon:</strong>
                                <p class="mb-0 small mt-1">{{ $publicRequest->notes }}</p>
                            </div>
                        @endif
                        @if($publicRequest->isRejected() && $publicRequest->rejection_reason)
                            <div class="alert alert-danger py-2 mt-2">
                                <strong class="small">Alasan Penolakan:</strong>
                                <p class="mb-0 small mt-1">{{ $publicRequest->rejection_reason }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Daftar Barang --}}
                <h6 class="fw-semibold border-top pt-3 mb-3">Daftar Barang yang Diminta</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Nama Barang</th>
                                <th class="text-center">Diminta</th>
                                @if(!$publicRequest->isPending())
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
                                    @if(!$publicRequest->isPending())
                                        <td class="text-center">{{ $item->quantity_approved ?? '-' }} {{ $item->item->unit }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Tanda Tangan --}}
                <h6 class="fw-semibold border-top pt-3 mb-3">Tanda Tangan</h6>
                <div class="row">
                    <div class="col-md-6 text-center mb-3">
                        <p class="text-muted small mb-2 fw-medium">Pemohon</p>
                        @if($publicRequest->requesterSignature)
                            <div class="border rounded p-2 d-inline-block bg-light">
                                <img src="{{ $publicRequest->requesterSignature->signature_data }}"
                                     alt="TTD Pemohon"
                                     style="max-width: 200px; max-height: 100px; display: block;">
                            </div>
                            <p class="fw-semibold small mt-2 mb-0">{{ $publicRequest->requester_name }}</p>
                            <p class="text-muted" style="font-size:11px;">
                                {{ $publicRequest->requesterSignature->signed_at?->format('d/m/Y H:i') }}
                            </p>
                        @else
                            <div class="border rounded py-4 text-muted small">Belum ada tanda tangan</div>
                        @endif
                    </div>
                    <div class="col-md-6 text-center mb-3">
                        <p class="text-muted small mb-2 fw-medium">PIC / Admin Unit</p>
                        @if($publicRequest->picSignature)
                            <div class="border rounded p-2 d-inline-block bg-light">
                                <img src="{{ $publicRequest->picSignature->signature_data }}"
                                     alt="TTD PIC"
                                     style="max-width: 200px; max-height: 100px; display: block;">
                            </div>
                            <p class="fw-semibold small mt-2 mb-0">{{ $publicRequest->pic->name }}</p>
                            <p class="text-muted" style="font-size:11px;">
                                {{ $publicRequest->picSignature->signed_at?->format('d/m/Y H:i') }}
                            </p>
                        @else
                            <div class="border rounded py-4 text-muted small">
                                @if(in_array($publicRequest->status, ['approved', 'partial']))
                                    <i class="bi bi-clock me-1"></i>Menunggu tanda tangan PIC
                                @else
                                    Belum ada tanda tangan PIC
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Action Panel --}}
    @if($publicRequest->isPending())
        <div class="col-md-4">
            {{-- Form Approve --}}
            <div class="card mb-3 border-success">
                <div class="card-header bg-success bg-opacity-10 text-success fw-semibold">
                    <i class="bi bi-check-circle me-2"></i>Setujui Permintaan
                </div>
                <div class="card-body">
                    <form action="{{ route('gudang.public-requests.approve', $publicRequest->id) }}" method="POST" id="approve-form">
                        @csrf
                        @foreach($publicRequest->items as $i => $item)
                            <div class="mb-3">
                                <label class="form-label small fw-medium">{{ $item->item->name }}</label>
                                <div class="input-group input-group-sm">
                                    <input type="hidden" name="items[{{ $i }}][item_id]" value="{{ $item->item_id }}">
                                    <input type="number" name="items[{{ $i }}][quantity_approved]"
                                           class="form-control"
                                           value="{{ $item->quantity_requested }}"
                                           min="0" max="{{ $item->quantity_requested }}">
                                    <span class="input-group-text">/ {{ $item->quantity_requested }} {{ $item->item->unit }}</span>
                                </div>
                            </div>
                        @endforeach
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle me-1"></i>Konfirmasi Setujui
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Form Reject --}}
            <div class="card border-danger">
                <div class="card-header bg-danger bg-opacity-10 text-danger fw-semibold">
                    <i class="bi bi-x-circle me-2"></i>Tolak Permintaan
                </div>
                <div class="card-body">
                    <form action="{{ route('gudang.public-requests.reject', $publicRequest->id) }}" method="POST" id="reject-form">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea name="rejection_reason" class="form-control form-control-sm" rows="3"
                                      placeholder="Tulis alasan penolakan..." required></textarea>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-danger"
                                    onclick="return confirm('Yakin ingin menolak permintaan ini?')">
                                <i class="bi bi-x-circle me-1"></i>Konfirmasi Tolak
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @elseif(in_array($publicRequest->status, ['approved', 'partial']))
        <div class="col-md-4">
            <div class="card border-warning">
                <div class="card-header bg-warning bg-opacity-10 text-warning fw-semibold">
                    <i class="bi bi-pen me-2"></i>Tanda Tangan Diperlukan
                </div>
                <div class="card-body text-center">
                    <p class="text-muted small mb-3">Permintaan sudah disetujui. Silakan tanda tangan untuk menyelesaikan dokumen.</p>
                    <a href="{{ route('gudang.public-requests.sign', $publicRequest->id) }}" class="btn btn-warning">
                        <i class="bi bi-pen me-1"></i>Tanda Tangan Sekarang
                    </a>
                </div>
            </div>
        </div>
    @elseif($publicRequest->isCompleted())
        <div class="col-md-4">
            <div class="card border-success mb-3">
                <div class="card-header bg-success bg-opacity-10 text-success fw-semibold">
                    <i class="bi bi-check-circle-fill me-2"></i>Dokumen Selesai
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-3">
                        <tr>
                            <td class="text-muted small">Disetujui</td>
                            <td class="small">{{ $publicRequest->approved_at?->format('d/m/Y H:i') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted small">Diselesaikan</td>
                            <td class="small">{{ $publicRequest->completed_at?->format('d/m/Y H:i') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted small">PIC</td>
                            <td class="small fw-semibold">{{ $publicRequest->pic->name }}</td>
                        </tr>
                    </table>
                    <div class="d-grid gap-2">
                        <a href="{{ route('public.request.pdf', $publicRequest->token) }}"
                           class="btn btn-danger" target="_blank">
                            <i class="bi bi-file-earmark-pdf me-2"></i>Download PDF
                        </a>
                        <a href="{{ route('public.request.document', $publicRequest->token) }}"
                           class="btn btn-outline-primary" target="_blank">
                            <i class="bi bi-eye me-2"></i>Lihat Dokumen Publik
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @elseif($publicRequest->isRejected())
        <div class="col-md-4">
            <div class="card border-danger">
                <div class="card-header bg-danger bg-opacity-10 text-danger fw-semibold">
                    <i class="bi bi-x-circle-fill me-2"></i>Permintaan Ditolak
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">Alasan penolakan:</p>
                    <div class="alert alert-danger py-2 mb-0">
                        <p class="mb-0 small">{{ $publicRequest->rejection_reason }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
