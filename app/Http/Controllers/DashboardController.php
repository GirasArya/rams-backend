<?php

namespace App\Http\Controllers;

use App\Models\Spatial\ManholePoint;
use App\Models\Spatial\PatokPemanduPoint;
use App\Models\Spatial\PatokRMJPoint;
use App\Models\Spatial\PatokROWPoint;
use App\Models\Spatial\RambuLalulintasPoint;
use App\Models\Spatial\RambuPenunjukarahPoint;
use App\Models\Spatial\ReflektorPoint;
use App\Models\Spatial\RumahKabelPoint;
use App\Models\Spatial\StaTextPoint;
use App\Models\Spatial\TiangListrikPoint;
use App\Models\Spatial\TiangTeleponPoint;
use App\Models\Spatial\VMSPoint;
use Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\JalanTol;
use App\Models\Leger;
use App\Models\Spatial\LHRPolygon;
use App\Models\Spatial\IRIPolygon;
use App\Models\Spatial\PatokHMPoint;
use App\Models\Spatial\PatokKMPoint;
use App\Models\Spatial\PatokLJPoint;
use Illuminate\Support\Facades\Auth as FacadesAuth;

class DashboardController extends Controller
{
    public function getDashboardData()
    {
        $total_ruas = JalanTol::count();
        $jumlah_user = User::count();
        if (FacadesAuth::user()->id == 1) {
            $jumlah_ruas_user = JalanTol::count();
            $jumlah_leger_user = Leger::count();
            $lhr = LHRPolygon::get();
            $iri = IRIPolygon::get();
            $manhole = ManholePoint::get();
            $patokHM = PatokHMPoint::get();
            $patokKM = PatokKMPoint::get();
            $patokLJ = PatokLJPoint::get();
            $patokPemandu = PatokPemanduPoint::get();
            $patokRMJ = PatokRMJPoint::get();
            $patokROW = PatokROWPoint::get();
            $rambuLaluLintas = RambuLalulintasPoint::get();
            $rambuPenunjukArah = RambuPenunjukarahPoint::get();
            // $reflektor = ReflektorPoint::get();
            $rumahKabelPoint = RumahKabelPoint::get();
            $staText = StaTextPoint::get();
            $tiangListrik = TiangListrikPoint::get();
            $tiangTelepon = TiangTeleponPoint::get();
            $vms = VMSPoint::get();
        } else {
            $jumlah_ruas_user = JalanTol::where('user_id', FacadesAuth::user()->id)->count();
            $jumlah_leger_user = Leger::where('user_id', FacadesAuth::user()->id)->count();
            $lhr = LHRPolygon::whereRelation('jalanTol', 'user_id', FacadesAuth::user()->id)->get();
            $iri = IRIPolygon::whereRelation('jalanTol', 'user_id', FacadesAuth::user()->id)->get();
            $manhole = ManholePoint::whereRelation('jalanTol', 'user_id', FacadesAuth::user()->id)->get();
            $patokHM = PatokHMPoint::whereRelation('jalanTol', 'user_id', FacadesAuth::user()->id)->get();
            $patokKM = PatokKMPoint::whereRelation('jalanTol', 'user_id', FacadesAuth::user()->id)->get();
            $patokLJ = PatokLJPoint::whereRelation('jalanTol', 'user_id', FacadesAuth::user()->id)->get();
            $patokPemandu = PatokPemanduPoint::whereRelation('jalanTol', 'user_id', FacadesAuth::user()->id)->get();
            $patokRMJ = PatokRMJPoint::whereRelation('jalanTol', 'user_id', FacadesAuth::user()->id)->get();
            $patokROW = PatokROWPoint::whereRelation('jalanTol', 'user_id', FacadesAuth::user()->id)->get();
            $rambuLaluLintas = RambuLalulintasPoint::whereRelation('jalanTol', 'user_id', FacadesAuth::user()->id)->get();
            $rambuPenunjukArah = RambuPenunjukarahPoint::whereRelation('jalanTol', 'user_id', FacadesAuth::user()->id)->get();
            // $reflektor = ReflektorPoint::whereRelation('jalanTol', 'user_id', FacadesAuth::user()->id)->get();
            $rumahKabelPoint = RumahKabelPoint::whereRelation('jalanTol', 'user_id', FacadesAuth::user()->id)->get();
            $staText = StaTextPoint::whereRelation('jalanTol', 'user_id', FacadesAuth::user()->id)->get();
            $tiangListrik = TiangListrikPoint::whereRelation('jalanTol', 'user_id', FacadesAuth::user()->id)->get();
            $tiangTelepon = TiangTeleponPoint::whereRelation('jalanTol', 'user_id', FacadesAuth::user()->id)->get();
            $vms = VMSPoint::whereRelation('jalanTol', 'user_id', FacadesAuth::user()->id)->get();
        }

        // LHR
        $lhr_gol_i = $lhr->sum('gol_i');
        $lhr_gol_ii = $lhr->sum('gol_ii');
        $lhr_gol_iii = $lhr->sum('gol_iii');
        $lhr_gol_iv = $lhr->sum('gol_iv');
        $lhr_gol_v = $lhr->sum('gol_v');

        // IRI
        $iri_baik = $iri->where('nilai_iri', '<=', 4)->count();
        $iri_sedang = $iri->where('nilai_iri', '>', 4)->where('nilai_iri', '<=', 8)->count();
        $iri_rusak_ringan = $iri->where('nilai_iri', '>', 8)->where('nilai_iri', '<=', 12)->count();
        $iri_rusak_berat = $iri->where('nilai_iri', '>', 12)->count();

        //Asset
        $manholeCount = $manhole->count();
        $patokHMCount = $patokHM->count();
        $patokKMCount = $patokKM->count();
        $patokLJCount = $patokLJ->count();
        $patokPemanduCount = $patokPemandu->count();
        $patokRMJCount = $patokRMJ->count();
        $patokROWCount = $patokROW->count();
        $rambuLaluLintasCount = $rambuLaluLintas->count();
        $rambuPenunjukArahCount = $rambuPenunjukArah->count();
        // $reflektorCount = $reflektor->count();
        $rumahKabelPointCount = $rumahKabelPoint->count();
        $staTextCount = $staText->count();
        $tiangListrikCount = $tiangListrik->count();
        $tiangTeleponCount = $tiangTelepon->count();
        $vmsCount = $vms->count();
        $assetSum = $manholeCount + $patokHMCount + $patokKMCount + $patokLJCount + $patokPemanduCount + $patokRMJCount + $patokROWCount + $rambuLaluLintasCount + $rambuPenunjukArahCount  + $rumahKabelPointCount + $staTextCount + $tiangListrikCount + $tiangTeleponCount + $vmsCount;
        
        $allAssets = collect()
            ->merge($manhole)
            ->merge($patokHM)
            ->merge($patokKM)
            ->merge($patokLJ)
            ->merge($patokPemandu)
            ->merge($patokRMJ)
            ->merge($patokROW)
            ->merge($rambuLaluLintas)
            ->merge($rambuPenunjukArah)
            // ->merge($reflektor)
            ->merge($rumahKabelPoint)
            ->merge($staText)
            ->merge($tiangListrik)
            ->merge($tiangTelepon)
            ->merge($vms);

        // Kelompokkan berdasarkan ruas_id
        $asetPerRuas = $allAssets
            ->groupBy('jalan_tol_id') // kelompokkan berdasarkan jalan_tol_id
            ->map(function ($group) {
                return $group->count(); // jumlah total aset di ruas ini
            });

        // Ambil nama ruas dari JalanTol
        $asetPerRuasWithName = $asetPerRuas->mapWithKeys(function ($jumlah, $id) {
            $namaRuas = JalanTol::find($id)?->nama ?? 'Tidak diketahui';
            return [$namaRuas => $jumlah];
        });
        
        return response()->json([
            'total_ruas' => $total_ruas,
            'jumlah_user' => $jumlah_user,
            'jumlah_ruas_user' => $jumlah_ruas_user,
            'jumlah_leger_user' => $jumlah_leger_user,
            'lhr_gol_i' => $lhr_gol_i,
            'lhr_gol_ii' => $lhr_gol_ii,
            'lhr_gol_iii' => $lhr_gol_iii,
            'lhr_gol_iv' => $lhr_gol_iv,
            'lhr_gol_v' => $lhr_gol_v,
            'iri_baik' => $iri_baik,
            'iri_sedang' => $iri_sedang,
            'iri_rusak_ringan' => $iri_rusak_ringan,
            'iri_rusak_berat' => $iri_rusak_berat,
            'manhole' => $manholeCount,
            'patokHM' => $patokHMCount,
            'patokKM' => $patokKMCount,
            'patokLJ' => $patokLJCount,
            'patokPemandu' => $patokPemanduCount,
            'patokRMJ' => $patokRMJCount,
            'patokROW' => $patokROWCount,
            'rambuLaluLintas' => $rambuLaluLintasCount,
            'rambuPenunjukArah' => $rambuPenunjukArahCount,
            // 'reflektor' => $reflektorCount,
            'rumahKabelPoint' => $rumahKabelPointCount,
            'staText' => $staTextCount,
            'tiangListrik' => $tiangListrikCount,
            'tiangTelepon' => $tiangTeleponCount,
            'vms' => $vmsCount,
            'assetSum' => $assetSum,
            'asetPerRuas' => $asetPerRuasWithName
        ]);
    }
}
