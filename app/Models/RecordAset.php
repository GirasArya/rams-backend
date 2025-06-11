<?php

namespace App\Models;

use App\Models\Spatial\ManholePoint;
use App\Models\Spatial\PatokHMPoint;
use App\Models\Spatial\PatokKMPoint;
use App\Models\Spatial\PatokLJPoint;
use App\Models\Spatial\PatokRMJPoint;
use App\Models\Spatial\PatokROWPoint;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecordAset extends Model
{
    use HasFactory;

    protected $table = 'record_aset';
    protected $fillable = [
        'id_ruas',
        'jenis_aset',
        'tanggal_pemasangan',
    ];
    public function ruasJalan()
    {
        return $this->belongsTo(JalanTol::class, 'id_ruas');
    }

    public function recordPatokKmPoint()
    {
        return $this->hasMany(PatokKMPoint::class, 'record_aset_id');
    }

    public function recordPatokHmPoint()
    {
        return $this->hasMany(PatokHMPoint::class, 'record_aset_id');
    }

    public function recordPatokLjPoint()
    {
        return $this->hasMany(PatokLJPoint::class, 'record_aset_id');
    }

    public function recordPatokRowPoint()
    {
        return $this->hasMany(PatokROWPoint::class, 'record_aset_id');
    }

    public function recordPatokRmjPoint()
    {
        return $this->hasMany(PatokRMJPoint::class, 'record_aset_id');
    }
    public function recordManholePoint()
    {
        return $this->hasMany(ManholePoint::class, 'record_aset_id');
    }
}
