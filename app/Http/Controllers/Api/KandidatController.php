<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kandidat;
use App\Models\Pemilih;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;


class KandidatController extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'data' => Kandidat::all()
        ], 200);
    }
    public function getFoto($filename)
    {
        $path = storage_path('app/public/kandidat/' . $filename);

        if (!file_exists($path)) {
            return response()->json(['message' => 'Foto tidak ditemukan'], 404);
        }

        return response()->file($path);
    }

    public function vote(Request $request)
    {
        $request->validate([
            'nis' => 'required',
            'id_kandidat' => 'required'
        ]);

        DB::beginTransaction();
        try {
            $pemilih = Pemilih::where('nis', $request->nis)->lockForUpdate()->first();

            if (!$pemilih) {
                DB::rollBack();
                return response()->json(['message' => 'Pemilih tidak ditemukan'], 404);
            }

            if ($pemilih->sudah_memilih == 1) {
                DB::rollBack();
                return response()->json(['message' => 'Anda sudah memberikan suara!'], 403);
            }

            $pemilih->paslon_dipilih = $request->id_kandidat;
            $pemilih->sudah_memilih = 1;
            $pemilih->waktu_memilih = now();
            $pemilih->save();

            DB::commit();
            return response()->json(['message' => 'Suara berhasil disimpan!'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan sistem'], 500);
        }
    }

    public function adminGetKandidat()
    {
        $kandidat = DB::table('kandidat')->get();
        foreach ($kandidat as $k) {
            $k->jumlah_suara = DB::table('pemilih')->where('paslon_dipilih', $k->id)->count();
        }
        return response()->json(['status' => 'success', 'data' => $kandidat]);
    }

    public function store(Request $request)
    {
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('kandidat', 'public');
        }

        $lastPaslon = DB::table('kandidat')->orderBy('no_paslon', 'desc')->first();
        $noPaslon = $lastPaslon ? $lastPaslon->no_paslon + 1 : 1;

        DB::table('kandidat')->insert([
            'no_paslon' => $noPaslon,
            'nama_ketua' => $request->nama_ketua,
            'nama_wakil' => $request->nama_wakil,
            'visi' => $request->visi,
            'misi' => $request->misi,
            'proggram' => $request->program ?? null, // Fleksibel: jika kosong diset null
            'foto' => $fotoPath,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['status' => 'success']);
    }

    public function update(Request $request, $id)
    {
        $kandidat = DB::table('kandidat')->where('id', $id)->first();
        if (!$kandidat) return response()->json(['message' => 'Not found'], 404);

        $updateData = [
            'nama_ketua' => $request->nama_ketua,
            'nama_wakil' => $request->nama_wakil,
            'visi' => $request->visi,
            'misi' => $request->misi,
            'proggram' => $request->program ?? null, // Fleksibel: jika kosong diset null
            'updated_at' => now()
        ];

        if ($request->hasFile('foto')) {
            if ($kandidat->foto) Storage::disk('public')->delete($kandidat->foto);
            $updateData['foto'] = $request->file('foto')->store('kandidat', 'public');
        }

        DB::table('kandidat')->where('id', $id)->update($updateData);
        return response()->json(['status' => 'success']);
    }

    public function destroy($id)
    {
        $kandidat = DB::table('kandidat')->where('id', $id)->first();
        if ($kandidat) {
            if ($kandidat->foto) Storage::disk('public')->delete($kandidat->foto);
            DB::table('kandidat')->where('id', $id)->delete();
        }
        return response()->json(['status' => 'success']);
    }
}