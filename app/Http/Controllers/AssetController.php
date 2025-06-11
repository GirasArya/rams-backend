<?php

namespace App\Http\Controllers;

use App\Models\Leger;
use App\Models\RecordAset;
use App\Models\Teknik\Jalan\LegerJalan;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB as FacadesDB;
use Illuminate\Support\Facades\Storage as FacadesStorage;
use Storage;

class AssetController extends Controller
{

    // public function getRecordAsset()
    // {
    //     try {
    //         $records = RecordAset::with('ruasJalan', 'recordPatokKmPoint')->get();

    //         $formattedRecords = $records->map(function ($record) {
    //             $ruasJalan = $record->ruasJalan ? [
    //                 'id' => $record->ruasJalan->id,
    //                 'nama' => $record->ruasJalan->nama
    //             ] : null;

    //             $patokKmPoints = $record->recordPatokKmPoint->map(function ($patok) {
    //                 return [
    //                     'id' => $patok->id,
    //                     'km' => $patok->km,
    //                     'created_at' => $patok->created_at,
    //                 ];
    //             });

    //             return [
    //                 'id' => $record->id,
    //                 'id_ruas' => $record->id_ruas,
    //                 'ruas_jalan' => $ruasJalan ? [$ruasJalan] : [],
    //                 'jenis_aset' => $record->jenis_aset,
    //                 'aset_jalan' => $patokKmPoints,
    //                 'tanggal_pemasangan' => $record->tanggal_pemasangan,
    //             ];
    //         });

    //         return response()->json($formattedRecords, 200);
    //     } catch (\Exception $e) {
    //         return response()->json(['message' => 'Error retrieving records: ' . $e->getMessage()], 500);
    //     }
    // }
    public function getRecordAsset(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 10);
            $page = $request->query('page', 1);

            $records = RecordAset::with(
                'ruasJalan',
                'recordPatokKmPoint',
                'recordPatokHmPoint',
                'recordPatokLjPoint',
                'recordPatokRmjPoint',
                'recordPatokRowPoint',
                'recordManholePoint',
            )
                ->get();

            $flattened = collect();

            foreach ($records as $record) {
                $commonData = [
                    'record_aset_id' => $record->id,
                    'jenis_aset' => $record->jenis_aset,
                    'ruas_jalan' => $record->ruasJalan?->nama ?? 'Tidak diketahui',
                    'tanggal_pemasangan' => $record->tanggal_pemasangan,
                ];

                foreach ($record->recordPatokKmPoint as $item) {
                    $flattened->push(array_merge($commonData, [
                        'aset_id' => $item->id,
                        'tipe_aset' => 'Patok KM',
                        'km' => $item->km,
                        'status' => $item->status != null ? $item->status : '-',
                        'created_at' => $item->created_at,
                    ]));
                }

                foreach ($record->recordPatokHmPoint as $item) {
                    $flattened->push(array_merge($commonData, [
                        'aset_id' => $item->id,
                        'tipe_aset' => 'Patok HM',
                        'km' => $item->km,
                        'status' => $item->status != null ? $item->status : '-',
                        'created_at' => $item->created_at,
                    ]));
                }

                foreach ($record->recordPatokLjPoint as $item) {
                    $flattened->push(array_merge($commonData, [
                        'aset_id' => $item->id,
                        'tipe_aset' => 'Patok LJ',
                        'km' => $item->km,
                        'status' => $item->status != null ? $item->status : '-',
                        'created_at' => $item->created_at,
                    ]));
                }

                foreach ($record->recordManholePoint as $item) {
                    $flattened->push(array_merge($commonData, [
                        'aset_id' => $item->id,
                        'tipe_aset' => 'Manhole',
                        'km' => $item->km,
                        'status' => $item->kondisi != null ? $item->kondisi : '-',
                        'created_at' => $item->created_at,
                    ]));
                }

                foreach ($record->recordPatokRowPoint as $item) {
                    $flattened->push(array_merge($commonData, [
                        'aset_id' => $item->id,
                        'tipe_aset' => 'Patok ROW',
                        'km' => $item->km,
                        'status' => $item->kondisi != null ? $item->kondisi : '-',
                        'created_at' => $item->created_at,
                    ]));
                }

                foreach ($record->recordPatokRmjPoint as $item) {
                    $flattened->push(array_merge($commonData, [
                        'aset_id' => $item->id,
                        'tipe_aset' => 'Patok RMJ',
                        'km' => $item->km,
                        'status' => $item->kondisi != null ? $item->kondisi : '-',
                        'created_at' => $item->created_at,
                    ]));
                }
            }

            // Manual pagination
            $paginated = $flattened->forPage($page, $perPage)->values();
            $total = $flattened->count();

