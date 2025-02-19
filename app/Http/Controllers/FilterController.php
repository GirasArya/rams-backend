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

    public function getIRIKMOptions()
    {
        $IRIKMOptions = DB::table('spatial_iri_polygon')
            ->select('km')
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
}