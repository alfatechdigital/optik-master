{{-- resources/views/admin/transactions/index.blade.php --}}
@extends('layouts.admin')
@section('title','Riwayat Transaksi')
@section('page-title','Riwayat Transaksi')
@section('content')
<div class="card">
    <div class="card-header p-3 d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <div><i class="bi bi-receipt-cutoff text-primary me-2"></i>Semua Transaksi</div>
        <form class="d-flex flex-wrap gap-2" method="GET">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="No. transaksi / pasien" value="{{ request('search') }}" style="width:200px">
            <select name="status" class="form-select form-select-sm" style="width:110px">
                <option value="">Semua</option>
                <option value="lunas" {{ request('status')=='lunas'?'selected':'' }}>Lunas</option>
                <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
                <option value="batal" {{ request('status')=='batal'?'selected':'' }}>Batal</option>
            </select>
            <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}" style="width:140px">
            <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}" style="width:140px">
            <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-filter"></i></button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">No. Transaksi</th><th>Pasien</th><th>Kasir</th>
                    <th>Total</th><th>Metode</th><th>Status</th><th>Tanggal</th><th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $trx)
                <tr>
                    <td class="ps-3 fw-semibold">{{ $trx->no_transaksi }}</td>
                    <td>{{ $trx->patient->nama ?? '<span class="text-muted small">Umum</span>' }}</td>
                    <td>{{ $trx->kasir->name ?? '-' }}</td>
                    <td>Rp {{ number_format($trx->total_bayar,0,',','.') }}</td>
                    <td><span class="badge bg-light text-dark border">{{ ucfirst($trx->metode_bayar) }}</span></td>
                    <td><span class="badge badge-{{ $trx->status }}">{{ ucfirst($trx->status) }}</span></td>
                    <td class="text-muted small">{{ $trx->created_at->format('d M Y H:i') }}</td>
                    <td>
                        <a href="{{ route('transactions.show',$trx) }}" class="btn btn-xs btn-outline-primary">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-5">Belum ada transaksi</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($transactions->hasPages())
    <div class="card-footer">{{ $transactions->links() }}</div>
    @endif
</div>
@push('styles')<style>.btn-xs{padding:3px 8px;font-size:.75rem;}</style>@endpush
@endsection
