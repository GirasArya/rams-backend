<?php 

namespace App\Models;

use App\Models\RecordPemeliharaan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AktivitasPemeliharaan extends Model
{
    use HasFactory;

    protected $table = 'record_aktivitas_pemeliharaan'; 
    protected $fillable = [
        'id_record_pemeliharaan',
        'jenis_pemeliharaan',
        'nama_kegiatan',
        'frekuensi_kegiatan_per_tahun',
        'jumlah_tenaga_kerja',
        'anggaran_kegiatan_per_meter'
    ];

    public function recordPemeliharaan()
    {
        return $this->belongsTo(RecordPemeliharaan::class, 'id_record_pemeliharaan');
    }
}
