<?php

namespace App\Http\Controllers;

use App\Models\AktivitasPemeliharaan;
use App\Models\RecordPemeliharaan;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB as FacadesDB;

class MaintenanceController extends Controller
{

    public function getMaintenance()
    {
        try {
            $records = RecordPemeliharaan::with('ruasJalan', 'aktivitasKegiatan')->latest('id')->get();

            $formattedRecords = $records->map(function ($record) {
                $ruasJalan = $record->ruasJalan ? [
                    'id' => $record->ruasJalan->id,
                    'nama' => $record->ruasJalan->nama
                ] : null;

                $aktivitasKegiatan = $record->aktivitasKegiatan->map(function ($aktivitas) {
                    return [
                        'id' => $aktivitas->id,
                        'nama_kegiatan' => $aktivitas->nama_kegiatan,
                        'jenis_pemeliharaan' => $aktivitas->jenis_pemeliharaan,
                        'frekuensi_kegiatan_per_tahun' => $aktivitas->frekuensi_kegiatan_per_tahun,
                        'jumlah_tenaga_kerja' => $aktivitas->jumlah_tenaga_kerja,
                        'anggaran_kegiatan_per_meter' => $aktivitas->anggaran_kegiatan_per_meter,
                    ];
                });


                return [
                    'id' => $record->id,
                    'id_ruas' => $record->id_ruas,
                    'ruas_jalan' => $ruasJalan ? [$ruasJalan] : [],
                    'aktivitasKegiatan' => $aktivitasKegiatan,
                    'nama' => $record->nama,
                    'km_awal' => $record->km_awal,
                    'km_akhir' => $record->km_akhir,
                    'jalur' => $record->jalur,
                    'periode_awal' => $record->periode_awal,
                    'periode_akhir' => $record->periode_akhir,
                    'total_biaya' => $record->total_biaya,
                    'created_at' => $record->created_at,
                    'updated_at' => $record->updated_at,
                ];
            });

            return response()->json($formattedRecords, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving records: ' . $e->getMessage()], 500);
        }
    }

    public function getMaintenanceById(Request $request)
    {
        try {
            $record = RecordPemeliharaan::with('ruasJalan', 'aktivitasKegiatan')->find($request->id);

            if (!$record) {
                return response()->json(['message' => 'Record not found'], 404);
            }

            $ruasJalan = $record->ruasJalan ? [
                'id' => $record->ruasJalan->id,
                'nama' => $record->ruasJalan->nama
            ] : null;

            $aktivitasKegiatan = $record->aktivitasKegiatan->map(function ($aktivitas) {
                return [
                    'id' => $aktivitas->id,
                    'nama_kegiatan' => $aktivitas->nama_kegiatan,
                    'jenis_pemeliharaan' => $aktivitas->jenis_pemeliharaan,
                    'frekuensi_kegiatan_per_tahun' => $aktivitas->frekuensi_kegiatan_per_tahun,
                    'jumlah_tenaga_kerja' => $aktivitas->jumlah_tenaga_kerja,
                    'anggaran_kegiatan_per_meter' => $aktivitas->anggaran_kegiatan_per_meter,
                ];
            });

            $formattedRecord = [
                'id' => $record->id,
                'id_ruas' => $record->id_ruas,
                'ruas_jalan' => $ruasJalan ? [$ruasJalan] : [],
                'aktivitasKegiatan' => $aktivitasKegiatan,
                'nama' => $record->nama,
                'km_awal' => $record->km_awal,
                'km_akhir' => $record->km_akhir,
                'jalur' => $record->jalur,
                'periode_awal' => $record->periode_awal,
                'periode_akhir' => $record->periode_akhir,
                'total_biaya' => $record->total_biaya,
                'created_at' => $record->created_at,
                'updated_at' => $record->updated_at,
            ];

            return response()->json($formattedRecord, 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving record: ' . $e->getMessage()], 500);
        }
    }

    public function getRecentMaintenance()
    {
        try {
            $records = RecordPemeliharaan::with('ruasJalan')->latest('id')->take(2)->get();

            $formattedRecords = $records->map(function ($record) {
                $ruasJalan = $record->ruasJalan ? [
                    'id' => $record->ruasJalan->id,
                    'nama' => $record->ruasJalan->nama
                ] : null;

                return [
                    'id' => $record->id,
                    'id_ruas' => $record->id_ruas,
                    'ruas_jalan' => $ruasJalan ? [$ruasJalan] : [],
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
                    'updated_at' => $record->updated_at,
                ];
            });

            return response()->json($formattedRecords, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving records: ' . $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        // Simulasi kalkulasi anggaran
        $detailAktivitasList = $request->input('detail_aktivitas', []);
        $periodeAwal = Carbon::parse($request->periode_awal);
        $periodeAkhir = Carbon::parse($request->periode_akhir);
        $selisihJarak = selisihJarak($request->km_awal, $request->km_akhir);
        $selisihTahun = $periodeAkhir->diffInYears($periodeAwal);
        $inflasi = 0.05; //rata-rata inflasi 10 tahun terakhir
        $totalBiaya = 0;

        for ($t = 0; $t <= $selisihTahun; $t++) {
            foreach ($detailAktivitasList as $aktivitas) {
                $anggaranPerMeter = $aktivitas['anggaranKegiatanPerMeter'];
                $frekuensi = $aktivitas['frekuensi'];
                $tenagaKerja = $aktivitas['tenagaKerja'];

                // Perhitungkan inflasi untuk tahun ke-t
                $anggaranDenganInflasi = $anggaranPerMeter * pow(1 + $inflasi, $t);

                // Total biaya untuk aktivitas ini di tahun ke-t
                $biayaTahunan = $anggaranDenganInflasi * $selisihJarak * $frekuensi;

                // Akumulasi ke total biaya keseluruhan
                $totalBiaya += $biayaTahunan;
            }
        }

        try {
            FacadesDB::beginTransaction();
            $recordPemeliharaan = new RecordPemeliharaan;
            $recordPemeliharaan->fill([
                'nama' => $request->nama,
                'id_ruas' => $request->id_ruas,
                'km_awal' => $request->km_awal,
                'km_akhir' => $request->km_akhir,
                'jalur' => $request->jalur,
                // 'indeks_iri' => $request->indeks_iri,
                'periode_awal' => $request->periode_awal,
                'periode_akhir' => $request->periode_akhir,
                'total_biaya' => $totalBiaya,
            ]);
            $recordPemeliharaan->save();
            $idPemeliharaan = $recordPemeliharaan->id;

            foreach ($detailAktivitasList as $aktivitas) {
                $detail = new AktivitasPemeliharaan;
                $detail->id_record_pemeliharaan = $idPemeliharaan;
                $detail->jenis_pemeliharaan = $aktivitas['jenis_pemeliharaan'];
                $detail->nama_kegiatan = $aktivitas['namaKegiatan'];
                $detail->frekuensi_kegiatan_per_tahun = $aktivitas['frekuensi'];
                $detail->jumlah_tenaga_kerja = $aktivitas['tenagaKerja'];
                $detail->anggaran_kegiatan_per_meter = $aktivitas['anggaranKegiatanPerMeter'];
                $detail->save();
            }
            FacadesDB::commit();
            return response()->json([
                'message' => 'Data berhasil disimpan',
            ], 201);
        } catch (\Exception $e) {
            FacadesDB::rollBack();
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

            AktivitasPemeliharaan::where('id_record_pemeliharaan', $id)->delete();

            $record->delete();

            return response()->json(['message' => 'Record and related activities deleted successfully'], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting record: ' . $e->getMessage()], 500);
        }
    }

}

function konversiKmToMeter($kmString)
{
    if (empty($kmString)) {
        throw new \Exception("KM input kosong");
    }

    $kmString = str_replace(' ', '', $kmString); // Hapus spasi
    $parts = explode('+', $kmString);

    if (count($parts) !== 2) {
        throw new \Exception("Format KM tidak valid: " . $kmString);
    }

    $km = (int) $parts[0];
    $meterTambahan = (int) $parts[1];

    return ($km * 1000) + $meterTambahan;
}

function selisihJarak($kmAwal, $kmAkhir)
{
    $meterAwal = konversiKmToMeter($kmAwal);
    $meterAkhir = konversiKmToMeter($kmAkhir);

    return abs($meterAkhir - $meterAwal);
}
