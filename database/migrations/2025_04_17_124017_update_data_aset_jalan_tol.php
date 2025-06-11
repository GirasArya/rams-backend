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
        Schema::table('record_aset', function (Blueprint $table) {
            $table->dropColumn('titik_km');
            $table->dropColumn('masa_hidup');
            $table->dropColumn('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('record_aset', function (Blueprint $table) {
            $table->string('titik_km');
            $table->string('masa_hidup');
            $table->string('status');
        });
    }
};
