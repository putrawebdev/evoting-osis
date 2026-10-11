<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\KandidatController;
use App\Http\Controllers\Api\PemilihController;

// ========== AUTHENTICATION ==========
Route::post('/login', [AuthController::class, 'login']);
Route::post('/admin/login', [AuthController::class, 'adminLogin']);

// ========== KANDIDAT & VOTING ==========
Route::get('/kandidat', [KandidatController::class, 'index']);
Route::post('/vote', [KandidatController::class, 'vote']);

// Admin CRUD Kandidat
Route::get('/admin/kandidat', [KandidatController::class, 'adminGetKandidat']);
Route::post('/admin/kandidat', [KandidatController::class, 'store']);
Route::post('/admin/kandidat/{id}', [KandidatController::class, 'update']);
Route::delete('/admin/kandidat/{id}', [KandidatController::class, 'destroy']);

// ========== PEMILIH, DASHBOARD, & LAPORAN ==========
Route::get('/admin/dashboard-stats', [PemilihController::class, 'dashboardStats']);
Route::get('/admin/pemilih', [PemilihController::class, 'index']);
Route::post('/admin/pemilih', [PemilihController::class, 'store']);
Route::post('/admin/pemilih/import', [PemilihController::class, 'import']);
Route::get('/admin/belum-memilih', [PemilihController::class, 'belumMemilih']);

// Export & Riwayat
Route::get('/admin/export/excel', [PemilihController::class, 'exportExcel']);
Route::get('/admin/export/pdf', [PemilihController::class, 'exportPdf']);
Route::get('/admin/riwayat', [PemilihController::class, 'riwayat']);