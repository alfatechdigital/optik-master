<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'no_transaksi', 'patient_id', 'user_id', 'medical_record_id',
        'total_harga', 'diskon_persen', 'diskon_nominal', 'total_bayar',
        'bayar', 'kembalian', 'metode_bayar', 'status', 'catatan',
    ];

    // Auto-generate nomor transaksi
    public static function generateNomor(): string
    {
        $prefix = 'TRX' . date('Ymd');
        $last   = static::where('no_transaksi', 'like', $prefix . '%')->latest('id')->first();
        $num    = $last ? ((int) substr($last->no_transaksi, -4)) + 1 : 1;
        return $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    // Relasi
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function kasir()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function medicalRecord()
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }
}
