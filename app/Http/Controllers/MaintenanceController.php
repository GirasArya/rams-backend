<?php

namespace App\Http\Controllers;

use App\Models\RecordPemeliharaan;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
class MaintenanceController extends Controller
{

    public function getMaintenance()
    {
        try {
            $records = RecordPemeliharaan::all();
            return response()->json(
                $records->map(function ($record) {
                    return [
                        'id' => $record->id,
                        'nama' => $record->nama,
                        'km_awal' => $record->km_awal,
                        'km_akhir' => $record->km_akhir,
                        'jalur' => $record->jalur,
                        'bagian_jalan' => $record->bagian_jalan,
                        'periode_awal' => $record->periode_awal,
                        'periode_akhir' => $record->periode_akhir,
                        'jenis_pemeliharaan' => $record->jenis_pemeliharaan,
                        'biaya_pemeliharaan' => $record->biaya_pemeliharaan,
                        'biaya_impor' => $record->biaya_impor,
                        'keterangan_pemeliharaan' => $record->keterangan_pemeliharaan,
                        'total_biaya' => $record->total_biaya,
                        'created_at' => $record->created_at,
                        'updated_at' => $record->updated_at
                    ];
                }),
                200
            );

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving records: ' . $e->getMessage()], 500);
        }
    }

    public function getMaintenanceById($id)
    {
        try {
            $record = RecordPemeliharaan::find($id);

            if (!$record) {
                return response()->json(['message' => 'Record not found'], 404);
            }

            return response()->json([
                'id' => $record->id,
                'nama' => $record->nama,
                'km_awal' => $record->km_awal,
                'km_akhir' => $record->km_akhir,
                'jalur' => $record->jalur,
                'bagian_jalan' => $record->bagian_jalan,
                'periode_awal' => $record->periode_awal,
                'periode_akhir' => $record->periode_akhir,
                'jenis_pemeliharaan' => $record->jenis_pemeliharaan,
                'biaya_pemeliharaan' => $record->biaya_pemeliharaan,
                'biaya_impor' => $record->biaya_impor,
                'keterangan_pemeliharaan' => $record->keterangan_pemeliharaan,
                'total_biaya' => $record->total_biaya,
                'created_at' => $record->created_at,
                'updated_at' => $record->updated_at
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving record: ' . $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {

        // Simulasi kalkulasi anggaran
        $periodeAwal = Carbon::parse($request->periode_awal);
        $periodeAkhir = Carbon::parse($request->periode_akhir);
        $selisihTahun = $periodeAkhir->diffInYears($periodeAwal);
        $biayaPemeliharaan = $request->biaya_pemeliharaan;
        $biayaImpor = $request->biaya_impor;
        $inflasi = 0.05;
        $kursDolar = 15000;
        $pajak = 0.10;
        $totalBiaya = 0;

        for ($t = 1; $t <= $selisihTahun; $t++) {
            $biayaPemeliharaanTahunan = $biayaPemeliharaan * pow(1 + $inflasi, $t); //BPt = BP * (1 + i)^t 
            $totalBiayaPemeliharaanPajak = $biayaPemeliharaanTahunan * (1 + $pajak); //TBPt = BPt * (1 + p)
            $totalBiayaImpor = $biayaImpor * $kursDolar; //TBI = BI * k
            $totalBiayaImporTahunan = $totalBiayaImpor / $selisihTahun; //TBIt = TBI / t
            $totalBiayaKeselurahan = $totalBiayaImporTahunan + $totalBiayaPemeliharaanPajak; //TCt =  TBIt+TBPt
            $totalBiaya += $totalBiayaKeselurahan; // TCP = Σ TCt
        }

        try {
            $record = new RecordPemeliharaan;
            $record->fill([
                'nama' => $request->nama,
                'km_awal' => $request->km_awal,
                'km_akhir' => $request->km_akhir,
                'jalur' => $request->jalur,
                'bagian_jalan' => $request->bagian_jalan,
                'periode_awal' => $request->periode_awal,
                'periode_akhir' => $request->periode_akhir,
                'jenis_pemeliharaan' => $request->jenis_pemeliharaan,
                'biaya_pemeliharaan' => $request->biaya_pemeliharaan,
                'biaya_impor' => $request->biaya_impor,
                'keterangan_pemeliharaan' => $request->keterangan_pemeliharaan,
                'total_biaya' => $totalBiaya
            ]);
            $record->save();
            return response()->json([
                'message' => 'Data berhasil disimpan',
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function deleteMaintenance($id)
    {
        try {
            $record = RecordPemeliharaan::find($id);

            if (!$record) {
                return response()->json(['message' => 'Record not found'], 404);
            }

            $record->delete();

            return response()->json(['message' => 'Record deleted successfully'], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting record: ' . $e->getMessage()], 500);
        }
    }
}
