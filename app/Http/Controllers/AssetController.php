<?php
namespace App\Http\Controllers;
use App\Models\RecordAset;
use DB;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function getRecordAsset()
    {
        try {
            $records = RecordAset::with('ruasJalan')->latest('id')->get();

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
                    'titik_km' => $record->titik_km,
                    'masa_hidup' => $record->masa_hidup,
                    'status' => $record->status,
                    'tanggal_pemasangan' => $record->tanggal_pemasangan,
                ];
            });

            return response()->json($formattedRecords, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving records: ' . $e->getMessage()], 500);
        }
    }

    public function storeAssetRecord(Request $request)
    {
        try {
            $record = new RecordAset;
            $record->fill([
                'id_ruas' => $request->id_ruas,
                'jenis_aset' => $request->jenis_aset,
                'titik_km' => $request->titik_km,
                'masa_hidup' => $request->masa_hidup,
                'status' => $request->status,
                'tanggal_pemasangan' => $request->tanggal_pemasangan,
            ]);
            $record->save();
            return response()->json([
                'message' => 'Data berhasil disimpan',
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function deleteAssetRecord($id)
    {
        try {
            $record = RecordAset::find($id);

            if (!$record) {
                return response()->json(['message' => 'Record not found'], 404);
            }

            $record->delete();

            return response()->json(['message' => 'Record deleted successfully'], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting record: ' . $e->getMessage()], 500);
        }
    }

    public function getFilteredAssets(Request $request)
    {
        $status = $request->query('status');
        $aset = $request->query('jenis_aset');
        $tanggal = $request->query('tanggal_pemasangan');
        
        $filterAset = DB::table('record_aset')
            ->select('id', 'id_ruas', 'jenis_aset', 'titik_km', 'masa_hidup', 'status', 'tanggal_pemasangan');
    
        if ($status !== null) {
            $filterAset->where('status', $status);
        }
    
        if ($aset !== null) {
            $filterAset->where('jenis_aset', $aset);
        }
    
        if ($tanggal !== null) {
            $filterAset->where('tanggal_pemasangan', $tanggal);
        }
    
        $results = $filterAset->get();
    
        return response()->json($results);
    }
}