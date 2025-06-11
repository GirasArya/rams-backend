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
            $table->decimal('biaya_pemeliharaan', 30, 0)->change();
            $table->decimal('biaya_impor', 30, 0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('record_pemeliharaan', function (Blueprint $table) {
            $table->decimal('biaya_pemeliharaan', 30, 2)->change();
            $table->decimal('biaya_impor', 30, 2)->change();
        });
    }
};
