<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('record_aktivitas_pemeliharaan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kegiatan')->nullable();
            $table->string('jenis_pemeliharaan')->nullable();
            $table->integer('frekuensi_kegiatan_per_tahun')->nullable();
            $table->integer('jumlah_tenaga_kerja')->nullable();
            $table->decimal('anggaran_kegiatan_per_meter',20,0)->nullable();
            $table->unsignedBigInteger('id_record_pemeliharaan')->nullable();
            $table->foreign('id_record_pemeliharaan')->references('id')->on('record_pemeliharaan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_aktivitas_pemeliharaan');
    }
};
