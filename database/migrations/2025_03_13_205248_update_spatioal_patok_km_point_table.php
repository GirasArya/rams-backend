<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('spatial_patok_km_point', function (Blueprint $table) {
            $table->unsignedBigInteger('record_id')->nullable();
            $table->foreign('record_id')->references('id')->on('record_aset');
        });
    }


    public function down(): void
    {
        Schema::table('spatial_patok_km_point', function (Blueprint $table) {
            $table->dropForeign('spatial_patok_km_point_record_id_foreign');
            $table->dropColumn('record_id');
        });
    }
};
