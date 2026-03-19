@extends('layouts.admin')
@section('title', 'Edit Pasien')
@section('page-title', 'Edit Data Pasien')

@section('content')
<div class="row justify-content-center">
<div class="col-md-8">
<div class="card">
    <div class="card-header p-3">
        <i class="bi bi-pencil text-warning me-2"></i>Edit: {{ $patient->nama }}
        <span class="badge bg-secondary ms-2">{{ $patient->no_rm }}</span>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('patients.update', $patient) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                           value="{{ old('nama', $patient->nama) }}" required>
                    @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-select">
                        <option value="">-- Pilih --</option>
                        <option value="L" {{ old('jenis_kelamin', $patient->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('jenis_kelamin', $patient->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" class="form-control @error('tanggal_lahir') is-invalid @enderror"
                           value="{{ old('tanggal_lahir', $patient->tanggal_lahir?->format('Y-m-d')) }}">
                    @error('tanggal_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">No. HP</label>
                    <input type="text" name="no_hp" class="form-control"
                           value="{{ old('no_hp', $patient->no_hp) }}" placeholder="08xx-xxxx-xxxx">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control"
                           value="{{ old('email', $patient->email) }}">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Alamat</label>
                    <textarea name="alamat" class="form-control" rows="3">{{ old('alamat', $patient->alamat) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Riwayat Penyakit</label>
                    <textarea name="riwayat_penyakit" class="form-control" rows="2"
                              placeholder="Diabetes, hipertensi, alergi obat...">{{ old('riwayat_penyakit', $patient->riwayat_penyakit) }}</textarea>
                </div>
            </div>
            <hr class="my-4">
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-warning px-4">
                    <i class="bi bi-check-lg me-1"></i>Update Data Pasien
                </button>
                <a href="{{ route('patients.show', $patient) }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection
