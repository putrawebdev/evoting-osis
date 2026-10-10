<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Pemilih;
use App\Models\Kandidat;

class AdminController extends Controller
{
    // Menampilkan form login admin
    public function showLogin()
    {
        return view('admin.login');
    }

    // Memproses login admin
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string'
        ]);

        // Menggunakan guard 'admin' yang baru kita buat
        if (Auth::guard('admin')->attempt(['username' => $credentials['username'], 'password' => $credentials['password']])) {
            $request->session()->regenerate();
            return redirect()->intended('/admin/dashboard');
        }

        return back()->withErrors(['username' => 'Username atau password salah.']);
    }

    // Logout admin
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/admin/login');
    }

    // Menampilkan Dashboard Admin (FR-14, FR-16)
    public function dashboard()
    {
        // Menghitung statistik kartu ringkasan[cite: 3]
        $total_pemilih = Pemilih::count();
        $sudah_memilih = Pemilih::where('sudah_memilih', true)->count();
        $belum_memilih = $total_pemilih - $sudah_memilih;
        $partisipasi = $total_pemilih > 0 ? round(($sudah_memilih / $total_pemilih) * 100, 2) : 0;

        // Mengambil data paslon dan menghitung perolehan suara masing-masing
        $kandidat = Kandidat::withCount('pemilih')->get();

        return view('admin.dashboard', compact(
            'total_pemilih', 
            'sudah_memilih', 
            'belum_memilih', 
            'partisipasi',
            'kandidat'
        ));
    }
}