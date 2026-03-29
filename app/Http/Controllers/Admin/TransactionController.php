<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Transaction, TransactionItem, Patient, Product, MedicalRecord};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['patient', 'kasir']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('no_transaksi', 'like', "%{$search}%")
                    ->orWhereHas('patient', fn($p) => $p->where('nama', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $transactions = $query->latest()->paginate(15)->withQueryString();
        return view('admin.transactions.index', compact('transactions'));
    }

    public function create()
    {
        $products = Product::where('is_active', true)
            ->where('stok', '>', 0)
            ->with('category')
            ->get();
        $patients = Patient::orderBy('nama')->get(['id', 'no_rm', 'nama', 'no_bpjs']);
        $medRecs = collect();

        return view('admin.transactions.create', compact('products', 'patients', 'medRecs'));
    }

    public function store(Request $request)
    {
        // Normalize string to numeric input
        $request->merge([
            'bayar' => (int) str_replace('.', '', $request->bayar),
            'diskon_nominal' => (int) str_replace('.', '', $request->diskon_nominal),
            'potongan_bpjs' => (int) str_replace('.', '', $request->potongan_bpjs),
        ]);

        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.harga_satuan' => 'required|numeric|min:0',
            'metode_bayar' => 'required|in:tunai,transfer,qris,debit,kredit',
            'bayar' => 'required|numeric|min:0',
            'diskon_persen' => 'nullable|numeric|min:0|max:100',
            'diskon_nominal' => 'nullable|numeric|min:0',
            'potongan_bpjs' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $totalHarga = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                if ($product->stok < $item['qty']) {
                    throw new \Exception("Stok produk '{$product->nama}' tidak mencukupi. Stok saat ini: {$product->stok}");
                }

                $subtotal = $item['harga_satuan'] * $item['qty'];
                $totalHarga += $subtotal;

                $itemsData[] = [
                    'product_id' => $product->id,
                    'nama_produk' => $product->nama,
                    'qty' => $item['qty'],
                    'harga_satuan' => $item['harga_satuan'],
                    'diskon' => $item['diskon'] ?? 0,
                    'subtotal' => $subtotal - ($item['diskon'] ?? 0),
                ];

                // Kurangi stok
                $product->decrement('stok', $item['qty']);
            }

            // Hitung diskon
            $diskonPersen = $request->diskon_persen ?? 0;
            $diskonNominal = $request->diskon_nominal ?? 0;
            if ($diskonPersen > 0) {
                $diskonNominal = round($totalHarga * ($diskonPersen / 100));
            }

            $potonganBpjs = $request->potongan_bpjs ?? 0;
            $totalBayar = $totalHarga - $diskonNominal - $potonganBpjs;
            $kembalian = $request->bayar - $totalBayar;

            // Double Protection
            if ($request->bayar < $totalBayar) {
                throw new \Exception('Jumlah bayar kurang dari total yang harus dibayar.');
            }

            $transaction = Transaction::create([
                'no_transaksi' => Transaction::generateNomor(),
                'patient_id' => $request->patient_id ?: null,
                'user_id' => auth()->id(),
                'medical_record_id' => $request->medical_record_id ?: null,
                'total_harga' => $totalHarga,
                'diskon_persen' => $diskonPersen,
                'diskon_nominal' => $diskonNominal,
                'potongan_bpjs' => $potonganBpjs,
                'total_bayar' => $totalBayar,
                'bayar' => $request->bayar,
                'kembalian' => max(0, $kembalian),
                'metode_bayar' => $request->metode_bayar,
                'status' => 'lunas',
                'catatan' => $request->catatan,
            ]);

            $transaction->items()->createMany($itemsData);
        });

        return redirect()->route('transactions.index')
            ->with('success', 'Transaksi berhasil disimpan.');
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['patient', 'kasir', 'medicalRecord', 'items.product']);
        return view('admin.transactions.show', compact('transaction'));
    }

    public function cancel(Transaction $transaction)
    {
        if ($transaction->status === 'batal') {
            return back()->with('error', 'Transaksi sudah dibatalkan.');
        }

        DB::transaction(function () use ($transaction) {
            // Kembalikan stok
            foreach ($transaction->items as $item) {
                if ($item->product) {
                    $item->product->increment('stok', $item->qty);
                }
            }
            $transaction->update(['status' => 'batal']);
        });

        return redirect()->route('transactions.index')
            ->with('success', 'Transaksi berhasil dibatalkan dan stok telah dikembalikan.');
    }

    // AJAX — cari produk di POS
    public function searchProduct(Request $request)
    {
        $products = Product::where('is_active', true)
            ->where('stok', '>', 0)
            ->where(function ($q) use ($request) {
                $q->where('nama', 'like', "%{$request->q}%")
                    ->orWhere('kode_produk', 'like', "%{$request->q}%")
                    ->orWhere('merek', 'like', "%{$request->q}%");
            })
            ->with('category')
            ->limit(10)
            ->get(['id', 'kode_produk', 'nama', 'merek', 'harga_jual', 'stok', 'category_id']);

        return response()->json($products);
    }

    // AJAX — ambil rekam medis berdasarkan pasien
    public function getMedicalRecords(Request $request)
    {
        if (!$request->patient_id) {
            return response()->json([]);
        }

        $records = MedicalRecord::where('patient_id', $request->patient_id)
            ->latest()
            ->take(10)
            ->get(['id', 'tanggal_kunjungan', 'od_sph', 'os_sph'])
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'tanggal_kunjungan' => $r->tanggal_kunjungan->format('d M Y'),
                    'od_sph' => $r->od_sph ?? '-',
                    'os_sph' => $r->os_sph ?? '-',
                ];
            });

        return response()->json($records);
    }

    // --- ADVANCED POS API ---

    public function posNav(Request $request)
    {
        $dir = $request->dir; // awal, sebelum, sesudah, akhir
        $currentId = $request->current_id;

        $query = Transaction::with(['patient', 'items.product', 'medicalRecord']);

        if ($dir == 'awal') {
            $trx = $query->orderBy('id', 'asc')->first();
        } elseif ($dir == 'akhir') {
            $trx = $query->orderBy('id', 'desc')->first();
        } elseif ($dir == 'sebelum' && $currentId) {
            $trx = $query->where('id', '<', $currentId)->orderBy('id', 'desc')->first();
        } elseif ($dir == 'sesudah' && $currentId) {
            $trx = $query->where('id', '>', $currentId)->orderBy('id', 'asc')->first();
        } else {
            $trx = null;
        }

        return response()->json($trx ?: null);
    }

    public function posSearch(Request $request)
    {
        $q = $request->q;
        $query = Transaction::with('patient')->latest()->take(20);

        if ($q) {
            $query->where('no_transaksi', 'like', "%{$q}%")
                ->orWhereHas('patient', function ($p) use ($q) {
                    $p->where('nama', 'like', "%{$q}%")
                        ->orWhere('no_bpjs', 'like', "%{$q}%");
                });
        }

        $transactions = $query->get()->map(function ($t) {
            return [
                'id' => $t->id,
                'no_transaksi' => $t->no_transaksi,
                'tanggal' => $t->created_at->format('d/m/Y'),
                'pasien' => $t->patient ? $t->patient->nama : ($t->nama_pasien ?? '-'),
                'total' => 'Rp ' . number_format($t->total_bayar, 0, ',', '.'),
            ];
        });

        return response()->json($transactions);
    }

    public function posSave(Request $request)
    {
        // Remove dots from currency
        $request->merge([
            'harga_jual' => (int) str_replace('.', '', $request->harga_jual),
            'dp' => (int) str_replace('.', '', $request->dp),
            'potongan' => (int) str_replace('.', '', $request->potongan),
        ]);

        try {
            DB::beginTransaction();

            // Handle Patient
            $patient = null;
            if ($request->patient_id) {
                $patient = Patient::find($request->patient_id);
                if ($patient) {
                    $patient->update([
                        'nama' => $request->nama,
                        'no_bpjs' => $request->no_bpjs,
                        'no_hp' => $request->telp,
                        'alamat' => $request->alamat,
                    ]);
                }
            } elseif ($request->nama) {
                // Try to find if exists
                $patient = Patient::where('nama', $request->nama)->where('no_hp', $request->telp)->first();
                if (!$patient) {
                    $patient = Patient::create([
                        'no_rm' => Patient::generateNoRM(),
                        'nama' => $request->nama,
                        'no_bpjs' => $request->no_bpjs,
                        'no_hp' => $request->telp,
                        'alamat' => $request->alamat,
                    ]);
                }
            }

            // Product input array handling (first item to transaction fields)
            $kodeFrame = is_array($request->kode_frame) ? ($request->kode_frame[0] ?? null) : $request->kode_frame;
            $namaProduk = is_array($request->nama_produk) ? ($request->nama_produk[0] ?? null) : $request->nama_produk;
            $seri = is_array($request->seri) ? ($request->seri[0] ?? null) : $request->seri;
            $warna = is_array($request->warna) ? ($request->warna[0] ?? null) : $request->warna;
            $keterangan = is_array($request->keterangan) ? ($request->keterangan[0] ?? null) : $request->keterangan;

            // Transaction Data array
            $data = [
                'patient_id' => $patient ? $patient->id : null,
                'user_id' => auth()->id(),
                'status' => 'lunas',
                'metode_bayar' => 'tunai',

                // Fields
                'tgl_order' => $request->tgl_order ?? date('Y-m-d'),
                'no_legalisasi' => $request->no_legalisasi,
                'tgl_legalisasi' => $request->tgl_legalisasi,
                'tgl_faset' => $request->tgl_faset,
                'lab' => $request->lab,
                'tempat_faset' => $request->tempat_faset,
                'tgl_datang_faset' => $request->tgl_datang_faset,
                'tgl_selesai_faset' => $request->tgl_selesai_faset,
                'tgl_selesai_janji' => $request->tgl_selesai_janji,
                'catatan' => $request->catatan,

                'od_sph' => $request->od_sph,
                'od_cyl' => $request->od_cyl,
                'od_axis' => $request->od_axis,
                'od_add' => $request->od_add,
                'od_mpd' => $request->od_mpd,
                'os_sph' => $request->os_sph,
                'os_cyl' => $request->os_cyl,
                'os_axis' => $request->os_axis,
                'os_add' => $request->os_add,
                'os_mpd' => $request->os_mpd,

                'no_bpjs' => $request->no_bpjs,
                'nama_pasien' => $request->nama,
                'alamat_pasien' => $request->alamat,
                'telp_pasien' => $request->telp,
                'asal_resep' => $request->asal_resep,

                'lensa' => $request->lensa,
                'kode_frame' => $kodeFrame,
                'nama_produk' => $namaProduk,
                'keterangan_frame' => $keterangan,
                'seri' => $seri,
                'warna' => $warna,
                'typefaktur' => $request->typefaktur,
                'diambil' => $request->diambil,

                // Money
                'harga_jual' => $request->harga_jual,
                'dp' => $request->dp,
                'potongan' => $request->potongan,
                'sisa' => max(0, $request->harga_jual - $request->dp - $request->potongan),

                // Legacy fallback overrides mapping if empty (so show views don't break entirely)
                'total_harga' => $request->harga_jual ?? 0,
                'total_bayar' => max(0, ($request->harga_jual ?? 0) - ($request->potongan ?? 0)),
                'bayar' => $request->dp ?? 0,
                'diskon_nominal' => $request->potongan ?? 0,
            ];

            if ($request->id) {
                // Update
                $trx = Transaction::findOrFail($request->id);
                $trx->update($data);
                $msg = 'Transaksi berhasil diupdate';
            } else {
                // Create
                $data['no_transaksi'] = Transaction::generateNomor();
                $trx = Transaction::create($data);
                $msg = 'Transaksi berhasil disimpan';
            }

            // Product / Items updating (from cart_data) - fallback to kode_frame list
            $trx->items()->delete();

            $cartData = json_decode($request->cart_data, true) ?: [];
            if (is_array($cartData) && count($cartData) > 0) {
                foreach ($cartData as $item) {
                    if (empty($item['kode']))
                        continue;
                    $product = Product::where('kode_produk', $item['kode'])->first();
                    $trx->items()->create([
                        'product_id' => $product ? $product->id : null,
                        'nama_produk' => $item['nama'] ?? ($product->nama ?? 'Unknown'),
                        'qty' => max(1, intval($item['qty'] ?? 1)),
                        'harga_satuan' => floatval($item['harga'] ?? 0),
                        'subtotal' => floatval($item['harga'] ?? 0) * max(1, intval($item['qty'] ?? 1))
                    ]);
                }
            } else {
                $kodeFrames = is_array($request->kode_frame) ? $request->kode_frame : [$request->kode_frame];
                foreach ($kodeFrames as $idx => $kode) {
                    if (!$kode)
                        continue;
                    $product = Product::where('kode_produk', $kode)->first();
                    $trx->items()->create([
                        'product_id' => $product->id,
                        'nama_produk' => $product->nama,
                        'qty' => 1,
                        'harga_satuan' => $request->harga_jual,
                        'subtotal' => $request->harga_jual
                    ]);
                }
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => $msg, 'data' => $trx]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function posDelete($id)
    {
        try {
            $trx = Transaction::findOrFail($id);
            if ($trx->items()->count() > 0) {
                $trx->items()->delete();
            }
            $trx->delete();
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function patientAutocomplete(Request $request)
    {
        $q = $request->q;
        $patients = Patient::where('no_bpjs', 'like', "%{$q}%")
            ->orWhere('nama', 'like', "%{$q}%")
            ->orWhere('no_hp', 'like', "%{$q}%")
            ->take(10)->get();
        return response()->json($patients);
    }

    public function frameAutocomplete(Request $request)
    {
        $q = $request->q;
        $products = Product::where('kode_produk', 'like', "%{$q}%")
            ->orWhere('nama', 'like', "%{$q}%")
            ->orWhere('merek', 'like', "%{$q}%")
            ->take(10)->get();
        return response()->json($products);
    }
}
