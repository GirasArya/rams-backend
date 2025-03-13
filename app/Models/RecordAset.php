<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecordAset extends Model
{
    use HasFactory;

    protected $table = 'record_aset';
    protected $fillable = [
        'id_ruas',
        'jenis_aset',
        'titik_km',
        'masa_hidup',
        'status',
        'tanggal_pemasangan',
    ];
    public function ruasJalan()
    {
        return $this->belongsTo(JalanTol::class, 'id_ruas');
    }
}