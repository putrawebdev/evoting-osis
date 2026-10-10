<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kandidat;
use App\Models\Pemilih;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class VoteController extends Controller
{
    // Menampilkan halaman daftar paslon (FR-6, FR-7)
    public function index()
    {
        $kandidat = Kandidat::all(); //[cite: 3]
        $pemilih = Auth::user(); //[cite: 2]
        
        return view('pilih', compact('kandidat', 'pemilih'));
    }

    // Memproses penyimpanan suara secara atomik (FR-10)
    public function submitVote(Request $request)
    {
        $request->validate([
            'kandidat_id' => 'required|exists:kandidat,id'
        ]);

        $user = Auth::user();

        try {
            DB::transaction(function () use ($user, $request) {
                // Kunci baris data pemilih ini selama proses transaksi berlangsung[cite: 6]
                $pemilih = Pemilih::where('id', $user->id)->lockForUpdate()->first();

                // Validasi ulang: Pastikan kolom benar-benar masih kosong[cite: 3, 7]
                if (!is_null($pemilih->paslon_dipilih) || $pemilih->sudah_memilih) {
                    throw new \Exception('Suara ganda terdeteksi. Anda sudah memilih.'); //[cite: 7]
                }

                // Update data pemilih[cite: 4]
                $pemilih->paslon_dipilih = $request->kandidat_id;
                $pemilih->sudah_memilih = true;
                $pemilih->waktu_memilih = now();
                $pemilih->save();
            });

            // Jika sukses, logout otomatis (sesuai FR-11 & kriteria penerimaan)[cite: 3, 8]
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->with('success', 'Suara berhasil disimpan! Terima kasih.'); //[cite: 3]

        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // Menampilkan halaman detail visi, misi, dan program paslon
    public function showKandidatDetail($id)
    {
        $kandidat = Kandidat::findOrFail($id);
        return view('voter.detail-paslon', compact('kandidat'));
    }
}
