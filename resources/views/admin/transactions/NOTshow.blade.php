@extends('layouts.admin')

@section('title', 'Point of Sale')
@section('page-title', 'Transaksi Baru')

@push('styles')
<style>
    :root {
        --glass-bg: rgba(255, 255, 255, 0.7);
        --glass-border: rgba(255, 255, 255, 0.3);
        --primary-gradient: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
    }

    body {
        background: #f0f2f5;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    .glass-card {
        background: var(--glass-bg);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid var(--glass-border);
        border-radius: 20px;
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
        transition: all 0.3s ease;
        margin-bottom: 1.5rem;
    }

    .glass-card:hover {
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
    }

    .section-title {
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #0d6efd;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
    }

    .section-title i {
        margin-right: 0.75rem;
        font-size: 1.1rem;
    }

    .form-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #6c757d;
        margin-bottom: 0.5rem;
    }

    .form-control-sm, .form-select-sm {
        border-radius: 10px;
        border: 1px solid #dee2e6;
        padding: 0.5rem 0.75rem;
        background: rgba(255, 255, 255, 0.9);
    }

    .form-control-sm:focus {
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        border-color: #0d6efd;
    }

    .refraction-table {
        background: #fff;
        border-radius: 15px;
        overflow: hidden;
        border: 1px solid #eee;
    }

    .refraction-table th {
        background: #f8f9fa;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #495057;
        padding: 10px;
        border-bottom: 2px solid #dee2e6;
    }

    .refraction-table td {
        padding: 8px;
        border-bottom: 1px solid #eee;
    }

    .btn-action {
        border-radius: 12px;
        font-weight: 600;
        padding: 0.6rem 1.2rem;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-action:hover {
        transform: translateY(-2px);
    }

    .finance-card {
        background: var(--primary-gradient);
        color: white;
    }

    .finance-card .form-label { color: rgba(255,255,255,0.8); }
    .finance-card .form-control { 
        background: rgba(255,255,255,0.15); 
        border: 1px solid rgba(255,255,255,0.2);
        color: white;
        font-weight: 700;
    }
    .finance-card .form-control::placeholder { color: rgba(255,255,255,0.4); }

    .sticky-actions {
        position: sticky;
        bottom: 1.5rem;
        z-index: 100;
        margin-top: 2rem;
    }

    .badge-status {
        font-size: 0.7rem;
        padding: 0.4rem 0.8rem;
        border-radius: 50px;
    }

    .text-gradient {
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
</style>
@endpush

@section('content')
<div class="row g-4">
    <div class="col-12">
        <form action="{{ route('transactions.store') }}" method="POST" id="pos-form">
            @csrf
            
            <div class="row">
                {{-- LEFT COLUMN --}}
                <div class="col-lg-7">
                    {{-- 1. Metadata & Timeline --}}
                    <div class="card glass-card">
                        <div class="card-body p-4">
                            <h6 class="section-title"><i class="bi bi-info-circle"></i> Informasi Transaksi</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">No Faktur</label>
                                    <input type="text" name="no_transaksi" class="form-control form-control-sm bg-light" readonly value="{{ \App\Models\Transaction::generateNomor() }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tanggal Faktur</label>
                                    <input type="text" class="form-control form-control-sm bg-light" readonly value="{{ date('d/m/Y') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">No Legalisasi</label>
                                    <input type="text" name="no_legalisasi" class="form-control form-control-sm" placeholder="Input no. legalisasi...">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tgl Legalisasi</label>
                                    <input type="date" name="tgl_legalisasi" class="form-control form-control-sm">
                                </div>
                            </div>

                            <hr class="my-4 opacity-10">

                            <h6 class="section-title"><i class="bi bi-calendar-event"></i> Jadwal & Pengerjaan</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Tgl Order</label>
                                    <input type="date" name="tgl_order" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tgl Faset</label>
                                    <input type="date" name="tgl_faset" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Lab</label>
                                    <input type="text" name="lab" class="form-control form-control-sm" placeholder="Lab penyedia...">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tempat Faset</label>
                                    <input type="text" name="tempat_faset" class="form-control form-control-sm" placeholder="Lokasi faset...">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tgl Selesai Faset</label>
                                    <input type="date" name="tgl_selesai_faset" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Keterangan Tambahan</label>
                                    <textarea name="catatan" class="form-control form-control-sm" rows="2" placeholder="Catatan untuk laboratorium atau faset..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2.Vision / Refraction --}}
                    <div class="card glass-card">
                        <div class="card-body p-4">
                            <h6 class="section-title"><i class="bi bi-eye"></i> Pemeriksaan Refraksi (Ukuran)</h6>
                            <div class="table-responsive refraction-table shadow-sm">
                                <table class="table table-borderless align-middle mb-0 text-center">
                                    <thead>
                                        <tr>
                                            <th width="80">Mata</th>
                                            <th>Sph</th>
                                            <th>Cyl</th>
                                            <th>Axis</th>
                                            <th>Add</th>
                                            <th>Mpd</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="fw-bold text-primary">OD</td>
                                            <td><input type="text" name="od_sph" class="form-control form-control-sm text-center border-0 shadow-none" placeholder="0.00"></td>
                                            <td><input type="text" name="od_cyl" class="form-control form-control-sm text-center border-0 shadow-none" placeholder="0.00"></td>
                                            <td><input type="text" name="od_axis" class="form-control form-control-sm text-center border-0 shadow-none" placeholder="0"></td>
                                            <td><input type="text" name="od_add" class="form-control form-control-sm text-center border-0 shadow-none" placeholder="0.00"></td>
                                            <td><input type="text" name="od_mpd" class="form-control form-control-sm text-center border-0 shadow-none" placeholder="0.0"></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-danger">OS</td>
                                            <td><input type="text" name="os_sph" class="form-control form-control-sm text-center border-0 shadow-none" placeholder="0.00"></td>
                                            <td><input type="text" name="os_cyl" class="form-control form-control-sm text-center border-0 shadow-none" placeholder="0.00"></td>
                                            <td><input type="text" name="os_axis" class="form-control form-control-sm text-center border-0 shadow-none" placeholder="0"></td>
                                            <td><input type="text" name="os_add" class="form-control form-control-sm text-center border-0 shadow-none" placeholder="0.00"></td>
                                            <td><input type="text" name="os_mpd" class="form-control form-control-sm text-center border-0 shadow-none" placeholder="0.0"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT COLUMN --}}
                <div class="col-lg-5">
                    {{-- 3. Data Pasien --}}
                    <div class="card glass-card">
                        <div class="card-body p-4">
                            <h6 class="section-title"><i class="bi bi-person-badge"></i> Data Pasien</h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">No BPJS (Opsional)</label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="no_bpjs" class="form-control" placeholder="000...">
                                        <button class="btn btn-outline-primary" type="button"><i class="bi bi-search"></i></button>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" name="nama" class="form-control form-control-sm" placeholder="Nama pasien...">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">No. Telepon</label>
                                    <input type="text" name="telp" class="form-control form-control-sm" placeholder="08...">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Asal Resep</label>
                                    <input type="text" name="asal_resep" class="form-control form-control-sm" placeholder="Dokter/Klinik...">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Alamat</label>
                                    <textarea name="alamat" class="form-control form-control-sm" rows="2" placeholder="Alamat lengkap..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 4. Produk & Order specifics --}}
                    <div class="card glass-card">
                        <div class="card-body p-4">
                            <h6 class="section-title"><i class="bi bi-box-seam"></i> Produk & Frame</h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Lensa</label>
                                    <input type="text" name="lensa" class="form-control form-control-sm" placeholder="Jenis lensa...">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Kode Frame</label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="kode_frame" class="form-control">
                                        <button class="btn btn-outline-primary" type="button"><i class="bi bi-search"></i></button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Seri / Merk</label>
                                    <input type="text" name="seri" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Warna</label>
                                    <input type="text" name="warna" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tipe Transaksi</label>
                                    <div class="d-flex gap-3 align-items-center h-100 mt-1">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="typefaktur" id="tunai" value="1" checked>
                                            <label class="form-check-label small" for="tunai">Tunai</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="typefaktur" id="bpjs" value="2">
                                            <label class="form-check-label small" for="bpjs">BPJS</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 5. Financial Summary --}}
                    <div class="card glass-card finance-card border-0 shadow-lg">
                        <div class="card-body p-4">
                            <h6 class="section-title text-white"><i class="bi bi-cash-stack"></i> Rincian Pembayaran</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Harga Jual</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-transparent border-0 text-white">Rp</span>
                                        <input type="text" name="harga_jual" class="form-control text-end" value="0">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Potongan</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-transparent border-0 text-white">Rp</span>
                                        <input type="text" name="potongan" class="form-control text-end" value="0">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">DP / Bayar</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-transparent border-0 text-white">Rp</span>
                                        <input type="text" name="dp" class="form-control text-end" value="0">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Sisa Tagihan</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-transparent border-0 text-white">Rp</span>
                                        <input type="text" name="sisa" class="form-control text-end bg-danger bg-opacity-25" readonly value="0">
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4 border-white opacity-25">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Rencana Ambil</label>
                                    <input type="date" name="tgl_selesai_janji" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Status Ambil</label>
                                    <div class="d-flex gap-3 align-items-center h-100 mt-1">
                                        <div class="form-check">
                                            <input class="form-check-input border-white" type="radio" name="diambil" id="belum" value="2" checked>
                                            <label class="form-check-label small text-white" for="belum">Belum</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input border-white" type="radio" name="diambil" id="sudah" value="1">
                                            <label class="form-check-label small text-white" for="sudah">Sudah</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- STICKY ACTIONS --}}
            <div class="sticky-actions card glass-card p-3 shadow-lg border-primary border-opacity-25 bg-white bg-opacity-75">
                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <button type="button" class="btn btn-action btn-light border shadow-sm"><i class="bi bi-chevron-bar-left"></i> Awal</button>
                    <button type="button" class="btn btn-action btn-light border shadow-sm"><i class="bi bi-chevron-left"></i></button>
                    <button type="button" class="btn btn-action btn-light border shadow-sm"><i class="bi bi-chevron-right"></i></button>
                    <button type="button" class="btn btn-action btn-light border shadow-sm"><i class="bi bi-chevron-bar-right"></i> Akhir</button>
                    
                    <div class="vr mx-2 text-muted opacity-25"></div>
                    
                    <button type="button" class="btn btn-action btn-secondary shadow-sm"><i class="bi bi-search"></i> Cari</button>
                    <button type="button" class="btn btn-action btn-info text-white shadow-sm"><i class="bi bi-printer"></i> Struk</button>
                    <button type="submit" class="btn btn-action btn-primary shadow-sm"><i class="bi bi-plus-lg"></i> Simpan</button>
                    <button type="button" class="btn btn-action btn-warning text-white shadow-sm"><i class="bi bi-pencil-square"></i> Edit</button>
                    <button type="button" class="btn btn-action btn-danger shadow-sm"><i class="bi bi-trash"></i> Hapus</button>
                    
                    <a href="{{ route('transactions.index') }}" class="btn btn-action btn-dark shadow-sm"><i class="bi bi-box-arrow-right"></i> Keluar</a>
                </div>
                
                {{-- Secondary Prints --}}
                <div class="mt-3 d-flex flex-wrap justify-content-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 opacity-75 small">Cetak Bon (3 Rangkap)</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 opacity-75 small">Cetak Bon Fasetan</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 opacity-75 small">Cetak Bon (1 Rangkap)</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Placeholder for JS logic
    // Add number formatting and dynamic "Sisa" calculation here
    document.querySelectorAll('input[name="harga_jual"], input[name="dp"], input[name="potongan"]').forEach(el => {
        el.addEventListener('input', function() {
            let harga = parseInt(document.querySelector('input[name="harga_jual"]').value) || 0;
            let dp = parseInt(document.querySelector('input[name="dp"]').value) || 0;
            let potongan = parseInt(document.querySelector('input[name="potongan"]').value) || 0;
            document.querySelector('input[name="sisa"]').value = Math.max(0, harga - dp - potongan);
        });
    });
</script>
@endpush
