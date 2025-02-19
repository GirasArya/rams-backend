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
        Schema::table('record_pemeliharaan', function (Blueprint $table) {
            $table->bigInteger('biaya_pemeliharaan')->change();
            $table->bigInteger('biaya_impor')->change();
            $table->bigInteger('total_biaya')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('record_pemeliharaan', function (Blueprint $table) {
            $table->integer('biaya_pemeliharaan')->change();
            $table->integer('biaya_impor')->change();
            $table->integer('total_biaya')->change();
        });
    }
};
