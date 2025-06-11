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
        'id_ruas',
        'km_awal',
        'km_akhir',
        'jalur',
        'indeks_iri',
        'periode_awal',
        'periode_akhir',
        'total_biaya',
    ];

    public function ruasJalan()
    {
        return $this->belongsTo(JalanTol::class, 'id_ruas');
    }

    public function aktivitasKegiatan()
    {
        return $this->hasMany(AktivitasPemeliharaan::class, 'id_record_pemeliharaan');
    }
}

