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
        Schema::table('spatial_manhole_point', function (Blueprint $table) {
            $table->unsignedBigInteger('record_aset_id')->nullable();
            $table->foreign('record_aset_id')->references('id')->on('record_aset');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spatial_manhole_point', function (Blueprint $table) {
            $table->dropColumn('record_aset_id');
            $table->dropForeign('record_aset_id')->references('id')->on('record_aset');
        });
    }
};
