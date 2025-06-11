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
        Schema::table('record_pemeliharaan', function (Blueprint $table) {
            $table->decimal('biaya_pemeliharaan', 30, 2)->change();
            $table->decimal('biaya_impor', 30, 2)->change();
            $table->unsignedBigInteger('id_ruas')->nullable(); 
            $table->foreign('id_ruas')->references('id')->on('jalan_tol')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('record_pemeliharaan', function (Blueprint $table) {
            $table->bigInteger('biaya_pemeliharaan')->change();
            $table->bigInteger('biaya_pemeliharaan')->change(); 
            $table->bigInteger('biaya_impor')->change();
            $table->dropForeign(['id_ruas']); 
            $table->dropColumn('id_ruas');
        });
    }
};
