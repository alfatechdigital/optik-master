{{-- resources/views/admin/transactions/show.blade.php --}}
@extends('layouts.admin')
@section('title', 'Detail Transaksi')
@section('page-title', 'Detail Transaksi')

@push('styles')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 20px;
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    .section-title {
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #0d6efd;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        border-bottom: 2px dashed rgba(13, 110, 253, 0.2);
        padding-bottom: 0.75rem;
    }
    .section-title i {
        margin-right: 0.75rem;
        font-size: 1.1rem;
    }
    .info-label {
        font-size: 0.75rem;
        color: #6c757d;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.2rem;
    }
    .info-value {
        font-size: 0.9rem;
        font-weight: 600;
        color: #212529;
        margin-bottom: 1rem;
    }
    .finance-card {
        background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
        color: white;
    }
    .finance-card .info-label {
        color: rgba(255,255,255,0.8);
    }
    .finance-card .info-value, .finance-card .section-title {
        color: white;
        border-color: rgba(255,255,255,0.2);
    }
    .refraction-table th { background: #f8f9fa; font-size: 0.75rem; text-transform: uppercase; }
    .status-badge { padding: 0.4em 1em; font-weight: 600; letter-spacing: 0.5px; }
</style>
@endpush

@section('content')
<div class="row g-4">
    <!-- Header Invoice -->
    <div class="col-12">
        <div class="card glass-card border-0 mb-0">
            <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="bi bi-receipt fs-2"></i>
                    </div>
                    <div>
                        <h4 class="mb-1 fw-bold text-primary">{{ $transaction->no_transaksi }}</h4>
                        <div class="text-muted small">
                            <i class="bi bi-calendar3 me-1"></i> {{ $transaction->created_at->format('d F Y, H:i') }} WIB 
                            <span class="mx-2">|</span>
                            <i class="bi bi-person-badge me-1"></i> Kasir: {{ $transaction->kasir->name ?? 'Sistem' }}
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 align-items-center mt-3 mt-md-0">
                    @php
                        $badgeColor = match($transaction->status) {
                            'lunas' => 'bg-success',
                            'pending' => 'bg-warning text-dark',
                            'batal' => 'bg-danger',
                            default => 'bg-secondary'
                        };
                    @endphp
                    <span class="badge {{ $badgeColor }} rounded-pill status-badge fs-6 me-3">{{ strtoupper($transaction->status) }}</span>
                    
                    <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                    <a href="{{ route('transactions.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <i class="bi bi-pencil-square me-1"></i> Edit di POS
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Sisi Kiri (Produk & Teknis) -->
    <div class="col-lg-7">
        
        <!-- Detail Lensa & Frame -->
        <div class="card glass-card">
            <div class="card-body p-4">
                <h6 class="section-title"><i class="bi bi-box-seam"></i> Produk & Frame</h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-label">Lensa</div>
                        <div class="info-value">{{ $transaction->lensa ?: '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-label">Kode Frame</div>
                        <div class="info-value">
                            @if($transaction->kode_frame)
                                <span class="badge bg-dark bg-opacity-10 text-dark border">{{ $transaction->kode_frame }}</span>
                            @else
                                -
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-label">Merk / Seri</div>
                        <div class="info-value">{{ $transaction->seri ?: '-' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-label">Warna</div>
                        <div class="info-value">{{ $transaction->warna ?: '-' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-label">Keterangan Tambahan</div>
                        <div class="info-value">{{ $transaction->keterangan_frame ?: '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pemeriksaan Refraksi -->
        <div class="card glass-card">
            <div class="card-body p-4">
                <h6 class="section-title"><i class="bi bi-eye"></i> Pemeriksaan Refraksi (Ukuran)</h6>
                <div class="table-responsive bg-white rounded-3 border">
                    <table class="table refraction-table text-center align-middle mb-0">
                        <thead>
                            <tr>
                                <th width="80">Mata</th><th>SPH</th><th>CYL</th><th>AXIS</th><th>ADD</th><th>MPD</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="fw-bold text-primary">OD</td>
                                <td>{{ $transaction->od_sph ?: '-' }}</td>
                                <td>{{ $transaction->od_cyl ?: '-' }}</td>
                                <td>{{ $transaction->od_axis ?: '-' }}</td>
                                <td>{{ $transaction->od_add ?: '-' }}</td>
                                <td>{{ $transaction->od_mpd ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-danger">OS</td>
                                <td>{{ $transaction->os_sph ?: '-' }}</td>
                                <td>{{ $transaction->os_cyl ?: '-' }}</td>
                                <td>{{ $transaction->os_axis ?: '-' }}</td>
                                <td>{{ $transaction->os_add ?: '-' }}</td>
                                <td>{{ $transaction->os_mpd ?: '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                @if($transaction->catatan)
                <div class="mt-4 p-3 bg-warning bg-opacity-10 text-dark rounded-3 border border-warning border-opacity-25">
                    <div class="info-label text-warning mb-1"><i class="bi bi-chat-left-text me-1"></i> Catatan Khusus Laboratorium / Faset</div>
                    <div class="mb-0 fw-medium">{{ $transaction->catatan }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Jadwal & Pengerjaan Lab -->
        <div class="card glass-card">
            <div class="card-body p-4">
                <h6 class="section-title"><i class="bi bi-tools"></i> Jadwal Pengerjaan & Laboratorium</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="info-label">Tanggal Order</div>
                        <div class="info-value">{{ $transaction->tgl_order ? \Carbon\Carbon::parse($transaction->tgl_order)->format('d/m/Y') : '-' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-label">Tanggal Faset</div>
                        <div class="info-value">{{ $transaction->tgl_faset ? \Carbon\Carbon::parse($transaction->tgl_faset)->format('d/m/Y') : '-' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-label">Selesai Faset</div>
                        <div class="info-value text-success">{{ $transaction->tgl_selesai_faset ? \Carbon\Carbon::parse($transaction->tgl_selesai_faset)->format('d/m/Y') : '-' }}</div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="info-label">Lab Lensa</div>
                        <div class="info-value">{{ $transaction->lab ?: '-' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-label">Tempat Faset</div>
                        <div class="info-value">{{ $transaction->tempat_faset ?: '-' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-label">Tgl Lensa Datang</div>
                        <div class="info-value">{{ $transaction->tgl_datang_faset ? \Carbon\Carbon::parse($transaction->tgl_datang_faset)->format('d/m/Y') : '-' }}</div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="info-label">No Legalisasi</div>
                        <div class="info-value">{{ $transaction->no_legalisasi ?: '-' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-label">Tgl Legalisasi</div>
                        <div class="info-value">{{ $transaction->tgl_legalisasi ? \Carbon\Carbon::parse($transaction->tgl_legalisasi)->format('d/m/Y') : '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Sisi Kanan (Pasien & Info Finansial) -->
    <div class="col-lg-5">
        
        <!-- Data Pasien -->
        <div class="card glass-card">
            <div class="card-body p-4">
                <h6 class="section-title"><i class="bi bi-person-badge"></i> Data Pasien</h6>
                
                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                    <div class="bg-light text-primary rounded-circle d-flex align-items-center justify-content-center me-3 border" style="width: 50px; height: 50px;">
                        <i class="bi bi-person fs-3"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold">{!! $transaction->patient->nama ?? ($transaction->nama_pasien ?? '<span class="text-muted">Umum / Tidak Ada Nama</span>') !!}</h5>
                        <div class="text-muted small mt-1">
                            @if($transaction->patient && $transaction->patient->no_bpjs)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 me-1">BPJS: {{ $transaction->patient->no_bpjs }}</span>
                            @elseif($transaction->no_bpjs)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 me-1">BPJS: {{ $transaction->no_bpjs }}</span>
                            @endif
                            <i class="bi bi-telephone-fill ms-1"></i> {{ $transaction->patient->no_hp ?? ($transaction->telp_pasien ?? '-') }}
                        </div>
                    </div>
                </div>

                <div class="info-label">Alamat Lengkap</div>
                <div class="info-value">{{ $transaction->patient->alamat ?? ($transaction->alamat_pasien ?? '-') }}</div>

                <div class="info-label">Asal Resep / Rekomendasi</div>
                <div class="info-value mb-0">
                    @if($transaction->asal_resep)
                        <span class="badge bg-light text-dark border"><i class="bi bi-journal-medical me-1"></i> {{ $transaction->asal_resep }}</span>
                    @else
                        -
                    @endif
                </div>
            </div>
        </div>

        <!-- Rincian Pembayaran -->
        <div class="card glass-card finance-card border-0 shadow-lg">
            <div class="card-body p-4">
                <h6 class="section-title"><i class="bi bi-cash-stack"></i> Rincian Pembayaran</h6>
                
                <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-white bg-opacity-10 rounded-3 border border-white border-opacity-25">
                    <div class="info-label mb-0 text-white">Tipe Transaksi</div>
                    <div class="fs-6 fw-bold text-white text-uppercase tracking-wide">
                        @if($transaction->typefaktur == 2)
                            <i class="bi bi-shield-plus me-1"></i> KLAIM BPJS
                        @else
                            <i class="bi bi-wallet2 me-1"></i> TUNAI / UMUM
                        @endif
                    </div>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <div class="info-label pt-1 text-white opacity-75">Harga Jual / Total</div>
                    <div class="info-value fs-5 mb-0">Rp {{ number_format($transaction->harga_jual ?? $transaction->total_harga ?? 0, 0, ',', '.') }}</div>
                </div>
                
                <div class="d-flex justify-content-between mb-2">
                    <div class="info-label pt-1 text-white opacity-75">Potongan / Diskon (-)</div>
                    <div class="info-value mb-0 text-white opacity-75">Rp {{ number_format($transaction->potongan ?? $transaction->diskon_nominal ?? 0, 0, ',', '.') }}</div>
                </div>
                
                <div class="d-flex justify-content-between mb-3 pb-3 border-bottom border-white border-opacity-25">
                    <div class="info-label pt-1 text-white opacity-75">Deposit / Dibayar</div>
                    <div class="info-value mb-0">Rp {{ number_format($transaction->dp ?? $transaction->bayar ?? 0, 0, ',', '.') }}</div>
                </div>
                
                <div class="d-flex justify-content-between align-items-end mt-2">
                    <div class="info-label mb-0 text-white opacity-75">Sisa Tagihan</div>
                    @php
                        $harga = $transaction->harga_jual ?? $transaction->total_harga ?? 0;
                        $dp = $transaction->dp ?? $transaction->bayar ?? 0;
                        $pot = $transaction->potongan ?? $transaction->diskon_nominal ?? 0;
                        $sisa = max(0, $harga - $dp - $pot);
                    @endphp
                    <div class="fs-2 fw-bold text-white lh-1">
                        <small class="fs-5 fw-normal opacity-75">Rp</small> {{ number_format($sisa, 0, ',', '.') }}
                    </div>
                </div>
                
                @if($sisa == 0)
                <div class="mt-3 text-end">
                    <span class="badge bg-white text-success rounded-pill px-3 py-2 shadow-sm"><i class="bi bi-check-circle-fill me-1"></i> LUNAS</span>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Rencana Ambil -->
        <div class="card glass-card">
            <div class="card-body p-4">
                <h6 class="section-title"><i class="bi bi-box-arrow-up-right"></i> Rencana & Status Pengambilan</h6>
                
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="info-label">Tanggal Rencana Ambil</div>
                        <div class="info-value mb-0 fs-5 text-primary">
                            @if($transaction->tgl_selesai_janji)
                                <i class="bi bi-calendar-check me-2"></i>{{ \Carbon\Carbon::parse($transaction->tgl_selesai_janji)->format('d F Y') }}
                            @else
                                <span class="text-muted"><i class="bi bi-calendar-x me-2"></i> Belum dijadwalkan</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="text-end">
                        <div class="info-label">Status</div>
                        @if($transaction->diambil == 1)
                            <span class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill px-3 py-2"><i class="bi bi-check2-all me-1"></i> SUDAH DIAMBIL</span>
                        @else
                            <span class="badge bg-warning bg-opacity-10 text-dark border border-warning rounded-pill px-3 py-2"><i class="bi bi-clock-history me-1"></i> BELUM DIAMBIL</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
