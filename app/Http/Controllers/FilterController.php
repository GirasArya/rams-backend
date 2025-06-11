<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FilterController extends Controller
{
    public function getKMOptions()
    {
        $kmOptions = DB::table('spatial_patok_km_point')
            ->select('km')
            ->distinct()
            ->orderBy('km')
            ->get();

        return response()->json(['data' => $kmOptions]);
    }

public function getIRIKMOptions($nilai_iri = null)
{
    $IRIKMOptions = DB::table('spatial_iri_polygon')
        ->select('km')
        ->where(function ($query) use ($nilai_iri) {
            if ($nilai_iri == 'Baik') {
                $query->where('nilai_iri', '<', 4);
            } elseif ($nilai_iri == 'Sedang') {
                $query->whereBetween('nilai_iri', [4, 8]);
            } elseif ($nilai_iri == 'Rusak Ringan') {
                $query->whereBetween('nilai_iri', [8, 12]);
            } elseif ($nilai_iri == 'Rusak Berat') {
                $query->where('nilai_iri', '>', 12);
            }
        })
        ->distinct()
        ->orderBy('km')
        ->get();
    return response()->json(['data' => $IRIKMOptions]);
}

    public function getIRIJalur()
    {
        $IRIJalur = DB::table('spatial_iri_polygon')
            ->select('jalur')
            ->distinct()
            ->orderBy('jalur')
            ->get();
        return response()->json(['data' => $IRIJalur]);
    }

    public function getIRIBagianJalan()
    {
        $IRI_bagianJalan = DB::table('spatial_iri_polygon')
            ->select('bagian_jalan')
            ->distinct()
            ->orderBy('bagian_jalan')
            ->get();
        return response()->json(['data' => $IRI_bagianJalan]);
    }

    public function getRuasJalan()
    {
        $ruasJalanTol = DB::table('jalan_tol')
            ->select('id', 'nama')
            ->distinct()
            ->orderBy('id')
            ->get();
        return response()->json(['data' => $ruasJalanTol]);
    }
}