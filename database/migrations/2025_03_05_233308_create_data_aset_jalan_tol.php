<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('record_aset', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_ruas')->nullable();
            $table->string('jenis_aset');
            $table->string('titik_km');
            $table->string('masa_hidup');
            $table->string('status');
            $table->date('tanggal_pemasangan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_aset_jalan_tol');
    }
};
