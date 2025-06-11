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
        Schema::table('spatial_patok_lj_point', function (Blueprint $table) {
            $table->unsignedBigInteger('record_aset_id')->nullable();
            $table->foreign('record_aset_id')->references('id')->on('record_aset');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spatial_patok_lj_point', function (Blueprint $table) {
            $table->dropForeign('spatial_patok_hm_point_record_aset_id_foreign');
            $table->dropColumn('record_aset_id');
        });
    }
};
