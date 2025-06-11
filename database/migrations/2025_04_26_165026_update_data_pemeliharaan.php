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
        //
        Schema::table('record_pemeliharaan', function (Blueprint $table) {
            $table->dropColumn('jenis_pemeliharaan');
            $table->dropColumn('biaya_pemeliharaan');
            $table->dropColumn('biaya_impor');
            $table->dropColumn('keterangan_pemeliharaan');
            $table->dropColumn('bagian_jalan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('record_pemeliharaan', function (Blueprint $table) {
            $table->string('jenis_pemeliharaan');
            $table->decimal('biaya_pemeliharaan', 30, 0);
            $table->decimal('biaya_impor', 30, 0);
            $table->text('keterangan_pemeliharaan');
            $table->string('bagian_jalan');
        });
    }
};
