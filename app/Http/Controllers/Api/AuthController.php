<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pemilih;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate(['nisn' => 'required|string']);

        $siswa = Pemilih::where('nis', $request->nisn)->first();

        if (!$siswa) {
            return response()->json(['reason' => 'notfound', 'message' => 'NISN tidak terdaftar sebagai pemilih.'], 404);
        }

        if ($siswa->sudah_memilih == 1) {
            return response()->json(['reason' => 'voted', 'message' => 'NISN ini sudah memberikan suara. Terima kasih!'], 403);
        }

        return response()->json([
            'student' => [
                'nisn' => $siswa->nis,
                'name' => $siswa->nama
            ]
        ], 200);
    }

    public function adminLogin(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        $admin = DB::table('admin')->where('username', $request->username)->first();

        if ($admin && Hash::check($request->password, $admin->password_hash)) {
            return response()->json([
                'message' => 'Login sukses',
                'role' => $admin->role
            ], 200);
        }

        return response()->json(['message' => 'Username atau password salah.'], 401);
    }
}