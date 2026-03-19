<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Exports\PatientExport;
use App\Imports\PatientImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $query = Patient::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('no_rm', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        $patients = $query->latest()->paginate(15)->withQueryString();

        return view('admin.patients.index', compact('patients'));
    }

    public function create()
    {
        return view('admin.patients.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'             => 'required|string|max:100',
            'tanggal_lahir'    => 'nullable|date|before:today',
            'jenis_kelamin'    => 'nullable|in:L,P',
            'no_hp'            => 'nullable|string|max:20',
            'email'            => 'nullable|email|max:100',
            'alamat'           => 'nullable|string',
            'riwayat_penyakit' => 'nullable|string',
        ]);

        $validated['no_rm'] = Patient::generateNoRM();

        Patient::create($validated);

        return redirect()->route('patients.index')
            ->with('success', "Pasien {$validated['nama']} berhasil ditambahkan dengan No. RM {$validated['no_rm']}.");
    }

    public function show(Patient $patient)
    {
        $patient->load([
            'medicalRecords.dokter',
            'transactions.items.product',
            'transactions.kasir',
        ]);

        return view('admin.patients.show', compact('patient'));
    }

    public function edit(Patient $patient)
    {
        return view('admin.patients.edit', compact('patient'));
    }

    public function update(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'nama'             => 'required|string|max:100',
            'tanggal_lahir'    => 'nullable|date|before:today',
            'jenis_kelamin'    => 'nullable|in:L,P',
            'no_hp'            => 'nullable|string|max:20',
            'email'            => 'nullable|email|max:100',
            'alamat'           => 'nullable|string',
            'riwayat_penyakit' => 'nullable|string',
        ]);

        $patient->update($validated);

        return redirect()->route('patients.index')
            ->with('success', 'Data pasien berhasil diperbarui.');
    }

    public function destroy(Patient $patient)
    {
        $patient->delete();

        return redirect()->route('patients.index')
            ->with('success', 'Data pasien berhasil dihapus.');
    }

    public function export()
    {
        return Excel::download(new PatientExport, 'patients_' . now()->format('Y-m-d_H-i-s') . '.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        $file = $request->file('file');

        $headerError = $this->validateImportHeaders($file, PatientImport::expectedHeadings());
        if ($headerError) {
            return redirect()->route('patients.index')
                ->with('error', $headerError);
        }

        $import = new PatientImport;

        try {
            DB::beginTransaction();

            Excel::import($import, $file);

            $errors = $import->getErrors();
            if (!empty($errors)) {
                DB::rollBack();

                $message = 'Import gagal: data tidak valid pada beberapa baris. ';
                $message .= implode(' | ', array_slice($errors, 0, 5));
                if (count($errors) > 5) {
                    $message .= ' (+' . (count($errors) - 5) . ' lainnya)';
                }

                return redirect()->route('patients.index')
                    ->with('error', $message);
            }

            DB::commit();

            $successCount = $import->getSuccessCount();
            $message = "Import selesai. {$successCount} data berhasil diimpor.";

            return redirect()->route('patients.index')
                ->with('success', $message);
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('patients.index')
                ->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    private function validateImportHeaders($file, array $expectedHeaders): ?string
    {
        $rows = (new HeadingRowImport)->toArray($file);
        $headers = $rows[0][0] ?? [];

        if (!is_array($headers) || empty($headers)) {
            return 'Format file tidak valid: tidak dapat membaca baris header.';
        }

        $normalize = fn ($value) => Str::of($value)
            ->trim()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->replaceMatches('/_+/', '_')
            ->trim('_')
            ->__toString();

        $normalized = array_map($normalize, $headers);
        $expectedNormalized = array_map($normalize, $expectedHeaders);

        $missing = array_diff($expectedNormalized, $normalized);
        $extra = array_diff($normalized, $expectedNormalized);

        if (!empty($missing) || !empty($extra)) {
            $msgParts = [];
            if (!empty($missing)) {
                $msgParts[] = 'Kolom hilang: ' . implode(', ', $missing);
            }
            if (!empty($extra)) {
                $msgParts[] = 'Kolom tidak diharapkan: ' . implode(', ', $extra);
            }
            return 'Template file tidak sesuai. ' . implode(' ', $msgParts) . ' Pastikan menggunakan template import yang benar.';
        }

        return null;
    }

    // AJAX — search pasien untuk POS
    public function search(Request $request)
    {
        $patients = Patient::where('nama', 'like', "%{$request->q}%")
            ->orWhere('no_rm', 'like', "%{$request->q}%")
            ->limit(10)
            ->get(['id', 'no_rm', 'nama', 'no_hp']);

        return response()->json($patients);
    }
}
