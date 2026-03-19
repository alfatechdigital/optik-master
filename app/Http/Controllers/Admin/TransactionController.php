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
        $patients = Patient::orderBy('nama')->get(['id', 'no_rm', 'nama']);
        $medRecs  = collect();

        return view('admin.transactions.create', compact('products', 'patients', 'medRecs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'items'                  => 'required|array|min:1',
            'items.*.product_id'     => 'required|exists:products,id',
            'items.*.qty'            => 'required|integer|min:1',
            'items.*.harga_satuan'   => 'required|numeric|min:0',
            'metode_bayar'           => 'required|in:tunai,transfer,qris,debit,kredit',
            'bayar'                  => 'required|numeric|min:0',
            'diskon_persen'          => 'nullable|numeric|min:0|max:100',
            'diskon_nominal'         => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $totalHarga = 0;
            $itemsData  = [];

            foreach ($request->items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                if ($product->stok < $item['qty']) {
                    throw new \Exception("Stok produk '{$product->nama}' tidak mencukupi. Stok saat ini: {$product->stok}");
                }

                $subtotal    = $item['harga_satuan'] * $item['qty'];
                $totalHarga += $subtotal;

                $itemsData[] = [
                    'product_id'   => $product->id,
                    'nama_produk'  => $product->nama,
                    'qty'          => $item['qty'],
                    'harga_satuan' => $item['harga_satuan'],
                    'diskon'       => $item['diskon'] ?? 0,
                    'subtotal'     => $subtotal - ($item['diskon'] ?? 0),
                ];

                // Kurangi stok
                $product->decrement('stok', $item['qty']);
            }

            // Hitung diskon
            $diskonPersen  = $request->diskon_persen  ?? 0;
            $diskonNominal = $request->diskon_nominal ?? 0;
            if ($diskonPersen > 0) {
                $diskonNominal = round($totalHarga * ($diskonPersen / 100));
            }

            $totalBayar = $totalHarga - $diskonNominal;
            $kembalian  = $request->bayar - $totalBayar;

            $transaction = Transaction::create([
                'no_transaksi'      => Transaction::generateNomor(),
                'patient_id'        => $request->patient_id ?: null,
                'user_id'           => auth()->id(),
                'medical_record_id' => $request->medical_record_id ?: null,
                'total_harga'       => $totalHarga,
                'diskon_persen'     => $diskonPersen,
                'diskon_nominal'    => $diskonNominal,
                'total_bayar'       => $totalBayar,
                'bayar'             => $request->bayar,
                'kembalian'         => max(0, $kembalian),
                'metode_bayar'      => $request->metode_bayar,
                'status'            => 'lunas',
                'catatan'           => $request->catatan,
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
                    'id'                => $r->id,
                    'tanggal_kunjungan' => $r->tanggal_kunjungan->format('d M Y'),
                    'od_sph'            => $r->od_sph ?? '-',
                    'os_sph'            => $r->os_sph ?? '-',
                ];
            });

        return response()->json($records);
    }
}
