{{-- resources/views/admin/transactions/index.blade.php --}}
@extends('layouts.admin')
@section('title','Riwayat Transaksi')
@section('page-title','Riwayat Transaksi')

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
    }
    .table-hover tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.05);
    }
    .table > :not(caption) > * > * {
        padding: 1rem 0.75rem;
        border-bottom-color: rgba(0,0,0,.05);
    }
    .table-light th {
        background-color: rgba(248, 249, 250, 0.8);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        border-bottom: 2px solid rgba(0,0,0,.05);
    }
    .badge-status {
        padding: 0.4em 0.8em;
        font-weight: 500;
        letter-spacing: 0.3px;
    }
    .btn-action {
        border-radius: 12px;
        transition: transform 0.2s;
    }
    .btn-action:hover {
        transform: translateY(-2px);
    }
</style>
@endpush

@section('content')
<div class="card glass-card border-0 mb-4">
    <div class="card-header bg-transparent border-0 p-4 d-flex flex-wrap gap-3 justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold text-primary d-flex align-items-center">
            <i class="bi bi-receipt-cutoff me-2 fs-4"></i>Semua Transaksi
        </h5>
        <form class="d-flex flex-wrap gap-2" method="GET">
            <div class="input-group input-group-sm rounded-pill overflow-hidden shadow-sm" style="width:250px;">
                <span class="input-group-text bg-white border-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control border-0 ps-0" placeholder="Ketik No. Transaksi/Pasien..." value="{{ request('search') }}">
            </div>
            
            <select name="status" class="form-select form-select-sm rounded-pill shadow-sm border-0 px-3" style="width:130px">
                <option value="">Status (Semua)</option>
                <option value="lunas" {{ request('status')=='lunas'?'selected':'' }}>Lunas</option>
                <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
                <option value="batal" {{ request('status')=='batal'?'selected':'' }}>Batal</option>
            </select>
            
            <div class="input-group input-group-sm rounded-pill overflow-hidden shadow-sm flex-nowrap" style="width: auto;">
                <span class="input-group-text bg-white border-0 text-muted"><i class="bi bi-calendar3"></i></span>
                <input type="date" name="from" class="form-control border-0 px-2" value="{{ request('from') }}" title="Dari Tanggal">
                <span class="input-group-text bg-light border-0 text-muted px-2">-</span>
                <input type="date" name="to" class="form-control border-0 px-2" value="{{ request('to') }}" title="Sampai Tanggal">
            </div>
            
            <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm btn-action">
                <i class="bi bi-funnel-fill me-1"></i> Filter
            </button>
            
            @if(request()->anyFilled(['search', 'status', 'from', 'to']))
            <a href="{{ url()->current() }}" class="btn btn-sm btn-outline-danger rounded-pill px-3 shadow-sm btn-action">
                <i class="bi bi-x-circle"></i> Reset
            </a>
            @endif
        </form>
    </div>
    <div class="table-responsive px-3 pb-3">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4 rounded-start">No. Transaksi</th>
                    <th>Pasien</th>
                    <th>Kasir</th>
                    <th>Total</th>
                    <th>Potongan BPJS</th>
                    <th>Metode</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                    <th class="text-end pe-4 rounded-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $trx)
                <tr>
                    <td class="ps-4 fw-bold text-primary">{{ $trx->no_transaksi }}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 35px; height: 35px;">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <div>
                                <div class="fw-semibold text-dark">{!! $trx->patient->nama ?? '<span class="text-muted small">Umum</span>' !!}</div>
                                @if(isset($trx->patient->no_hp))
                                    <small class="text-muted">{{ $trx->patient->no_hp }}</small>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="text-muted small"><i class="bi bi-headset me-1"></i>{{ $trx->kasir->name ?? '-' }}</span>
                    </td>
                    <td class="fw-bold">Rp {{ number_format($trx->total_bayar,0,',','.') }}</td>
                    <td>
                        @if($trx->potongan_bpjs > 0)
                            <span class="text-success fw-semibold"><i class="bi bi-shield-check me-1"></i>Rp {{ number_format($trx->potongan_bpjs,0,',','.') }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border rounded-pill px-3 py-2 shadow-sm">
                            <i class="bi bi-credit-card-2-front me-1 text-muted"></i>{{ ucfirst($trx->metode_bayar) }}
                        </span>
                    </td>
                    <td>
                        @php
                            $badgeColor = match($trx->status) {
                                'lunas' => 'bg-success',
                                'pending' => 'bg-warning text-dark',
                                'batal' => 'bg-danger',
                                default => 'bg-secondary'
                            };
                        @endphp
                        <span class="badge {{ $badgeColor }} rounded-pill badge-status shadow-sm">
                            {{ ucfirst($trx->status) }}
                        </span>
                    </td>
                    <td>
                        <div class="text-muted small">
                            <div class="fw-semibold text-dark">{{ $trx->created_at->format('d M Y') }}</div>
                            {{ $trx->created_at->format('H:i') }} WIB
                        </div>
                    </td>
                    <td class="text-end pe-4">
                        <a href="{{ route('transactions.show',$trx) }}" class="btn btn-sm btn-primary rounded-circle shadow-sm btn-action" style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="Lihat Detail">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5">
                        <div class="d-flex flex-column align-items-center justify-content-center text-muted">
                            <i class="bi bi-inbox fs-1 mb-3 opacity-50"></i>
                            <h5>Belum Ada Transaksi</h5>
                            <p class="small mb-0">Coba ubah filter pencarian Anda.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($transactions->hasPages())
    <div class="card-footer bg-transparent border-0 px-4 pb-4 pt-0">
        {{ $transactions->links() }}
    </div>
    @endif
</div>
@endsection
