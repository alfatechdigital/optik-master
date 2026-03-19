{{-- resources/views/admin/medical-records/show.blade.php --}}
@extends('layouts.admin')
@section('title','Detail Rekam Medis')
@section('page-title','Detail Rekam Medis')

@section('content')
<div class="row justify-content-center">
<div class="col-xl-9">

{{-- Info Header --}}
<div class="card mb-3">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h5 class="fw-bold mb-1">{{ $medicalRecord->patient->nama }}</h5>
                <span class="badge bg-secondary me-2">{{ $medicalRecord->patient->no_rm }}</span>
                <span class="badge bg-primary">{{ $medicalRecord->tanggal_kunjungan->format('d M Y') }}</span>
            </div>
            <div class="d-flex gap-2">
                @can('medical_record.edit')
                <a href="{{ route('medical-records.edit',$medicalRecord) }}" class="btn btn-warning btn-sm">
                    <i class="bi bi-pencil me-1"></i>Edit
                </a>
                @endcan
                <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-printer me-1"></i>Cetak
                </button>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted ps-0" style="width:120px">Dokter</td><td>: {{ $medicalRecord->dokter->name ?? '-' }}</td></tr>
                    <tr><td class="text-muted ps-0">Keluhan</td><td>: {{ $medicalRecord->keluhan ?? '-' }}</td></tr>
                    <tr><td class="text-muted ps-0">Jenis Lensa</td><td>: {{ $medicalRecord->jenis_lensa ?? '-' }}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted ps-0" style="width:140px">Rekomendasi Frame</td><td>: {{ $medicalRecord->rekomendasi_frame ?? '-' }}</td></tr>
                    <tr><td class="text-muted ps-0">PD Total</td><td>: {{ $medicalRecord->pd_total ?? '-' }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Tabel Resep --}}
<div class="card mb-3">
    <div class="card-header p-3"><i class="bi bi-eyeglasses text-primary me-2"></i>Resep Kacamata</div>
    <div class="card-body p-3">
        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:120px">Mata</th>
                        <th>SPH</th><th>CYL</th><th>AXIS</th><th>ADD</th><th>PD</th><th>Visus</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="badge bg-danger fs-6">OD (Kanan)</span></td>
                        <td class="fw-bold">{{ $medicalRecord->formatResep($medicalRecord->od_sph) }}</td>
                        <td class="fw-bold">{{ $medicalRecord->formatResep($medicalRecord->od_cyl) }}</td>
                        <td>{{ $medicalRecord->od_axis ? $medicalRecord->od_axis.'°' : '-' }}</td>
                        <td>{{ $medicalRecord->od_add ? '+'.number_format($medicalRecord->od_add,2) : '-' }}</td>
                        <td>{{ $medicalRecord->od_pd ?? '-' }}</td>
                        <td>{{ $medicalRecord->od_vis ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><span class="badge bg-info fs-6">OS (Kiri)</span></td>
                        <td class="fw-bold">{{ $medicalRecord->formatResep($medicalRecord->os_sph) }}</td>
                        <td class="fw-bold">{{ $medicalRecord->formatResep($medicalRecord->os_cyl) }}</td>
                        <td>{{ $medicalRecord->os_axis ? $medicalRecord->os_axis.'°' : '-' }}</td>
                        <td>{{ $medicalRecord->os_add ? '+'.number_format($medicalRecord->os_add,2) : '-' }}</td>
                        <td>{{ $medicalRecord->os_pd ?? '-' }}</td>
                        <td>{{ $medicalRecord->os_vis ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @if($medicalRecord->catatan)
        <div class="alert alert-light border mt-3 mb-0">
            <strong><i class="bi bi-journal-text me-1"></i>Catatan Dokter:</strong>
            <div class="mt-1">{{ $medicalRecord->catatan }}</div>
        </div>
        @endif
    </div>
</div>

{{-- Transaksi Terkait --}}
@if($medicalRecord->transaction)
<div class="card mb-3">
    <div class="card-header p-3"><i class="bi bi-receipt text-success me-2"></i>Transaksi Terkait</div>
    <div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <div class="fw-bold">{{ $medicalRecord->transaction->no_transaksi }}</div>
                <div class="text-muted small">{{ $medicalRecord->transaction->created_at->format('d M Y H:i') }}</div>
            </div>
            <div class="text-end">
                <div class="fw-bold text-primary fs-5">
                    Rp {{ number_format($medicalRecord->transaction->total_bayar,0,',','.') }}
                </div>
                <span class="badge badge-{{ $medicalRecord->transaction->status }}">
                    {{ ucfirst($medicalRecord->transaction->status) }}
                </span>
            </div>
            <a href="{{ route('transactions.show',$medicalRecord->transaction) }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-eye me-1"></i>Lihat Struk
            </a>
        </div>
    </div>
</div>
@endif

<div class="d-flex gap-2">
    <a href="{{ route('patients.show',$medicalRecord->patient) }}" class="btn btn-outline-secondary">
        <i class="bi bi-person me-1"></i>Profil Pasien
    </a>
    <a href="{{ route('medical-records.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

</div>
</div>
@endsection
