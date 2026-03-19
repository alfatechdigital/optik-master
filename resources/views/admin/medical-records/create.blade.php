@extends('layouts.admin')
@section('title', 'Rekam Medis Baru')
@section('page-title', 'Input Rekam Medis')

@section('content')
<div class="row justify-content-center">
<div class="col-xl-10">
<form method="POST" action="{{ route('medical-records.store') }}">
@csrf

{{-- Pilih Pasien --}}
<div class="card mb-3">
    <div class="card-header p-3"><i class="bi bi-person text-primary me-2"></i>Data Pasien & Kunjungan</div>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-5">
                <label class="form-label fw-semibold">Pasien <span class="text-danger">*</span></label>
                <select name="patient_id" class="form-select @error('patient_id') is-invalid @enderror" required>
                    <option value="">-- Pilih Pasien --</option>
                    @foreach($patients as $p)
                    <option value="{{ $p->id }}"
                        {{ old('patient_id', $selectedPatient?->id) == $p->id ? 'selected' : '' }}>
                        {{ $p->no_rm }} — {{ $p->nama }}
                    </option>
                    @endforeach
                </select>
                @error('patient_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Dokter / Pemeriksa <span class="text-danger">*</span></label>
                <select name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                    <option value="">-- Pilih Dokter --</option>
                    @foreach($dokters as $d)
                    <option value="{{ $d->id }}" {{ old('user_id', auth()->id()) == $d->id ? 'selected' : '' }}>
                        {{ $d->name }}
                    </option>
                    @endforeach
                </select>
                @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Tanggal Kunjungan <span class="text-danger">*</span></label>
                <input type="date" name="tanggal_kunjungan" class="form-control @error('tanggal_kunjungan') is-invalid @enderror"
                       value="{{ old('tanggal_kunjungan', today()->format('Y-m-d')) }}" required>
                @error('tanggal_kunjungan')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Keluhan Utama</label>
                <input type="text" name="keluhan" class="form-control" value="{{ old('keluhan') }}"
                       placeholder="Penglihatan buram, mata lelah, sakit kepala...">
            </div>
        </div>
    </div>
</div>

{{-- Resep Kacamata --}}
<div class="card mb-3">
    <div class="card-header p-3">
        <i class="bi bi-eyeglasses text-primary me-2"></i>Resep Kacamata
        <small class="text-muted ms-2">SPH = Spheris | CYL = Silinder | AXIS (0–180°) | ADD = Addisi | PD = Pupil Distance</small>
    </div>
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light text-center">
                    <tr>
                        <th style="width:120px">Mata</th>
                        <th>SPH</th>
                        <th>CYL</th>
                        <th>AXIS</th>
                        <th>ADD</th>
                        <th>PD</th>
                        <th>Visus</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center">
                            <span class="badge bg-danger fs-6">OD</span>
                            <div class="text-muted" style="font-size:.72rem">Kanan</div>
                        </td>
                        <td><input type="number" name="od_sph" class="form-control text-center" step="0.25" min="-30" max="30" value="{{ old('od_sph') }}" placeholder="0.00"></td>
                        <td><input type="number" name="od_cyl" class="form-control text-center" step="0.25" min="-10" max="10" value="{{ old('od_cyl') }}" placeholder="0.00"></td>
                        <td><input type="number" name="od_axis" class="form-control text-center" min="0" max="180" value="{{ old('od_axis') }}" placeholder="0"></td>
                        <td><input type="number" name="od_add" class="form-control text-center" step="0.25" min="0" max="5" value="{{ old('od_add') }}" placeholder="0.00"></td>
                        <td><input type="number" name="od_pd" class="form-control text-center" step="0.5" min="20" max="40" value="{{ old('od_pd') }}" placeholder="0.0"></td>
                        <td><input type="number" name="od_vis" class="form-control text-center" step="0.1" min="0" max="2" value="{{ old('od_vis') }}" placeholder="1.0"></td>
                    </tr>
                    <tr>
                        <td class="text-center">
                            <span class="badge bg-info fs-6">OS</span>
                            <div class="text-muted" style="font-size:.72rem">Kiri</div>
                        </td>
                        <td><input type="number" name="os_sph" class="form-control text-center" step="0.25" min="-30" max="30" value="{{ old('os_sph') }}" placeholder="0.00"></td>
                        <td><input type="number" name="os_cyl" class="form-control text-center" step="0.25" min="-10" max="10" value="{{ old('os_cyl') }}" placeholder="0.00"></td>
                        <td><input type="number" name="os_axis" class="form-control text-center" min="0" max="180" value="{{ old('os_axis') }}" placeholder="0"></td>
                        <td><input type="number" name="os_add" class="form-control text-center" step="0.25" min="0" max="5" value="{{ old('os_add') }}" placeholder="0.00"></td>
                        <td><input type="number" name="os_pd" class="form-control text-center" step="0.5" min="20" max="40" value="{{ old('os_pd') }}" placeholder="0.0"></td>
                        <td><input type="number" name="os_vis" class="form-control text-center" step="0.1" min="0" max="2" value="{{ old('os_vis') }}" placeholder="1.0"></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="row g-3 mt-1">
            <div class="col-md-3">
                <label class="form-label fw-semibold">PD Total / Binokular</label>
                <input type="number" name="pd_total" class="form-control" step="0.5" min="40" max="80"
                       value="{{ old('pd_total') }}" placeholder="60.0">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Jenis Lensa</label>
                <select name="jenis_lensa" class="form-select">
                    <option value="">-- Pilih --</option>
                    <option value="Single Vision" {{ old('jenis_lensa') == 'Single Vision' ? 'selected':'' }}>Single Vision</option>
                    <option value="Bifocal" {{ old('jenis_lensa') == 'Bifocal' ? 'selected':'' }}>Bifocal</option>
                    <option value="Progresif" {{ old('jenis_lensa') == 'Progresif' ? 'selected':'' }}>Progresif</option>
                    <option value="Photochromic" {{ old('jenis_lensa') == 'Photochromic' ? 'selected':'' }}>Photochromic</option>
                    <option value="Blue Cut" {{ old('jenis_lensa') == 'Blue Cut' ? 'selected':'' }}>Blue Cut</option>
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label fw-semibold">Rekomendasi Frame</label>
                <input type="text" name="rekomendasi_frame" class="form-control"
                       value="{{ old('rekomendasi_frame') }}" placeholder="Full rim, semi rim, rimless...">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Catatan Dokter</label>
                <textarea name="catatan" class="form-control" rows="3"
                          placeholder="Catatan tambahan, anjuran pemakaian, kontrol ulang...">{{ old('catatan') }}</textarea>
            </div>
        </div>
    </div>
</div>

<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary px-4">
        <i class="bi bi-check-lg me-1"></i>Simpan Rekam Medis
    </button>
    <a href="{{ route('medical-records.index') }}" class="btn btn-outline-secondary">Batal</a>
</div>

</form>
</div>
</div>
@endsection
