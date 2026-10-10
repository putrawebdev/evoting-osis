<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pemilih;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Menampilkan halaman form login
    public function showLoginForm()
    {
        return view('login');
    }

    // Memproses data login (FR-1)
    public function login(Request $request)
    {
        $request->validate([
            'nis' => 'required|string'
        ]);

        $pemilih = Pemilih::where('nis', $request->nis)->first();

        // FR-5: NIS tidak terdaftar ditolak[cite: 2]
        if (!$pemilih) {
            return back()->withErrors(['nis' => 'NIS tidak ditemukan.']); //[cite: 2]
        }

        // Syarat Guru & FR-2: Tolak login jika sudah memilih[cite: 2]
        if ($pemilih->sudah_memilih || !is_null($pemilih->paslon_dipilih)) {
            return back()->withErrors(['nis' => 'Anda sudah menggunakan hak pilih.']); //[cite: 2]
        }

        // Login session manual
        Auth::login($pemilih);
        $request->session()->regenerate();

        return redirect()->intended('/pilih');
    }

    // Memproses logout otomatis (FR-4)
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login')->with('success', 'Sesi berakhir otomatis. Terima kasih!'); //[cite: 2, 3]
    }
}