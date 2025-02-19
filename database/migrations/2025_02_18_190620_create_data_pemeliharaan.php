<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('record_pemeliharaan', function (Blueprint $table) {
            $table->id();
            $table->string("nama");
            $table->string("km_awal");
            $table->string("km_akhir");
            $table->string("jalur");
            $table->string("bagian_jalan");
            $table->date("periode_awal");
            $table->date("periode_akhir");
            $table->string("jenis_pemeliharaan");
            $table->integer("biaya_pemeliharaan");
            $table->integer("biaya_impor");
            $table->string("keterangan_pemeliharaan");
            $table->integer("total_biaya");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_pemeliharaan');
    }
};