            return response()->json([
                'data' => $paginated,
                'current_page' => (int) $page,
                'per_page' => (int) $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage),
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving records: ' . $e->getMessage()], 500);
        }
    }



    public function getRecentAsset()
    {
        try {
            $records = RecordAset::with('ruasJalan')->latest('id')->take(2)->get();

            $formattedRecords = $records->map(function ($record) {
                $ruasJalan = $record->ruasJalan ? [
                    'id' => $record->ruasJalan->id,
                    'nama' => $record->ruasJalan->nama
                ] : null;

                return [
                    'id' => $record->id,
                    'id_ruas' => $record->id_ruas,
                    'ruas_jalan' => $ruasJalan ? [$ruasJalan] : [],
                    'jenis_aset' => $record->jenis_aset,
                    'tanggal_pemasangan' => $record->tanggal_pemasangan,
                    'created-at' => $record->created_at,
                ];
            });

            return response()->json($formattedRecords, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving records: ' . $e->getMessage()], 500);
        }
    }

    public function getFilteredAssets(Request $request)
    {
        $aset = $request->query('jenis_aset');
        $tanggal = $request->query('tanggal_pemasangan');

        $filterAset = RecordAset::with('ruasJalan', 'recordPatokKmPoint');

        if ($aset !== null) {
            $filterAset->where('jenis_aset', $aset);
        }

        if ($tanggal !== null) {
            $filterAset->where('tanggal_pemasangan', $tanggal);
        }

        $records = $filterAset->get();

        $formattedRecords = $records->map(function ($record) {
            $ruasJalan = $record->ruasJalan ? [
                'id' => $record->ruasJalan->id,
                'nama' => $record->ruasJalan->nama
            ] : null;

            $asetJalan = $record->recordPatokKmPoint->map(function ($patok) {
                return [
                    'id' => $patok->id,
                    'km' => $patok->km,
                    'created_at' => $patok->created_at,
                ];
            });

            return [
                'id' => $record->id,
                'id_ruas' => $record->id_ruas,
                'ruas_jalan' => $ruasJalan ? [$ruasJalan] : [],
                'jenis_aset' => $record->jenis_aset,
                'tanggal_pemasangan' => $record->tanggal_pemasangan,
                'aset_jalan' => $asetJalan,
            ];
        });

        return response()->json($formattedRecords);
    }

    public function storeAssetRecord(Request $request)
    {
        $uploadFunctions = [
            'administratif_polygon' => 'uploadAdministratifPolygon',
            'batas_desa_line' => 'uploadBatasDesaLine',
            'box_culvert_line' => 'uploadBoxCulvertLine',
            'bpt_line' => 'uploadBPTLine',
            'bronjong_line' => 'uploadBronjongLine',
            'concrete_barrier_line' => 'uploadConcreteBarrierLine',
            'data_geometrik_jalan_polygon' => 'uploadDataGeometrikJalanPolygon',
            'gerbang_line' => 'uploadGerbangLine',
            'gerbang_point' => 'uploadGerbangPoint',
            'gorong_gorong_line' => 'uploadGorongGorongLine',
            'guardrail_line' => 'uploadGuardrailLine',
            'iri_polygon' => 'uploadIRIPolygon',
            'jalan_line' => 'uploadJalanLine',
            'jembatan_point' => 'uploadJembatanPoint',
            'jembatan_polygon' => 'uploadJembatanPolygon',
            'lampu_lalulintas_point' => 'uploadLampuLalulintasPoint',
            'lapis_permukaan_polygon' => 'uploadLapisPermukaanPolygon',
            'lapis_pondasi_atas1_polygon' => 'uploadLapisPondasiAtas1Polygon',
            'lapis_pondasi_atas2_polygon' => 'uploadLapisPondasiAtas2Polygon',
            'lapis_pondasi_bawah_polygon' => 'uploadLapisPondasiBawahPolygon',
            'lhr_polygon' => 'uploadLHRPolygon',
            'listrik_bawahtanah_line' => 'uploadListrikBawahtanahLine',
            'manhole_point' => 'uploadManholePoint',
            'marka_line' => 'uploadMarkaLine',
            'pagar_operasional_line' => 'uploadPagarOperasionalLine',
            'patok_hm_point' => 'uploadPatokHMPoint',
            'patok_km_point' => 'uploadPatokKMPoint',
            'patok_lj_point' => 'uploadPatokLJPoint',
            'patok_pemandu_point' => 'uploadPatokPemanduPoint',
            'patok_rmj_point' => 'uploadPatokRMJPoint',
            'patok_row_point' => 'uploadPatokROWPoint',
            'pita_kejut_line' => 'uploadPitaKejutLine',
            'rambu_lalulintas_point' => 'uploadRambuLalulintasPoint',
            'rambu_penunjukarah_point' => 'uploadRambuPenunjukarahPoint',
            'reflektor_point' => 'uploadReflektorPoint',
            'riol_line' => 'uploadRiolLine',
            'rumah_kabel_point' => 'uploadRumahKabelPoint',
            'ruwasja_polygon' => 'uploadRuwasjaPoint',
            'saluran_line' => 'uploadSaluranLine',
            'segmen_konstruksi_polygon' => 'uploadSegmenKonstruksiPolygon',
            'segmen_leger_polygon' => 'uploadSegmenLegerPolygon',
            'segmen_perlengkapan_polygon' => 'uploadSegmenPerlengkapanPolygon',
            'segmen_seksi_polygon' => 'uploadSegmenSeksiPolygon',
            'segmen_tol_polygon' => 'uploadSegmenTolPolygon',
            'sta_text_point' => 'uploadStaTextPoint',
            'sungai_line' => 'uploadSungaiLine',
            'telepon_bawahtanah_line' => 'uploadTeleponBawahtanahLine',
            'tiang_listrik_point' => 'uploadTiangListrikPoint',
            'tiang_telepon_point' => 'uploadTiangTeleponPoint',
            'vms_point' => 'uploadVMSPoint',
        ];

        // if (Auth::user()->role_id == 1) {
        if (array_key_exists($request->jenis_aset, $uploadFunctions)) {
            return $this->{$uploadFunctions[$request->jenis_aset]}($request);
        } else {
            abort(404, 'Tipe Aset tidak ditemukan');
        }
        // } else {
        // abort(403);
        // }
    }

    public function deleteAssetRecord($id)
    {
        try {
            $record = RecordAset::with([
                'recordPatokKmPoint',
                'recordPatokHmPoint',
                'recordPatokLjPoint'
            ])->find($id);

            if (!$record) {
                return response()->json(['message' => 'Record not found'], 404);
            }

            // Hapus semua relasi terkait
            $record->recordPatokKmPoint()->delete();
            $record->recordPatokHmPoint()->delete();
            $record->recordPatokLjPoint()->delete();

            // Hapus record utama
            $record->delete();

            return response()->json(['message' => 'Record and related assets deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting record: ' . $e->getMessage()], 500);
        }
    }

    // fungsi untuk store data geoJson dari kode bang wawan
    public function uploadPatokHMPoint(Request $request)
    {
        //Insert data ke tabel record_aset
        $record = new RecordAset;
        $record->fill([
            'id_ruas' => $request->id_ruas,
            'jenis_aset' => $request->jenis_aset,
            'tanggal_pemasangan' => $request->tanggal_pemasangan,
        ]);
        $record->save();

        //jika ada data geoJson masukan ke tabel geospasial
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            // $file->move('temp', $filename);
            FacadesStorage::disk('local')->put("temp/{$filename}", file_get_contents($file));
            $jalan_tol_id = $request->id_ruas;
            $record_id = $record->id;

            // Insert Data
            // $geojson = file_get_contents(public_path('temp/' . $filename));
            $geojson = FacadesStorage::disk('local')->get("temp/{$filename}");
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::insert("INSERT INTO spatial_patok_hm_point (
                   jalan_tol_id,
                   geom,
                   layer,
                   km,
                   created_at,
                   updated_at,
                   record_aset_id
               ) VALUES (
                   ?, ST_GeomFromGeoJSON(?), ?, ?, ?, ?, ?
               )", [
                    $jalan_tol_id,
                    json_encode($object["geometry"]),
                    $object["properties"]["layer"] ?? null,
                    $object["properties"]["km"] ?? null,
                    now(),
                    now(),
                    $record_id,
                ]);
            }
            // unlink(public_path('temp/' . $filename));
            $geojson = FacadesStorage::disk('local')->get("temp/{$filename}");
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadPatokKMPoint(Request $request)
    {
        //Insert data ke tabel record_aset
        $record = new RecordAset;
        $record->fill([
            'id_ruas' => $request->id_ruas,
            'jenis_aset' => $request->jenis_aset,
            'tanggal_pemasangan' => $request->tanggal_pemasangan,
        ]);
        $record->save();

        //jika ada data geoJson masukan ke tabel geospasial
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            // $file->move('temp', $filename);
            FacadesStorage::disk('local')->put("temp/{$filename}", file_get_contents($file));
            $jalan_tol_id = $request->id_ruas;
            $record_id = $record->id;

            // Insert Data
            // $geojson = file_get_contents(public_path('temp/' . $filename));
            $geojson = FacadesStorage::disk('local')->get("temp/{$filename}");
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::insert("INSERT INTO spatial_patok_km_point (
                    jalan_tol_id,
                    geom,
                    layer,
                    km,
                    created_at,
                    updated_at,
                    record_aset_id
                ) VALUES (
                    ?, ST_GeomFromGeoJSON(?), ?, ?, ?, ?, ?
                )", [
                    $jalan_tol_id,
                    json_encode($object["geometry"]),
                    $object["properties"]["layer"] ?? null,
                    $object["properties"]["km"] ?? null,
                    now(),
                    now(),
                    $record_id,
                ]);
            }
            // unlink(public_path('temp/' . $filename));
            $geojson = FacadesStorage::disk('local')->get("temp/{$filename}");
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadPatokLJPoint(Request $request)
    {
        //Insert data ke tabel record_aset
        $record = new RecordAset;
        $record->fill([
            'id_ruas' => $request->id_ruas,
            'jenis_aset' => $request->jenis_aset,
            'tanggal_pemasangan' => $request->tanggal_pemasangan,
        ]);
        $record->save();

        //jika ada data geoJson masukan ke tabel geospasial
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            // $file->move('temp', $filename);
            FacadesStorage::disk('local')->put("temp/{$filename}", file_get_contents($file));
            $jalan_tol_id = $request->id_ruas;
            $record_id = $record->id;

            // Insert Data
            // $geojson = file_get_contents(public_path('temp/' . $filename));
            $geojson = FacadesStorage::disk('local')->get("temp/{$filename}");
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::insert("INSERT INTO spatial_patok_lj_point (
                   jalan_tol_id,
                   geom,
                   layer,
                   keterangan,
                   deskripsi,
                   created_at,
                   updated_at,
                   record_aset_id
               ) VALUES (
                   ?, ST_GeomFromGeoJSON(?), ?, ?, ?, ?, ?, ?
               )", [
                    $jalan_tol_id,
                    json_encode($object["geometry"]),
                    $object["properties"]["layer"] ?? null,
                    $object["properties"]["keterangan"] ?? null,
                    $object["properties"]["deskripsi"] ?? null,
                    now(),
                    now(),
                    $record_id,
                ]);
            }
            // unlink(public_path('temp/' . $filename));
            $geojson = FacadesStorage::disk('local')->get("temp/{$filename}");
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadAdministratifPolygon(Request $request)
    {
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            $file->move('temp', $filename);
            $jalan_tol_id = $request->ruas_id;

            // Insert Data
            $geojson = file_get_contents(public_path('temp/' . $filename));
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_administratif_polygon (
                        jalan_tol_id, 
                        geom, 
                        txtmemo, 
                        kode_prov, 
                        nama_prov, 
                        kode_kab, 
                        nama_kab, 
                        kode_kec, 
                        nama_kec, 
                        kode_desa, 
                        nama_desa, 
                        tahun, 
                        created_at, 
                        updated_at
                    )
                    VALUES (
                        " . $jalan_tol_id . ", 
                        ST_GeomFromGeoJSON('" . json_encode($object["geometry"]) . "'), 
                        '" . $object["properties"]["txtmemo"] . "',
                        '" . $object["properties"]["kode_prov"] . "',
                        '" . $object["properties"]["nama_prov"] . "',
                        '" . $object["properties"]["kode_kab"] . "',
                        '" . $object["properties"]["nama_kab"] . "',
                        '" . $object["properties"]["kode_kec"] . "',
                        '" . $object["properties"]["nama_kec"] . "',
                        '" . $object["properties"]["kode_desa"] . "',
                        '" . $object["properties"]["nama_desa"] . "',
                        '" . $object["properties"]["tahun"] . "',
                        '" . now() . "',
                        '" . now() . "'
                    )
                ");
            }
            unlink(public_path('temp/' . $filename));
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadBatasDesaLine(Request $request)
    {
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            $file->move('temp', $filename);
            $jalan_tol_id = $request->ruas_id;

            // Insert Data
            $geojson = file_get_contents(public_path('temp/' . $filename));
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_batas_desa_line (
                        jalan_tol_id, 
                        geom, 
                        layer,
                        created_at, 
                        updated_at
                    )
                    VALUES (
                        " . $jalan_tol_id . ", 
                        ST_GeomFromGeoJSON('" . json_encode($object["geometry"]) . "'), 
                        '" . $object["properties"]["layer"] . "',
                        '" . now() . "',
                        '" . now() . "'
                    )
                ");
            }
            unlink(public_path('temp/' . $filename));
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadBoxCulvertLine(Request $request)
    {
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            $file->move('temp', $filename);
            $jalan_tol_id = $request->ruas_id;

            // Insert Data
            $geojson = file_get_contents(public_path('temp/' . $filename));
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_box_culvert_line (
                        jalan_tol_id, 
                        geom, 
                        layer,
                        jenis_material,
                        ukuran_panjang,
                        kondisi,
                        created_at, 
                        updated_at
                    )
                    VALUES (
                        " . $jalan_tol_id . ", 
                        ST_GeomFromGeoJSON('" . json_encode($object["geometry"]) . "'), 
                        '" . $object["properties"]["layer"] . "',
                        '" . $object["properties"]["jns_mtrial"] . "',
                        '" . $object["properties"]["ukrn_pnjng"] . "',
                        '" . $object["properties"]["kondisi"] . "',
                        '" . now() . "',
                        '" . now() . "'
                    )
                ");
            }
            unlink(public_path('temp/' . $filename));
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadBPTLine(Request $request)
    {
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            $file->move('temp', $filename);
            $jalan_tol_id = $request->ruas_id;

            // Insert Data
            $geojson = file_get_contents(public_path('temp/' . $filename));
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_bpt_line (
                        jalan_tol_id, 
                        geom, 
                        layer,
                        jenis_material,
                        ukuran_pokok,
                        kondisi,
                        created_at, 
                        updated_at
                    )
                    VALUES (
                        " . $jalan_tol_id . ", 
                        ST_GeomFromGeoJSON('" . json_encode($object["geometry"]) . "'), 
                        '" . $object["properties"]["layer"] . "',
                        '" . $object["properties"]["jns_mtrial"] . "',
                        '" . $object["properties"]["ukrn_pokok"] . "',
                        '" . $object["properties"]["kondisi"] . "',
                        '" . now() . "',
                        '" . now() . "'
                    )
                ");
            }
            unlink(public_path('temp/' . $filename));
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadBronjongLine(Request $request)
    {
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            $file->move('temp', $filename);
            $jalan_tol_id = $request->ruas_id;

            // Insert Data
            $geojson = file_get_contents(public_path('temp/' . $filename));
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_bronjong_line (
                        jalan_tol_id, 
                        geom, 
                        layer,
                        created_at, 
                        updated_at
                    )
                    VALUES (
                        " . $jalan_tol_id . ", 
                        ST_GeomFromGeoJSON('" . json_encode($object["geometry"]) . "'), 
                        '" . $object["properties"]["layer"] . "',
                        '" . now() . "',
                        '" . now() . "'
                    )
                ");
            }
            unlink(public_path('temp/' . $filename));
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadConcreteBarrierLine(Request $request)
    {
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            $file->move('temp', $filename);
            $jalan_tol_id = $request->ruas_id;

            // Insert Data
            $geojson = file_get_contents(public_path('temp/' . $filename));
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_concrete_barrier_line (
                        jalan_tol_id, 
                        geom, 
                        layer,
                        created_at, 
                        updated_at
                    )
                    VALUES (
                        " . $jalan_tol_id . ", 
                        ST_GeomFromGeoJSON('" . json_encode($object["geometry"]) . "'), 
                        '" . $object["properties"]["layer"] . "',
                        '" . now() . "',
                        '" . now() . "'
                    )
                ");
            }
            unlink(public_path('temp/' . $filename));
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadDataGeometrikJalanPolygon(Request $request)
    {
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            $file->move('temp', $filename);
            $jalan_tol_id = $request->ruas_id;

            // Insert Data
            $geojson = file_get_contents(public_path('temp/' . $filename));
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_data_geometrik_jalan_polygon (
                        jalan_tol_id, 
                        geom, 
                        id_leger,
                        segmen_tol,
                        nama,
                        lebar_rmj,
                        gradien_kiri,
                        gradien_kanan,
                        cross_fall_kiri,
                        cross_fall_kanan,
                        super_elevasi,
                        radius,
                        terrain_kiri,
                        terrain_kanan,
                        tataguna_lahan_kiri,
                        tataguna_lahan_kanan,
                        created_at, 
                        updated_at
                    )
                    VALUES (
                        " . $jalan_tol_id . ", 
                        ST_GeomFromGeoJSON('" . json_encode($object["geometry"]) . "'), 
                        '" . $object["properties"]["id_leger"] . "',
                        '" . $object["properties"]["sgmn_tol"] . "',
                        '" . $object["properties"]["nama"] . "',
                        '" . $object["properties"]["lebar_rmj"] . "',
                        '" . $object["properties"]["gradien_ki"] . "',
                        '" . $object["properties"]["gradien_ka"] . "',
                        '" . $object["properties"]["crs_fal_ki"] . "',
                        '" . $object["properties"]["crs_fal_ka"] . "',
                        '" . $object["properties"]["spr_elevsi"] . "',
                        '" . $object["properties"]["radius"] . "',
                        '" . $object["properties"]["terrain_ki"] . "',
                        '" . $object["properties"]["terrain_ka"] . "',
                        '" . $object["properties"]["ttgn_lh_ki"] . "',
                        '" . $object["properties"]["ttgn_lh_ka"] . "',
                        '" . now() . "',
                        '" . now() . "'
                    )
                ");
            }
            unlink(public_path('temp/' . $filename));
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadGerbangLine(Request $request)
    {
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            $file->move('temp', $filename);
            $jalan_tol_id = $request->ruas_id;

            // Insert Data
            $geojson = file_get_contents(public_path('temp/' . $filename));
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_gerbang_line (
                        jalan_tol_id, 
                        geom, 
                        layer,
                        created_at, 
                        updated_at
                    )
                    VALUES (
                        " . $jalan_tol_id . ", 
                        ST_GeomFromGeoJSON('" . json_encode($object["geometry"]) . "'), 
                        '" . $object["properties"]["layer"] . "',
                        '" . now() . "',
                        '" . now() . "'
                    )
                ");
            }
            unlink(public_path('temp/' . $filename));
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadGerbangPoint(Request $request)
    {
        //Insert data ke tabel record_aset
        $record = new RecordAset;
        $record->fill([
            'id_ruas' => $request->id_ruas,
            'jenis_aset' => $request->jenis_aset,
            'tanggal_pemasangan' => $request->tanggal_pemasangan,
        ]);
        $record->save();

        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            // $file->move('temp', $filename);
            FacadesStorage::disk('local')->put("temp/{$filename}", file_get_contents($file));
            $jalan_tol_id = $request->ruas_id;
            $record_id = $record->id;

            // Insert Data
            // $geojson = file_get_contents(public_path('temp/' . $filename));
            $geojson = FacadesStorage::disk('local')->get("temp/{$filename}");
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_gerbang_point (
                        jalan_tol_id, 
                        geom, 
                        layer,
                        created_at, 
                        updated_at,
                        record_aset_id
                    )
                    VALUES (
                        ?, ST_GeomFromGeoJSON(?), ?, ?, ?, ?
                    )", [
                    $jalan_tol_id,
                    json_encode($object["geometry"]),
                    $object["properties"]["layer"],
                    now(),
                    now(),
                    $record_id
                ]);
            }
            // unlink(public_path('temp/' . $filename));
            $geojson = FacadesStorage::disk('local')->get("temp/{$filename}");
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadGorongGorongLine(Request $request)
    {
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            $file->move('temp', $filename);
            $jalan_tol_id = $request->ruas_id;

            // Insert Data
            $geojson = file_get_contents(public_path('temp/' . $filename));
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_gorong_gorong_line (
                        jalan_tol_id, 
                        geom, 
                        layer,
                        jenis_material,
                        ukuran_panjang,
                        kondisi,
                        diameter,
                        created_at, 
                        updated_at
                    )
                    VALUES (
                        " . $jalan_tol_id . ", 
                        ST_GeomFromGeoJSON('" . json_encode($object["geometry"]) . "'), 
                        '" . $object["properties"]["layer"] . "',
                        '" . $object["properties"]["jns_mtrial"] . "',
                        '" . $object["properties"]["ukrn_pnjng"] . "',
                        '" . $object["properties"]["kondisi"] . "',
                        '" . $object["properties"]["diameter"] . "',
                        '" . now() . "',
                        '" . now() . "'
                    )
                ");
            }
            unlink(public_path('temp/' . $filename));
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadGuardrailLine(Request $request)
    {
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            $file->move('temp', $filename);
            $jalan_tol_id = $request->ruas_id;

            // Insert Data
            $geojson = file_get_contents(public_path('temp/' . $filename));
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_guardrail_line (
                        jalan_tol_id, 
                        geom, 
                        layer,
                        created_at, 
                        updated_at
                    )
                    VALUES (
                        " . $jalan_tol_id . ", 
                        ST_GeomFromGeoJSON('" . json_encode($object["geometry"]) . "'), 
                        '" . $object["properties"]["layer"] . "',
                        '" . now() . "',
                        '" . now() . "'
                    )
                ");
            }
            unlink(public_path('temp/' . $filename));
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadIRIPolygon(Request $request)
    {
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            $file->move('temp', $filename);
            $jalan_tol_id = $request->ruas_id;

            // Insert Data
            $geojson = file_get_contents(public_path('temp/' . $filename));
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_iri_polygon (
                        jalan_tol_id, 
                        geom, 
                        jalur,
                        bagian_jalan,
                        lebar,
                        segmen_tol,
                        km,
                        nilai_iri,
                        created_at, 
                        updated_at
                    )
                    VALUES (
                        " . $jalan_tol_id . ", 
                        ST_GeomFromGeoJSON('" . json_encode($object["geometry"]) . "'), 
                        '" . $object["properties"]["jalur"] . "',
                        '" . $object["properties"]["bagian_jln"] . "',
                        '" . $object["properties"]["lebar"] . "',
                        '" . $object["properties"]["sgm_tol"] . "',
                        '" . $object["properties"]["km"] . "',
                        '" . $object["properties"]["nilai_iri"] . "',
                        '" . now() . "',
                        '" . now() . "'
                    )
                ");
            }
            unlink(public_path('temp/' . $filename));
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadJalanLine(Request $request)
    {
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            $file->move('temp', $filename);
            $jalan_tol_id = $request->ruas_id;

            // Insert Data
            $geojson = file_get_contents(public_path('temp/' . $filename));
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_jalan_line (
                        jalan_tol_id, 
                        geom, 
                        layer,
                        created_at, 
                        updated_at
                    )
                    VALUES (
                        " . $jalan_tol_id . ", 
                        ST_GeomFromGeoJSON('" . json_encode($object["geometry"]) . "'), 
                        '" . $object["properties"]["layer"] . "',
                        '" . now() . "',
                        '" . now() . "'
                    )
                ");
            }
            unlink(public_path('temp/' . $filename));
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadJembatanPoint(Request $request)
    {
        $record = new RecordAset;
        $record->fill([
            'id_ruas' => $request->id_ruas,
            'jenis_aset' => $request->jenis_aset,
            'tanggal_pemasangan' => $request->tanggal_pemasangan,
        ]);
        $record->save();

        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            // $file->move('temp', $filename);
            FacadesStorage::disk('local')->put("temp/{$filename}", file_get_contents($file));
            $jalan_tol_id = $request->id_ruas;
            $record_id = $record->id;

            // Insert Data
            // $geojson = file_get_contents(public_path('temp/' . $filename));
            $geojson = FacadesStorage::disk('local')->get("temp/{$filename}");
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_jembatan_point (
                        jalan_tol_id, 
                        geom, 
                        nama,
                        km,
                        panjang,
                        lebar,
                        luas,
                        absis_x,
                        ordinat_y,
                        created_at, 
                        updated_at,
                        record_aset_id
                    )
                    VALUES (
                        ?, 
                        ST_GeomFromGeoJSON(?), 
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?, 
                        ?,
                        ?
                    )
                ", [
                    $jalan_tol_id,
                    json_encode($object["geometry"]),
                    $object["properties"]["nama"],
                    $object["properties"]["km"],
                    $object["properties"]["panjang"],
                    $object["properties"]["lebar"],
                    $object["properties"]["luas"],
                    $object["properties"]["absis_x"],
                    $object["properties"]["ordinat_y"],
                    now(),
                    now(),
                    $record_id
                ]);
            }
            // unlink(public_path('temp/' . $filename));
            $geojson = FacadesStorage::disk('local')->get("temp/{$filename}");
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadJembatanPolygon(Request $request)
    {
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            $file->move('temp', $filename);
            $jalan_tol_id = $request->ruas_id;

            // Insert Data
            $geojson = file_get_contents(public_path('temp/' . $filename));
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_jembatan_polygon (
                        jalan_tol_id, 
                        geom, 
                        nama,
                        km,
                        panjang,
                        lebar,
                        luas,
                        created_at, 
                        updated_at
                    )
                    VALUES (
                        " . $jalan_tol_id . ", 
                        ST_GeomFromGeoJSON('" . json_encode($object["geometry"]) . "'), 
                        '" . $object["properties"]["nama"] . "',
                        '" . $object["properties"]["km"] . "',
                        '" . $object["properties"]["panjang"] . "',
                        '" . $object["properties"]["lebar"] . "',
                        '" . $object["properties"]["luas"] . "',
                        '" . now() . "',
                        '" . now() . "'
                    )
                ");
            }
            unlink(public_path('temp/' . $filename));
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadLampuLalulintasPoint(Request $request)
    {
        //Insert data ke tabel record_aset
        $record = new RecordAset;
        $record->fill([
            'id_ruas' => $request->id_ruas,
            'jenis_aset' => $request->jenis_aset,
            'tanggal_pemasangan' => $request->tanggal_pemasangan,
        ]);
        $record->save();

        //jika ada data geoJson masukan ke tabel geospasial
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            // $file->move('temp', $filename);
            FacadesStorage::disk('local')->put("temp/{$filename}", file_get_contents($file));
            $jalan_tol_id = $request->id_ruas;
            $record_id = $record->id;

            // Insert Data
            // $geojson = file_get_contents(public_path('temp/' . $filename));
            $geojson = FacadesStorage::disk('local')->get("temp/{$filename}");
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_lampu_lalulintas_point (
                        jalan_tol_id, 
                        geom, 
                        absis_x,
                        ordinat_y,
                        created_at, 
                        updated_at
                        record_aset_id,
                    )
                    VALUES (
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                    )
                ", [
                    $jalan_tol_id,
                    json_encode($object["geometry"]),
                    $object["properties"]["absis_x"],
                    $object["properties"]["ordinat_y"],
                    now(),
                    now(),
                    $record_id
                ]);
            }
            // unlink(public_path('temp/' . $filename));
            $geojson = FacadesStorage::disk('local')->get("temp/{$filename}");
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadLapisPermukaanPolygon(Request $request)
    {
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            $file->move('temp', $filename);
            $jalan_tol_id = $request->ruas_id;

            // Insert Data
            $geojson = file_get_contents(public_path('temp/' . $filename));
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_lapis_permukaan_polygon (
                        jalan_tol_id, 
                        geom, 
                        tebal,
                        jenis,
                        created_at, 
                        updated_at
                    )
                    VALUES (
                        " . $jalan_tol_id . ", 
                        ST_GeomFromGeoJSON('" . json_encode($object["geometry"]) . "'), 
                        '" . $object["properties"]["tebal"] . "',
                        '" . $object["properties"]["jenis"] . "',
                        '" . now() . "',
                        '" . now() . "'
                    )
                ");
            }
            unlink(public_path('temp/' . $filename));
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadLapisPondasiAtas1Polygon(Request $request)
    {
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            $file->move('temp', $filename);
            $jalan_tol_id = $request->ruas_id;

            // Insert Data
            $geojson = file_get_contents(public_path('temp/' . $filename));
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_lapis_pondasi_atas1_polygon (
                        jalan_tol_id, 
                        geom, 
                        tebal,
                        jenis,
                        created_at, 
                        updated_at
                    )
                    VALUES (
                        " . $jalan_tol_id . ", 
                        ST_GeomFromGeoJSON('" . json_encode($object["geometry"]) . "'), 
                        '" . $object["properties"]["tebal"] . "',
                        '" . $object["properties"]["jenis"] . "',
                        '" . now() . "',
                        '" . now() . "'
                    )
                ");
            }
            unlink(public_path('temp/' . $filename));
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadLapisPondasiAtas2Polygon(Request $request)
    {
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            $file->move('temp', $filename);
            $jalan_tol_id = $request->ruas_id;

            // Insert Data
            $geojson = file_get_contents(public_path('temp/' . $filename));
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_lapis_pondasi_atas2_polygon (
                        jalan_tol_id, 
                        geom, 
                        tebal,
                        jenis,
                        created_at, 
                        updated_at
                    )
                    VALUES (
                        " . $jalan_tol_id . ", 
                        ST_GeomFromGeoJSON('" . json_encode($object["geometry"]) . "'), 
                        '" . $object["properties"]["tebal"] . "',
                        '" . $object["properties"]["jenis"] . "',
                        '" . now() . "',
                        '" . now() . "'
                    )
                ");
            }
            unlink(public_path('temp/' . $filename));
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadLapisPondasiBawahPolygon(Request $request)
    {
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            $file->move('temp', $filename);
            $jalan_tol_id = $request->ruas_id;

            // Insert Data
            $geojson = file_get_contents(public_path('temp/' . $filename));
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_lapis_pondasi_bawah_polygon (
                        jalan_tol_id, 
                        geom, 
                        tebal,
                        jenis,
                        created_at, 
                        updated_at
                    )
                    VALUES (
                        " . $jalan_tol_id . ", 
                        ST_GeomFromGeoJSON('" . json_encode($object["geometry"]) . "'), 
                        '" . $object["properties"]["tebal"] . "',
                        '" . $object["properties"]["jenis"] . "',
                        '" . now() . "',
                        '" . now() . "'
                    )
                ");
            }
            unlink(public_path('temp/' . $filename));
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadLHRPolygon(Request $request)
    {
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            $file->move('temp', $filename);
            $jalan_tol_id = $request->ruas_id;

            // Insert Data
            $geojson = file_get_contents(public_path('temp/' . $filename));
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_lhr_polygon (
                        jalan_tol_id, 
                        geom, 
                        segmen_tol,
                        nama_segmen,
                        gol_i,
                        gol_ii,
                        gol_iii,
                        gol_iv,
                        gol_v,
                        created_at, 
                        updated_at
                    )
                    VALUES (
                        " . $jalan_tol_id . ", 
                        ST_GeomFromGeoJSON('" . json_encode($object["geometry"]) . "'), 
                        '" . $object["properties"]["sgm_tol"] . "',
                        '" . $object["properties"]["nama_sgmn"] . "',
                        '" . $object["properties"]["gol_i"] . "',
                        '" . $object["properties"]["gol_ii"] . "',
                        '" . $object["properties"]["gol_iii"] . "',
                        '" . $object["properties"]["gol_iv"] . "',
                        '" . $object["properties"]["gol_v"] . "',
                        '" . now() . "',
                        '" . now() . "'
                    )
                ");
            }
            unlink(public_path('temp/' . $filename));
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadListrikBawahtanahLine(Request $request)
    {
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            $file->move('temp', $filename);
            $jalan_tol_id = $request->ruas_id;

            // Insert Data
            $geojson = file_get_contents(public_path('temp/' . $filename));
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_listrik_bawahtanah_line (
                        jalan_tol_id, 
                        geom, 
                        layer,
                        created_at, 
                        updated_at
                    )
                    VALUES (
                        " . $jalan_tol_id . ", 
                        ST_GeomFromGeoJSON('" . json_encode($object["geometry"]) . "'), 
                        '" . $object["properties"]["layer"] . "',
                        '" . now() . "',
                        '" . now() . "'
                    )
                ");
            }
            unlink(public_path('temp/' . $filename));
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadManholePoint(Request $request)
    {
        //Insert data ke tabel record_aset
        $record = new RecordAset;
        $record->fill([
            'id_ruas' => $request->id_ruas,
            'jenis_aset' => $request->jenis_aset,
            'tanggal_pemasangan' => $request->tanggal_pemasangan,
        ]);
        $record->save();

        //jika ada data geoJson masukan ke tabel geospasial
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            // $file->move('temp', $filename);
            FacadesStorage::disk('local')->put("temp/{$filename}", file_get_contents($file));
            $jalan_tol_id = $request->id_ruas;
            $record_id = $record->id;

            // Insert Data
            // $geojson = file_get_contents(public_path('temp/' . $filename));
            $geojson = FacadesStorage::disk('local')->get("temp/{$filename}");
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_manhole_point (
                        jalan_tol_id, 
                        geom, 
                        layer,
                        jenis_material,
                        ukuran_pokok,
                        kondisi,
                        created_at, 
                        updated_at,
                        record_aset_id
                    )
                    VALUES (
                        ?, 
                        ST_GeomFromGeoJSON(?),
                        ?,
                        ?,
                        ?,
                        ?,
                        ?, 
                        ?,
                        ?
                    )
                ", [
                    $jalan_tol_id,
                    json_encode($object["geometry"]),
                    $object["properties"]["layer"],
                    $object["properties"]["jns_mtrial"],
                    $object["properties"]["ukrn_pokok"],
                    $object["properties"]["kondisi"],
                    now(),
                    now(),
                    $record_id
                ]);
            }
            // unlink(public_path('temp/' . $filename));
            $geojson = FacadesStorage::disk('local')->get("temp/{$filename}");
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadMarkaLine(Request $request)
    {
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            $file->move('temp', $filename);
            $jalan_tol_id = $request->ruas_id;

            // Insert Data
            $geojson = file_get_contents(public_path('temp/' . $filename));
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_marka_line (
                        jalan_tol_id, 
                        geom, 
                        layer,
                        created_at, 
                        updated_at
                    )
                    VALUES (
                        " . $jalan_tol_id . ", 
                        ST_GeomFromGeoJSON('" . json_encode($object["geometry"]) . "'), 
                        '" . $object["properties"]["layer"] . "',
                        '" . now() . "',
                        '" . now() . "'
                    )
                ");
            }
            unlink(public_path('temp/' . $filename));
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadPagarOperasionalLine(Request $request)
    {
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            $file->move('temp', $filename);
            $jalan_tol_id = $request->ruas_id;

            // Insert Data
            $geojson = file_get_contents(public_path('temp/' . $filename));
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_pagar_operasional_line (
                        jalan_tol_id, 
                        geom, 
                        layer,
                        jenis,
                        created_at, 
                        updated_at
                    )
                    VALUES (
                        " . $jalan_tol_id . ", 
                        ST_GeomFromGeoJSON('" . json_encode($object["geometry"]) . "'), 
                        '" . $object["properties"]["layer"] . "',
                        '" . $object["properties"]["jenis"] . "',
                        '" . now() . "',
                        '" . now() . "'
                    )
                ");
            }
            unlink(public_path('temp/' . $filename));
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadPatokPemanduPoint(Request $request)
    {
        //Insert data ke tabel record_aset
        $record = new RecordAset;
        $record->fill([
            'id_ruas' => $request->id_ruas,
            'jenis_aset' => $request->jenis_aset,
            'tanggal_pemasangan' => $request->tanggal_pemasangan,
        ]);
        $record->save();

        //jika ada data geoJson masukan ke tabel geospasial
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            // $file->move('temp', $filename);
            FacadesStorage::disk('local')->put("temp/{$filename}", file_get_contents($file));
            $jalan_tol_id = $request->id_ruas;
            $record_id = $record->id;

            // Insert Data
            // $geojson = file_get_contents(public_path('temp/' . $filename));
            $geojson = FacadesStorage::disk('local')->get("temp/{$filename}");
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_patok_pemandu_point (
                        jalan_tol_id, 
                        geom, 
                        layer,
                        created_at, 
                        updated_at,
                        record_aset_id
                    )
                    VALUES (
                        ?, 
                        ?, 
                        ?,
                        ?, 
                        ?,
                        ?
                    )
                ", [
                    $jalan_tol_id,
                    json_encode($object["geometry"]),
                    $object["properties"]["layer"],
                    now(),
                    now(),
                    $record_id
                ]);
            }
            // unlink(public_path('temp/' . $filename));
            $geojson = FacadesStorage::disk('local')->get("temp/{$filename}");
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadPatokRMJPoint(Request $request)
    {
        //Insert data ke tabel record_aset
        $record = new RecordAset;
        $record->fill([
            'id_ruas' => $request->id_ruas,
            'jenis_aset' => $request->jenis_aset,
            'tanggal_pemasangan' => $request->tanggal_pemasangan,
        ]);
        $record->save();

        //jika ada data geoJson masukan ke tabel geospasial
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            // $file->move('temp', $filename);
            FacadesStorage::disk('local')->put("temp/{$filename}", file_get_contents($file));
            $jalan_tol_id = $request->id_ruas;
            $record_id = $record->id;

            // Insert Data
            // $geojson = file_get_contents(public_path('temp/' . $filename));
            $geojson = FacadesStorage::disk('local')->get("temp/{$filename}");
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_patok_rmj_point (
                        jalan_tol_id, 
                        geom, 
                        layer,
                        created_at, 
                        updated_at,
                        record_aset_id
                    )
                    VALUES (
                        ?, 
                        st_GeomFromGeoJSON(?), 
                        ?,
                        ?, 
                        ?,
                        ?
                    )
                ", [
                    $jalan_tol_id,
                    json_encode($object["geometry"]),
                    $object["properties"]["layer"],
                    now(),
                    now(),
                    $record_id
                ]);
            }
            // unlink(public_path('temp/' . $filename));
            $geojson = FacadesStorage::disk('local')->get("temp/{$filename}");
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadPatokROWPoint(Request $request)
    {
        //Insert data ke tabel record_aset
        $record = new RecordAset;
        $record->fill([
            'id_ruas' => $request->id_ruas,
            'jenis_aset' => $request->jenis_aset,
            'tanggal_pemasangan' => $request->tanggal_pemasangan,
        ]);
        $record->save();

        //jika ada data geoJson masukan ke tabel geospasial
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            // $file->move('temp', $filename);
            FacadesStorage::disk('local')->put("temp/{$filename}", file_get_contents($file));
            $jalan_tol_id = $request->id_ruas;
            $record_id = $record->id;

            // Insert Data
            // $geojson = file_get_contents(public_path('temp/' . $filename));
            $geojson = FacadesStorage::disk('local')->get("temp/{$filename}");
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_patok_row_point (
                        jalan_tol_id, 
                        geom, 
                        layer,
                        created_at, 
                        updated_at,
                        record_aset_id
                    )
                    VALUES (
                        ?, 
                        ST_GeomFromGeoJSON(?), 
                        ?,
                        ?, 
                        ?,
                        ?
                    )
                ", [
                    $jalan_tol_id,
                    json_encode($object["geometry"]),
                    $object["properties"]["layer"],
                    now(),
                    now(),
                    $record_id
                ]);
            }
            // unlink(public_path('temp/' . $filename));
            $geojson = FacadesStorage::disk('local')->get("temp/{$filename}");
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadPitaKejutLine(Request $request)
    {
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            $file->move('temp', $filename);
            $jalan_tol_id = $request->ruas_id;

            // Insert Data
            $geojson = file_get_contents(public_path('temp/' . $filename));
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_pita_kejut_line (
                        jalan_tol_id, 
                        geom, 
                        layer,
                        created_at, 
                        updated_at
                    )
                    VALUES (
                        " . $jalan_tol_id . ", 
                        ST_GeomFromGeoJSON('" . json_encode($object["geometry"]) . "'), 
                        '" . $object["properties"]["layer"] . "',
                        '" . now() . "',
                        '" . now() . "'
                    )
                ");
            }
            unlink(public_path('temp/' . $filename));
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadRambuLalulintasPoint(Request $request)
    {
        //Insert data ke tabel record_aset
        $record = new RecordAset;
        $record->fill([
            'id_ruas' => $request->id_ruas,
            'jenis_aset' => $request->jenis_aset,
            'tanggal_pemasangan' => $request->tanggal_pemasangan,
        ]);
        $record->save();

        //jika ada data geoJson masukan ke tabel geospasial
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            // $file->move('temp', $filename);
            FacadesStorage::disk('local')->put("temp/{$filename}", file_get_contents($file));
            $jalan_tol_id = $request->id_ruas;
            $record_id = $record->id;

            // Insert Data
            // $geojson = file_get_contents(public_path('temp/' . $filename));
            $geojson = FacadesStorage::disk('local')->get("temp/{$filename}");
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_rambu_lalulintas_point (
                        jalan_tol_id, 
                        geom, 
                        layer,
                        created_at, 
                        updated_at,
                        record_aset_id
                    )
                    VALUES (
                        ?, 
                        ?, 
                        ?,
                        ?, 
                        ?,
                        ?
                    )
                ", [
                    $jalan_tol_id,
                    json_encode($object["geometry"]),
                    $object["properties"]["layer"],
                    now(),
                    now(),
                    $record_id
                ]);
            }
            // unlink(public_path('temp/' . $filename));
            $geojson = FacadesStorage::disk('local')->get("temp/{$filename}");
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadRambuPenunjukarahPoint(Request $request)
    {
        //Insert data ke tabel record_aset
        $record = new RecordAset;
        $record->fill([
            'id_ruas' => $request->id_ruas,
            'jenis_aset' => $request->jenis_aset,
            'tanggal_pemasangan' => $request->tanggal_pemasangan,
        ]);
        $record->save();

        //jika ada data geoJson masukan ke tabel geospasial
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            // $file->move('temp', $filename);
            FacadesStorage::disk('local')->put("temp/{$filename}", file_get_contents($file));
            $jalan_tol_id = $request->id_ruas;
            $record_id = $record->id;

            // Insert Data
            // $geojson = file_get_contents(public_path('temp/' . $filename));
            $geojson = FacadesStorage::disk('local')->get("temp/{$filename}");
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_rambu_penunjukarah_point (
                        jalan_tol_id, 
                        geom, 
                        layer,
                        created_at, 
                        updated_at,
                        record_aset_id
                    )
                    VALUES (
                        ?, 
                        ?, 
                        ?,
                        ?, 
                        ?,
                        ?
                    )
                ", [
                    $jalan_tol_id,
                    json_encode($object["geometry"]),
                    $object["properties"]["layer"],
                    now(),
                    now(),
                    $record_id
                ]);
            }
            // unlink(public_path('temp/' . $filename));
            $geojson = FacadesStorage::disk('local')->get("temp/{$filename}");
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadReflektorPoint(Request $request)
    {
        //Insert data ke tabel record_aset
        $record = new RecordAset;
        $record->fill([
            'id_ruas' => $request->id_ruas,
            'jenis_aset' => $request->jenis_aset,
            'tanggal_pemasangan' => $request->tanggal_pemasangan,
        ]);
        $record->save();

        //jika ada data geoJson masukan ke tabel geospasial
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            // $file->move('temp', $filename);
            FacadesStorage::disk('local')->put("temp/{$filename}", file_get_contents($file));
            $jalan_tol_id = $request->id_ruas;
            $record_id = $record->id;

            // Insert Data
            // $geojson = file_get_contents(public_path('temp/' . $filename));
            $geojson = FacadesStorage::disk('local')->get("temp/{$filename}");
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_reflektor_point (
                        jalan_tol_id, 
                        geom, 
                        layer,
                        created_at, 
                        updated_at,
                        record_aset_id
                    )
                    VALUES (
                        ?, 
                        ?, 
                        ?,
                        ?, 
                        ?,
                        ?                        
                    )
                ", [
                    $jalan_tol_id,
                    json_encode($object["geometry"]),
                    $object["properties"]["layer"],
                    now(),
                    now(),
                    $record_id
                ]);
            }
            // unlink(public_path('temp/' . $filename));
            $geojson = FacadesStorage::disk('local')->get("temp/{$filename}");
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadRiolLine(Request $request)
    {
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            $file->move('temp', $filename);
            $jalan_tol_id = $request->ruas_id;

            // Insert Data
            $geojson = file_get_contents(public_path('temp/' . $filename));
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_riol_line (
                        jalan_tol_id, 
                        geom, 
                        layer,
                        jenis_material,
                        ukuran_pokok,
                        kondisi,
                        created_at, 
                        updated_at
                    )
                    VALUES (
                        " . $jalan_tol_id . ", 
                        ST_GeomFromGeoJSON('" . json_encode($object["geometry"]) . "'), 
                        '" . $object["properties"]["layer"] . "',
                        '" . $object["properties"]["jns_mtrial"] . "',
                        '" . $object["properties"]["ukrn_pokok"] . "',
                        '" . $object["properties"]["kondisi"] . "',
                        '" . now() . "',
                        '" . now() . "'
                    )
                ");
            }
            unlink(public_path('temp/' . $filename));
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadRumahKabelPoint(Request $request)
    {
        //Insert data ke tabel record_aset
        $record = new RecordAset;
        $record->fill([
            'id_ruas' => $request->id_ruas,
            'jenis_aset' => $request->jenis_aset,
            'tanggal_pemasangan' => $request->tanggal_pemasangan,
        ]);
        $record->save();

        //jika ada data geoJson masukan ke tabel geospasial
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            // $file->move('temp', $filename);
            FacadesStorage::disk('local')->put("temp/{$filename}", file_get_contents($file));
            $jalan_tol_id = $request->id_ruas;
            $record_id = $record->id;

            // Insert Data
            // $geojson = file_get_contents(public_path('temp/' . $filename));
            $geojson = FacadesStorage::disk('local')->get("temp/{$filename}");
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_rumah_kabel_point (
                        jalan_tol_id, 
                        geom, 
                        layer,
                        created_at, 
                        updated_at,
                        record_aset_id
                    )
                    VALUES (
                        ?, 
                        ?, 
                        ?,
                        ?, 
                        ?,
                        ?
                    )
                ", [
                    $jalan_tol_id,
                    json_encode($object["geometry"]),
                    $object["properties"]["layer"],
                    now(),
                    now(),
                    $record_id
                ]);
            }
            // unlink(public_path('temp/' . $filename));
            $geojson = FacadesStorage::disk('local')->get("temp/{$filename}");
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadRuwasjaPoint(Request $request)
    {
        //Insert data ke tabel record_aset
        $record = new RecordAset;
        $record->fill([
            'id_ruas' => $request->id_ruas,
            'jenis_aset' => $request->jenis_aset,
            'tanggal_pemasangan' => $request->tanggal_pemasangan,
        ]);
        $record->save();

        //jika ada data geoJson masukan ke tabel geospasial
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            // $file->move('temp', $filename);
            FacadesStorage::disk('local')->put("temp/{$filename}", file_get_contents($file));
            $jalan_tol_id = $request->id_ruas;
            $record_id = $record->id;

            // Insert Data
            $geojson = file_get_contents(public_path('temp/' . $filename));
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_ruwasja_polygon (
                        jalan_tol_id, 
                        geom, 
                        keterangan,
                        created_at, 
                        updated_at,
                        record_aset_id
                    )
                    VALUES (
                        ?, 
                        ?, 
                        ?,
                        ?, 
                        ?,
                        ?
                    )
                ", [
                    $jalan_tol_id,
                    json_encode($object["geometry"]),
                    $object["properties"]["keterangan"],
                    now(),
                    now(),
                    $record_id
                ]);
            }
            // unlink(public_path('temp/' . $filename));
            $geojson = FacadesStorage::disk('local')->get("temp/{$filename}");
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadSaluranLine(Request $request)
    {
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            $file->move('temp', $filename);
            $jalan_tol_id = $request->ruas_id;

            // Insert Data
            $geojson = file_get_contents(public_path('temp/' . $filename));
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_saluran_line (
                        jalan_tol_id, 
                        geom, 
                        layer,
                        jenis_material,
                        kondisi,
                        panjang,
                        lebar,
                        tinggi,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        " . $jalan_tol_id . ", 
                        ST_GeomFromGeoJSON('" . json_encode($object["geometry"]) . "'), 
                        '" . $object["properties"]["layer"] . "',
                        '" . $object["properties"]["jns_mtrial"] . "',
                        '" . $object["properties"]["kondisi"] . "',
                        '" . $object["properties"]["panjang"] . "',
                        '" . $object["properties"]["lebar"] . "',
                        '" . $object["properties"]["tinggi"] . "',
                        '" . now() . "',
                        '" . now() . "'
                    )
                ");
            }
            unlink(public_path('temp/' . $filename));
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadSegmenKonstruksiPolygon(Request $request)
    {
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            $file->move('temp', $filename);
            $jalan_tol_id = $request->ruas_id;

            // Insert Data
            $geojson = file_get_contents(public_path('temp/' . $filename));
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_segmen_konstruksi_polygon (
                        jalan_tol_id, 
                        geom, 
                        bagian_jalan,
                        lebar,
                        created_at, 
                        updated_at
                    )
                    VALUES (
                        " . $jalan_tol_id . ", 
                        ST_GeomFromGeoJSON('" . json_encode($object["geometry"]) . "'), 
                        '" . $object["properties"]["bagian_jln"] . "',
                        '" . $object["properties"]["lebar"] . "',
                        '" . now() . "',
                        '" . now() . "'
                    )
                ");
            }
            unlink(public_path('temp/' . $filename));
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadSegmenLegerPolygon(Request $request)
    {
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            $file->move('temp', $filename);
            $jalan_tol_id = $request->ruas_id;

            // Insert Data
            $geojson = file_get_contents(public_path('temp/' . $filename));
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                $kode_leger = $object["properties"]["id_leger"];
                FacadesDB::statement("INSERT 
                    INTO spatial_segmen_leger_polygon (
                        jalan_tol_id, 
                        geom, 
                        id_leger,
                        km,
                        created_at, 
                        updated_at
                    )
                    VALUES (
                        " . $jalan_tol_id . ", 
                        ST_GeomFromGeoJSON('" . json_encode($object["geometry"]) . "'), 
                        '" . $kode_leger . "',
                        '" . $object["properties"]["km"] . "',
                        '" . now() . "',
                        '" . now() . "'
                    )
                ");

                $initial_leger = substr($kode_leger, 0, 1);
                switch ($initial_leger) {
                    case 'M':
                        $jenis_leger = 'Mainroad';
                        break;
                    case 'R':
                        $jenis_leger = 'Ramp';
                        break;
                    case 'A':
                        $jenis_leger = 'Akses';
                        break;
                }
                $existingLeger = LegerJalan::where('kode_leger', $kode_leger)->first();
                if (!$existingLeger) {
                    $leger = Leger::create([
                        'jalan_tol_id' => $jalan_tol_id,
                        'user_id' => Auth::user()->id,
                        'kode_leger' => $kode_leger,
                        'jenis_leger' => $jenis_leger,
                    ]);
                    LegerJalan::create([
                        'leger_id' => $leger->id,
                        'kode_leger' => $kode_leger,
                    ]);
                }
            }
            unlink(public_path('temp/' . $filename));
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadSegmenPerlengkapanPolygon(Request $request)
    {
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            $file->move('temp', $filename);
            $jalan_tol_id = $request->ruas_id;

            // Insert Data
            $geojson = file_get_contents(public_path('temp/' . $filename));
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_segmen_perlengkapan_polygon (
                        jalan_tol_id, 
                        geom, 
                        jalur,
                        created_at, 
                        updated_at
                    )
                    VALUES (
                        " . $jalan_tol_id . ", 
                        ST_GeomFromGeoJSON('" . json_encode($object["geometry"]) . "'), 
                        '" . $object["properties"]["jalur"] . "',
                        '" . now() . "',
                        '" . now() . "'
                    )
                ");
            }
            unlink(public_path('temp/' . $filename));
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadSegmenSeksiPolygon(Request $request)
    {
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            $file->move('temp', $filename);
            $jalan_tol_id = $request->ruas_id;

            // Insert Data
            $geojson = file_get_contents(public_path('temp/' . $filename));
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_segmen_seksi_polygon (
                        jalan_tol_id, 
                        geom, 
                        no_ruas,
                        nama_ruas,
                        seksi,
                        keterangan,
                        km_awal,
                        km_akhir,
                        sta_awal,
                        sta_akhir,
                        x_awal,
                        x_akhir,
                        y_awal,
                        y_akhir,
                        z_awal,
                        z_akhir,
                        deskripsi_awal,
                        deskripsi_akhir,
                        created_at, 
                        updated_at
                    )
                    VALUES (
                        " . $jalan_tol_id . ", 
                        ST_GeomFromGeoJSON('" . json_encode($object["geometry"]) . "'), 
                        '" . $object["properties"]["no_ruas"] . "',
                        '" . $object["properties"]["nama_ruas"] . "',
                        '" . $object["properties"]["seksi"] . "',
                        '" . $object["properties"]["keterangan"] . "',
                        '" . $object["properties"]["km_awal"] . "',
                        '" . $object["properties"]["km_akhir"] . "',
                        '" . $object["properties"]["sta_awal"] . "',
                        '" . $object["properties"]["sta_akhir"] . "',
                        '" . $object["properties"]["x_awal"] . "',
                        '" . $object["properties"]["x_akhir"] . "',
                        '" . $object["properties"]["y_awal"] . "',
                        '" . $object["properties"]["y_akhir"] . "',
                        '" . $object["properties"]["z_awal"] . "',
                        '" . $object["properties"]["z_akhir"] . "',
                        '" . $object["properties"]["dskrpsi_al"] . "',
                        '" . $object["properties"]["dskrpsi_ar"] . "',
                        '" . now() . "',
                        '" . now() . "'
                    )
                ");
            }
            unlink(public_path('temp/' . $filename));
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadSegmenTolPolygon(Request $request)
    {
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            $file->move('temp', $filename);
            $jalan_tol_id = $request->ruas_id;

            // Insert Data
            $geojson = file_get_contents(public_path('temp/' . $filename));
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_segmen_tol_polygon (
                        jalan_tol_id, 
                        geom, 
                        segmen_tol,
                        nama_segmen,
                        created_at, 
                        updated_at
                    )
                    VALUES (
                        " . $jalan_tol_id . ", 
                        ST_GeomFromGeoJSON('" . json_encode($object["geometry"]) . "'), 
                        '" . $object["properties"]["sgm_tol"] . "',
                        '" . $object["properties"]["nama_sgmn"] . "',
                        '" . now() . "',
                        '" . now() . "'
                    )
                ");
            }
            unlink(public_path('temp/' . $filename));
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadStaTextPoint(Request $request)
    {
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            $file->move('temp', $filename);
            $jalan_tol_id = $request->ruas_id;

            // Insert Data
            $geojson = file_get_contents(public_path('temp/' . $filename));
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_sta_text_point (
                        jalan_tol_id, 
                        geom, 
                        sta,
                        created_at, 
                        updated_at
                    )
                    VALUES (
                        " . $jalan_tol_id . ", 
                        ST_GeomFromGeoJSON('" . json_encode($object["geometry"]) . "'), 
                        '" . $object["properties"]["sta"] . "',
                        '" . now() . "',
                        '" . now() . "'
                    )
                ");
            }
            unlink(public_path('temp/' . $filename));
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadSungaiLine(Request $request)
    {
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            $file->move('temp', $filename);
            $jalan_tol_id = $request->ruas_id;

            // Insert Data
            $geojson = file_get_contents(public_path('temp/' . $filename));
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_sungai_line (
                        jalan_tol_id, 
                        geom, 
                        layer,
                        created_at, 
                        updated_at
                    )
                    VALUES (
                        " . $jalan_tol_id . ", 
                        ST_GeomFromGeoJSON('" . json_encode($object["geometry"]) . "'), 
                        '" . $object["properties"]["layer"] . "',
                        '" . now() . "',
                        '" . now() . "'
                    )
                ");
            }
            unlink(public_path('temp/' . $filename));
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadTeleponBawahtanahLine(Request $request)
    {
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            $file->move('temp', $filename);
            $jalan_tol_id = $request->ruas_id;

            // Insert Data
            $geojson = file_get_contents(public_path('temp/' . $filename));
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_telepon_bawahtanah_line (
                        jalan_tol_id, 
                        geom, 
                        layer,
                        created_at, 
                        updated_at
                    )
                    VALUES (
                        " . $jalan_tol_id . ", 
                        ST_GeomFromGeoJSON('" . json_encode($object["geometry"]) . "'), 
                        '" . $object["properties"]["layer"] . "',
                        '" . now() . "',
                        '" . now() . "'
                    )
                ");
            }
            unlink(public_path('temp/' . $filename));
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadTiangListrikPoint(Request $request)
    {
        //Insert data ke tabel record_aset
        $record = new RecordAset;
        $record->fill([
            'id_ruas' => $request->id_ruas,
            'jenis_aset' => $request->jenis_aset,
            'tanggal_pemasangan' => $request->tanggal_pemasangan,
        ]);
        $record->save();

        //jika ada data geoJson masukan ke tabel geospasial
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            // $file->move('temp', $filename);
            FacadesStorage::disk('local')->put("temp/{$filename}", file_get_contents($file));
            $jalan_tol_id = $request->id_ruas;
            $record_id = $record->id;

            // Insert Data
            // $geojson = file_get_contents(public_path('temp/' . $filename));
            $geojson = FacadesStorage::disk('local')->get("temp/{$filename}");
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_tiang_listrik_point (
                        jalan_tol_id, 
                        geom, 
                        layer,
                        created_at, 
                        updated_at,
                        record_aset_id
                    )
                    VALUES (
                        ?, 
                        ?, 
                        ?,
                        ?, 
                        ?,
                        ?
                    )
                ", [
                    $jalan_tol_id,
                    json_encode($object["geometry"]),
                    $object["properties"]["layer"],
                    now(),
                    now(),
                    $record_id
                ]);
            }
            // unlink(public_path('temp/' . $filename));
            $geojson = FacadesStorage::disk('local')->get("temp/{$filename}");
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadTiangTeleponPoint(Request $request)
    {
        //Insert data ke tabel record_aset
        $record = new RecordAset;
        $record->fill([
            'id_ruas' => $request->id_ruas,
            'jenis_aset' => $request->jenis_aset,
            'tanggal_pemasangan' => $request->tanggal_pemasangan,
        ]);
        $record->save();

        //jika ada data geoJson masukan ke tabel geospasial
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            // $file->move('temp', $filename);
            FacadesStorage::disk('local')->put("temp/{$filename}", file_get_contents($file));
            $jalan_tol_id = $request->id_ruas;
            $record_id = $record->id;

            // Insert Data
            // $geojson = file_get_contents(public_path('temp/' . $filename));
            $geojson = FacadesStorage::disk('local')->get("temp/{$filename}");
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_tiang_telepon_point (
                        jalan_tol_id, 
                        geom, 
                        layer,
                        created_at, 
                        updated_at,
                        record_aset_id
                    )
                    VALUES (
                        ?, 
                        ?, 
                        ?,
                        ?, 
                        ?,
                        ?
                    )
                ", [
                    $jalan_tol_id,
                    json_encode($object["geometry"]),
                    $object["properties"]["layer"],
                    now(),
                    now(),
                    $record_id
                ]);
            }
            // unlink(public_path('temp/' . $filename));
            $geojson = FacadesStorage::disk('local')->get("temp/{$filename}");
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    public function uploadVMSPoint(Request $request)
    {
        //Insert data ke tabel record_aset
        $record = new RecordAset;
        $record->fill([
            'id_ruas' => $request->id_ruas,
            'jenis_aset' => $request->jenis_aset,
            'tanggal_pemasangan' => $request->tanggal_pemasangan,
        ]);
        $record->save();

        //jika ada data geoJson masukan ke tabel geospasial
        if ($request->geojson) {
            $file = $request->file('geojson');
            $filename = $this->generateRandomString();
            // $file->move('temp', $filename);
            FacadesStorage::disk('local')->put("temp/{$filename}", file_get_contents($file));
            $jalan_tol_id = $request->id_ruas;
            $record_id = $record->id;

            // Insert Data
            // $geojson = file_get_contents(public_path('temp/' . $filename));
            $geojson = FacadesStorage::disk('local')->get("temp/{$filename}");
            $objects = json_decode($geojson, true);
            foreach ($objects['features'] as $object) {
                FacadesDB::statement("INSERT 
                    INTO spatial_vms_point (
                        jalan_tol_id, 
                        geom, 
                        layer,
                        created_at, 
                        updated_at,
                        record_aset_id
                    )
                    VALUES (
                        
                    )
                ", [
                    $jalan_tol_id,
                    json_encode($object["geometry"]),
                    $object["properties"]["layer"],
                    now(),
                    now(),
                    $record_id
                ]);
            }
            // unlink(public_path('temp/' . $filename));
            $geojson = FacadesStorage::disk('local')->get("temp/{$filename}");
            return response()->json(['message' => 'Success']);
        } else {
            abort(403);
        }
    }

    private function generateRandomString($length = 30)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
