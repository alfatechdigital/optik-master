@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@push('styles')
<style>
.grafik-bar-wrap { height: 100px; display:flex; align-items:flex-end; gap:6px; }
.grafik-bar { flex:1; background:#1e2a5e; opacity:.7; border-radius:4px 4px 0 0; min-height:4px; transition:opacity .2s; }
.grafik-bar:hover { opacity:1; }
.grafik-label { font-size:.7rem; color:#6c757d; text-align:center; }
</style>
@endpush

@section('content')

{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card stat-card border-primary p-3">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <div class="stat-label">Total Pasien</div>
                    <div class="stat-val text-primary">{{ number_format($totalPasien) }}</div>
                </div>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card border-success p-3">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <div class="stat-label">Omzet Hari Ini</div>
                    <div class="stat-val text-success" style="font-size:1.15rem">
                        Rp {{ number_format($omzetHari, 0, ',', '.') }}
                    </div>
                </div>
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-cash-coin"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card border-info p-3">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <div class="stat-label">Transaksi Hari Ini</div>
                    <div class="stat-val text-info">{{ $transaksiHari }}</div>
                </div>
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-receipt"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card border-warning p-3">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <div class="stat-label">Produk Aktif</div>
                    <div class="stat-val text-warning">{{ number_format($totalProduk) }}</div>
                </div>
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-box-seam-fill"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    {{-- Grafik Omzet --}}
    <div class="col-md-8">
        <div class="card p-3 h-100">
            <div class="card-header px-0 pt-0 mb-3">
                <i class="bi bi-bar-chart-fill text-primary me-2"></i>Omzet 7 Hari Terakhir
            </div>
            @php $maxOmzet = $grafikOmzet->max() ?: 1; @endphp
            <div class="grafik-bar-wrap">
                @foreach($grafikOmzet as $label => $val)
                <div style="flex:1; display:flex; flex-direction:column; align-items:center; gap:4px;">
                    <small class="text-muted" style="font-size:.65rem">
                        Rp{{ number_format($val/1000,0) }}k
                    </small>
                    <div class="grafik-bar w-100" style="height:{{ max(4, ($val/$maxOmzet)*90) }}px"
                         title="Rp {{ number_format($val,0,',','.') }}"></div>
                    <div class="grafik-label">{{ $label }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Stok Menipis --}}
    <div class="col-md-4">
        <div class="card p-3 h-100">
            <div class="card-header px-0 pt-0 mb-3">
                <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Stok Menipis
            </div>
            @forelse($stokMenipis as $p)
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                <div>
                    <div class="fw-semibold" style="font-size:.82rem">{{ $p->nama }}</div>
                    <div class="text-muted" style="font-size:.72rem">{{ $p->category->nama }}</div>
                </div>
                <span class="badge {{ $p->stok == 0 ? 'bg-danger' : 'bg-warning text-dark' }}">
                    {{ $p->stok }} {{ $p->satuan }}
                </span>
            </div>
            @empty
            <div class="text-center text-muted py-3">
                <i class="bi bi-check-circle fs-3 text-success"></i>
                <div class="mt-1 small">Semua stok aman</div>
            </div>
            @endforelse
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Transaksi Terbaru --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header p-3 d-flex justify-content-between align-items-center">
                <div><i class="bi bi-clock-history text-primary me-2"></i>Transaksi Terbaru</div>
                <a href="{{ route('transactions.index') }}" class="btn btn-outline-primary btn-sm">Lihat semua</a>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">No. Transaksi</th>
                            <th>Pasien</th>
                            <th>Kasir</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transaksiTerbaru as $trx)
                        <tr>
                            <td class="ps-3">
                                <a href="{{ route('transactions.show', $trx) }}" class="text-decoration-none fw-semibold">
                                    {{ $trx->no_transaksi }}
                                </a>
                            </td>
                            <td>{{ $trx->patient->nama ?? '-' }}</td>
                            <td>{{ $trx->kasir->name ?? '-' }}</td>
                            <td>Rp {{ number_format($trx->total_bayar, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge badge-{{ $trx->status }}">{{ ucfirst($trx->status) }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Belum ada transaksi hari ini</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Top Produk --}}
    <div class="col-md-4">
        <div class="card p-3">
            <div class="card-header px-0 pt-0 mb-3">
                <i class="bi bi-trophy-fill text-warning me-2"></i>Top Produk Bulan Ini
            </div>
            @forelse($topProduk as $i => $p)
            <div class="d-flex align-items-center gap-2 py-2 border-bottom">
                <span class="badge bg-secondary rounded-circle" style="width:24px;height:24px;display:flex;align-items:center;justify-content:center;font-size:.7rem">
                    {{ $i + 1 }}
                </span>
                <div class="flex-fill" style="min-width:0">
                    <div class="fw-semibold text-truncate" style="font-size:.82rem">{{ $p->nama }}</div>
                </div>
                <span class="badge bg-primary bg-opacity-10 text-primary">{{ $p->total_qty }} pcs</span>
            </div>
            @empty
            <div class="text-center text-muted py-3 small">Belum ada data</div>
            @endforelse
        </div>
    </div>
</div>

@endsection
