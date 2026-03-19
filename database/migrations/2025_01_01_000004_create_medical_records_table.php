<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('tanggal_kunjungan');
            $table->string('keluhan')->nullable();

            // Mata Kanan (OD = Oculus Dexter)
            $table->decimal('od_sph', 5, 2)->nullable()->comment('Spheris kanan');
            $table->decimal('od_cyl', 5, 2)->nullable()->comment('Silinder kanan');
            $table->smallInteger('od_axis')->nullable()->comment('Axis kanan 0-180');
            $table->decimal('od_add', 5, 2)->nullable()->comment('Addisi kanan');
            $table->decimal('od_pd', 5, 2)->nullable()->comment('Pupil Distance kanan');
            $table->decimal('od_vis', 5, 2)->nullable()->comment('Visus kanan');

            // Mata Kiri (OS = Oculus Sinister)
            $table->decimal('os_sph', 5, 2)->nullable()->comment('Spheris kiri');
            $table->decimal('os_cyl', 5, 2)->nullable()->comment('Silinder kiri');
            $table->smallInteger('os_axis')->nullable()->comment('Axis kiri 0-180');
            $table->decimal('os_add', 5, 2)->nullable()->comment('Addisi kiri');
            $table->decimal('os_pd', 5, 2)->nullable()->comment('Pupil Distance kiri');
            $table->decimal('os_vis', 5, 2)->nullable()->comment('Visus kiri');

            // PD total
            $table->decimal('pd_total', 5, 2)->nullable()->comment('PD Binokular');

            $table->string('jenis_lensa')->nullable();
            $table->string('rekomendasi_frame')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};
