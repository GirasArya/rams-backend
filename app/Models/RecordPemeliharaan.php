<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecordPemeliharaan extends Model
{
    use HasFactory;

    protected $table = 'record_pemeliharaan'; 
    protected $fillable = [ 
        'nama',
        'km_awal',
        'km_akhir',
        'jalur',
        'bagian_jalan',
        'periode_awal',
        'periode_akhir',
        'jenis_pemeliharaan',
        'biaya_pemeliharaan',
        'biaya_impor',
        'keterangan_pemeliharaan',
        'total_biaya',
    ];
}