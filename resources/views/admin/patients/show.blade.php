@extends('layouts.admin')
@section('title', 'Detail Pasien')
@section('page-title', 'Detail Pasien')

@section('content')
<div class="row g-3">
    {{-- Info Pasien --}}
    <div class="col-md-4">
        <div class="card p-3 mb-3">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                     style="width:56px;height:56px;font-size:1.5rem;color:#1e2a5e">
                    <i class="bi bi-person-fill"></i>
                </div>
                <div>
                    <div class="fw-bold fs-6">{{ $patient->nama }}</div>
                    <span class="badge bg-secondary">{{ $patient->no_rm }}</span>
                </div>
            </div>
            <table class="table table-sm mb-0">
                <tr>
                    <td class="text-muted ps-0">Jenis Kelamin</td>
                    <td>{{ $patient->jenis_kelamin == 'L' ? 'Laki-laki' : ($patient->jenis_kelamin == 'P' ? 'Perempuan' : '-') }}</td>
                </tr>
                <tr>
                    <td class="text-muted ps-0">Tanggal Lahir</td>
                    <td>{{ $patient->tanggal_lahir ? $patient->tanggal_lahir->format('d M Y') . ' (' . $patient->umur . ' th)' : '-' }}</td>
                </tr>
                <tr>
                    <td class="text-muted ps-0">No. HP</td>
                    <td>{{ $patient->no_hp ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="text-muted ps-0">Email</td>
                    <td>{{ $patient->email ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="text-muted ps-0">Alamat</td>
                    <td>{{ $patient->alamat ?? '-' }}</td>
                </tr>
                @if($patient->riwayat_penyakit)
                <tr>
                    <td class="text-muted ps-0">Riwayat</td>
                    <td><span class="badge bg-warning text-dark">{{ $patient->riwayat_penyakit }}</span></td>
                </tr>
                @endif
            </table>
        </div>
        <div class="d-flex gap-2">
            @can('patient.edit')
            <a href="{{ route('patients.edit', $patient) }}" class="btn btn-outline-warning btn-sm flex-fill">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
            @endcan
            @can('medical_record.create')
            <a href="{{ route('medical-records.create', ['patient_id' => $patient->id]) }}" class="btn btn-primary btn-sm flex-fill">
                <i class="bi bi-clipboard2-plus me-1"></i>Rekam Medis
            </a>
            @endcan
        </div>
    </div>

    <div class="col-md-8">
        {{-- Rekam Medis / Histori Kunjungan --}}
        <div class="card mb-3">
            <div class="card-header p-3">
                <i class="bi bi-clipboard2-pulse text-primary me-2"></i>
                Histori Rekam Medis ({{ $patient->medicalRecords->count() }} kunjungan)
            </div>
            @forelse($patient->medicalRecords as $rm)
            <div class="p-3 border-bottom">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="badge bg-primary me-2">{{ $rm->tanggal_kunjungan->format('d M Y') }}</span>
                        <small class="text-muted">Dokter: {{ $rm->dokter->name ?? '-' }}</small>
                    </div>
                    <a href="{{ route('medical-records.show', $rm) }}" class="btn btn-xs btn-outline-info">
                        <i class="bi bi-eye"></i> Detail
                    </a>
                </div>
                @if($rm->keluhan)
                <div class="text-muted small mb-2"><i class="bi bi-chat-dots me-1"></i>{{ $rm->keluhan }}</div>
                @endif
                {{-- Tabel Resep --}}
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0" style="font-size:.8rem">
                        <thead class="table-light">
                            <tr>
                                <th>Mata</th><th>SPH</th><th>CYL</th><th>AXIS</th><th>ADD</th><th>PD</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge bg-danger">OD (Kanan)</span></td>
                                <td>{{ $rm->formatResep($rm->od_sph) }}</td>
                                <td>{{ $rm->formatResep($rm->od_cyl) }}</td>
                                <td>{{ $rm->od_axis ?? '-' }}°</td>
                                <td>{{ $rm->od_add ? '+'.number_format($rm->od_add,2) : '-' }}</td>
                                <td>{{ $rm->od_pd ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-info">OS (Kiri)</span></td>
                                <td>{{ $rm->formatResep($rm->os_sph) }}</td>
                                <td>{{ $rm->formatResep($rm->os_cyl) }}</td>
                                <td>{{ $rm->os_axis ?? '-' }}°</td>
                                <td>{{ $rm->os_add ? '+'.number_format($rm->os_add,2) : '-' }}</td>
                                <td>{{ $rm->os_pd ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            @empty
            <div class="p-4 text-center text-muted">
                <i class="bi bi-clipboard2 fs-2 d-block mb-2 opacity-25"></i>
                Belum ada rekam medis
            </div>
            @endforelse
        </div>

        {{-- Histori Transaksi --}}
        <div class="card">
            <div class="card-header p-3">
                <i class="bi bi-receipt text-success me-2"></i>
                Histori Transaksi ({{ $patient->transactions->count() }})
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">No. Transaksi</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Metode</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($patient->transactions as $trx)
                        <tr>
                            <td class="ps-3">
                                <a href="{{ route('transactions.show', $trx) }}" class="text-decoration-none">
                                    {{ $trx->no_transaksi }}
                                </a>
                            </td>
                            <td>{{ $trx->created_at->format('d M Y') }}</td>
                            <td>Rp {{ number_format($trx->total_bayar, 0, ',', '.') }}</td>
                            <td>{{ ucfirst($trx->metode_bayar) }}</td>
                            <td><span class="badge badge-{{ $trx->status }}">{{ ucfirst($trx->status) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">Belum ada transaksi</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>.btn-xs{padding:3px 8px;font-size:.75rem;}</style>
@endpush
@endsection
