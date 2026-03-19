@extends('layouts.admin')
@section('title','Edit Rekam Medis')
@section('page-title','Edit Rekam Medis')

@section('content')
<div class="row justify-content-center">
<div class="col-xl-10">
<form method="POST" action="{{ route('medical-records.update',$medicalRecord) }}">
@csrf @method('PUT')

<div class="card mb-3">
    <div class="card-header p-3">
        <i class="bi bi-pencil text-warning me-2"></i>Edit Rekam Medis — {{ $medicalRecord->patient->nama }}
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-5">
                <label class="form-label fw-semibold">Pasien <span class="text-danger">*</span></label>
                <select name="patient_id" class="form-select" required>
                    @foreach($patients as $p)
                    <option value="{{ $p->id }}"
                        {{ old('patient_id',$medicalRecord->patient_id) == $p->id ? 'selected':'' }}>
                        {{ $p->no_rm }} — {{ $p->nama }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Dokter <span class="text-danger">*</span></label>
                <select name="user_id" class="form-select" required>
                    @foreach($dokters as $d)
                    <option value="{{ $d->id }}"
                        {{ old('user_id',$medicalRecord->user_id) == $d->id ? 'selected':'' }}>
                        {{ $d->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Tanggal Kunjungan <span class="text-danger">*</span></label>
                <input type="date" name="tanggal_kunjungan" class="form-control" required
                       value="{{ old('tanggal_kunjungan',$medicalRecord->tanggal_kunjungan->format('Y-m-d')) }}">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Keluhan</label>
                <input type="text" name="keluhan" class="form-control"
                       value="{{ old('keluhan',$medicalRecord->keluhan) }}">
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header p-3"><i class="bi bi-eyeglasses text-primary me-2"></i>Resep Kacamata</div>
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light text-center">
                    <tr><th>Mata</th><th>SPH</th><th>CYL</th><th>AXIS</th><th>ADD</th><th>PD</th><th>Visus</th></tr>
                </thead>
                <tbody>
                    @foreach([['od','Kanan','danger'],['os','Kiri','info']] as [$eye,$label,$color])
                    <tr>
                        <td class="text-center">
                            <span class="badge bg-{{ $color }} fs-6">{{ strtoupper($eye) }}</span>
                            <div class="text-muted" style="font-size:.72rem">{{ $label }}</div>
                        </td>
                        @foreach(['sph','cyl','axis','add','pd','vis'] as $field)
                        <td>
                            <input type="number" name="{{ $eye }}_{{ $field }}" class="form-control text-center"
                                   step="{{ in_array($field,['axis']) ? 1 : 0.25 }}"
                                   value="{{ old("{$eye}_{$field}", $medicalRecord->{"{$eye}_{$field}"}) }}"
                                   placeholder="{{ strtoupper($field) }}">
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="row g-3 mt-1">
            <div class="col-md-3">
                <label class="form-label fw-semibold">PD Total</label>
                <input type="number" name="pd_total" class="form-control" step="0.5"
                       value="{{ old('pd_total',$medicalRecord->pd_total) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Jenis Lensa</label>
                <select name="jenis_lensa" class="form-select">
                    <option value="">-- Pilih --</option>
                    @foreach(['Single Vision','Bifocal','Progresif','Photochromic','Blue Cut'] as $jl)
                    <option value="{{ $jl }}" {{ old('jenis_lensa',$medicalRecord->jenis_lensa)==$jl?'selected':'' }}>{{ $jl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label fw-semibold">Rekomendasi Frame</label>
                <input type="text" name="rekomendasi_frame" class="form-control"
                       value="{{ old('rekomendasi_frame',$medicalRecord->rekomendasi_frame) }}">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Catatan Dokter</label>
                <textarea name="catatan" class="form-control" rows="3">{{ old('catatan',$medicalRecord->catatan) }}</textarea>
            </div>
        </div>
    </div>
</div>

<div class="d-flex gap-2">
    <button type="submit" class="btn btn-warning px-4">
        <i class="bi bi-check-lg me-1"></i>Update Rekam Medis
    </button>
    <a href="{{ route('medical-records.show',$medicalRecord) }}" class="btn btn-outline-secondary">Batal</a>
</div>
</form>
</div>
</div>
@endsection
