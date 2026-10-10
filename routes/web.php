<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminManagementController;

// Rute Admin
Route::prefix('admin')->group(function () {
    // Tamu admin (belum login)
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminController::class, 'showLogin'])->name('admin.login');
        Route::post('/login', [AdminController::class, 'login']);
    });

    // Admin yang sudah login
    Route::middleware('auth:admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard'); //[cite: 8]
        // Manajemen Pemilih (User)
        Route::get('/pemilih', [AdminManagementController::class, 'indexPemilih'])->name('admin.pemilih');
        Route::post('/pemilih', [AdminManagementController::class, 'storePemilih']);
        Route::delete('/pemilih/{id}', [AdminManagementController::class, 'destroyPemilih']);
        Route::get('/pemilih/{id}/edit', [AdminManagementController::class, 'editPemilih'])->name('admin.pemilih.edit');
        Route::put('/pemilih/{id}', [AdminManagementController::class, 'updatePemilih'])->name('admin.pemilih.update');
        Route::post('/pemilih/import', [AdminManagementController::class, 'importPemilih'])->name('admin.pemilih.import');
        
        // Manajemen Kandidat
        Route::get('/kandidat', [AdminManagementController::class, 'indexKandidat'])->name('admin.kandidat');
        Route::post('/kandidat', [AdminManagementController::class, 'storeKandidat']);
        Route::delete('/kandidat/{id}', [AdminManagementController::class, 'destroyKandidat']);
        Route::get('/kandidat/{id}/edit', [AdminManagementController::class, 'editKandidat'])->name('admin.kandidat.edit');
        Route::put('/kandidat/{id}', [AdminManagementController::class, 'updateKandidat'])->name('admin.kandidat.update');

        // Ekspor Hasil
        Route::get('/export/excel', [AdminManagementController::class, 'exportExcel'])->name('admin.export.excel');
        Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');
    });
});

// Redirect halaman utama ke form login
Route::get('/', function () {
    return redirect('/login');
});

// Grup route untuk tamu (belum login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,5'); //[cite: 2]
});

// Grup route khusus untuk pemilih yang sudah login
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Rute utama bilik suara / pemilihan
    Route::get('/pilih', [VoteController::class, 'index'])->name('voter.index');
    
    // Rute halaman detail paslon
    Route::get('/pilih/kandidat/{id}', [VoteController::class, 'showKandidatDetail'])->name('voter.kandidat.detail');
    
    // Rute aksi simpan pilihan suara (menggunakan submitVote sesuai controller kamu)
    Route::post('/pilih/submit', [VoteController::class, 'submitVote'])->name('voter.submit');
});