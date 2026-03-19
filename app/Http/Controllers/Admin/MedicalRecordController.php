<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{MedicalRecord, Patient, User};
use Illuminate\Http\Request;

class MedicalRecordController extends Controller
{
    public function index(Request $request)
    {
        $query = MedicalRecord::with(['patient', 'dokter']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('patient', fn($q) =>
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('no_rm', 'like', "%{$search}%")
            );
        }

        if ($request->filled('from')) {
            $query->whereDate('tanggal_kunjungan', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('tanggal_kunjungan', '<=', $request->to);
        }

        $records = $query->latest()->paginate(15)->withQueryString();

        return view('admin.medical-records.index', compact('records'));
    }

    public function create(Request $request)
    {
        $patients = Patient::orderBy('nama')->get(['id', 'no_rm', 'nama']);
        $dokters  = User::role(['super_admin', 'admin'])->orderBy('name')->get(['id', 'name']);
        $selectedPatient = $request->patient_id ? Patient::find($request->patient_id) : null;

        return view('admin.medical-records.create', compact('patients', 'dokters', 'selectedPatient'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id'        => 'required|exists:patients,id',
            'user_id'           => 'required|exists:users,id',
            'tanggal_kunjungan' => 'required|date',
            'keluhan'           => 'nullable|string|max:255',
            // OD
            'od_sph'  => 'nullable|numeric|between:-30,30',
            'od_cyl'  => 'nullable|numeric|between:-10,10',
            'od_axis' => 'nullable|integer|between:0,180',
            'od_add'  => 'nullable|numeric|between:0,5',
            'od_pd'   => 'nullable|numeric|between:20,40',
            'od_vis'  => 'nullable|numeric|between:0,2',
            // OS
            'os_sph'  => 'nullable|numeric|between:-30,30',
            'os_cyl'  => 'nullable|numeric|between:-10,10',
            'os_axis' => 'nullable|integer|between:0,180',
            'os_add'  => 'nullable|numeric|between:0,5',
            'os_pd'   => 'nullable|numeric|between:20,40',
            'os_vis'  => 'nullable|numeric|between:0,2',
            // Info lain
            'pd_total'          => 'nullable|numeric|between:40,80',
            'jenis_lensa'       => 'nullable|string|max:100',
            'rekomendasi_frame' => 'nullable|string|max:100',
            'catatan'           => 'nullable|string',
        ]);

        MedicalRecord::create($validated);

        return redirect()->route('medical-records.index')
            ->with('success', 'Rekam medis berhasil disimpan.');
    }

    public function show(MedicalRecord $medicalRecord)
    {
        $medicalRecord->load(['patient', 'dokter', 'transaction.items.product']);
        return view('admin.medical-records.show', compact('medicalRecord'));
    }

    public function edit(MedicalRecord $medicalRecord)
    {
        $patients = Patient::orderBy('nama')->get(['id', 'no_rm', 'nama']);
        $dokters  = User::role(['super_admin', 'admin'])->orderBy('name')->get(['id', 'name']);
        return view('admin.medical-records.edit', compact('medicalRecord', 'patients', 'dokters'));
    }

    public function update(Request $request, MedicalRecord $medicalRecord)
    {
        $validated = $request->validate([
            'patient_id'        => 'required|exists:patients,id',
            'user_id'           => 'required|exists:users,id',
            'tanggal_kunjungan' => 'required|date',
            'keluhan'           => 'nullable|string|max:255',
            'od_sph' => 'nullable|numeric|between:-30,30',
            'od_cyl' => 'nullable|numeric|between:-10,10',
            'od_axis'=> 'nullable|integer|between:0,180',
            'od_add' => 'nullable|numeric|between:0,5',
            'od_pd'  => 'nullable|numeric|between:20,40',
            'os_sph' => 'nullable|numeric|between:-30,30',
            'os_cyl' => 'nullable|numeric|between:-10,10',
            'os_axis'=> 'nullable|integer|between:0,180',
            'os_add' => 'nullable|numeric|between:0,5',
            'os_pd'  => 'nullable|numeric|between:20,40',
            'pd_total'          => 'nullable|numeric',
            'jenis_lensa'       => 'nullable|string|max:100',
            'rekomendasi_frame' => 'nullable|string|max:100',
            'catatan'           => 'nullable|string',
        ]);

        $medicalRecord->update($validated);

        return redirect()->route('medical-records.index')
            ->with('success', 'Rekam medis berhasil diperbarui.');
    }

    public function destroy(MedicalRecord $medicalRecord)
    {
        $medicalRecord->delete();
        return redirect()->route('medical-records.index')
            ->with('success', 'Rekam medis berhasil dihapus.');
    }
}
