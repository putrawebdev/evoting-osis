<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Pemilih;
use App\Models\Kandidat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Barryvdh\DomPDF\Facade\Pdf;

// ========== API Routes for React Frontend ==========

// ========== SISWA LOGIN & VOTING ==========
// 1. Endpoint untuk login siswa menggunakan NISN
Route::post('/login', function (Request $request) {
    // Frontend React mengirim data dengan nama 'nisn'
    $request->validate([
        'nisn' => 'required|string'
    ]);

    // Kita cocokkan dengan kolom 'nis' di database Laravel kamu
    $siswa = Pemilih::where('nis', $request->nisn)->first();

    if (!$siswa) {
        return response()->json([
            'reason' => 'notfound',
            'message' => 'NISN tidak terdaftar sebagai pemilih.'
        ], 404);
    }

    // Cek kolom 'sudah_memilih', jika 1 berarti akses ditolak
    if ($siswa->sudah_memilih == 1) {
        return response()->json([
            'reason' => 'voted',
            'message' => 'NISN ini sudah memberikan suara. Terima kasih!'
        ], 403);
    }

    // Jika berhasil masuk, balas dengan format yang diminta React
    return response()->json([
        'student' => [
            'nisn' => $siswa->nis,
            'name' => $siswa->nama
        ]
    ], 200);
});

// 2. Endpoint untuk mengirim daftar kandidat ke React
Route::get('/kandidat', function () {
    return response()->json([
        'status' => 'success',
        'data' => Kandidat::all()
    ], 200);
});

// 3. Endpoint untuk menerima ID paslon yang dicoblos
Route::post('/vote', function (Request $request) {
    $request->validate([
        'nis' => 'required',
        'id_kandidat' => 'required'
    ]);

    // Menggunakan Transaction & lockForUpdate untuk mencegah siswa menekan tombol 2x bersamaan
    DB::beginTransaction();
    try {
        $pemilih = Pemilih::where('nis', $request->nis)->lockForUpdate()->first();

        if (!$pemilih) {
            DB::rollBack();
            return response()->json(['message' => 'Pemilih tidak ditemukan'], 404);
        }

        // Cek lagi untuk memastikan siswa belum memilih
        if ($pemilih->sudah_memilih == 1) {
            DB::rollBack();
            return response()->json(['message' => 'Anda sudah memberikan suara!'], 403);
        }

        // Simpan suara ke database
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
});

// ========== ADMIN API ==========
// 1. Endpoint untuk login admin
Route::post('/admin/login', function (Request $request) {
    $request->validate([
        'username' => 'required',
        'password' => 'required'
    ]);

    // Cari admin berdasarkan username di tabel 'admin'
    $admin = DB::table('admin')->where('username', $request->username)->first();

    // Verifikasi hash password
    if ($admin && Hash::check($request->password, $admin->password_hash)) {
        return response()->json([
            'message' => 'Login sukses',
            'role' => $admin->role
        ], 200);
    }

    return response()->json([
        'message' => 'Username atau password salah.'
    ], 401);
});

// 2. Endpoint untuk mengirim data perolehan suara ke Dashboard Admin
Route::get('/admin/dashboard-stats', function () {
    // 1. Hitung partisipasi pemilih total
    $total_pemilih = DB::table('pemilih')->count();
    $sudah_memilih = DB::table('pemilih')->where('sudah_memilih', 1)->count();
    $belum_memilih = $total_pemilih - $sudah_memilih;

    // 2. Rekap perolehan suara masing-masing kandidat
    $kandidat = DB::table('kandidat')->get();
    $suara_paslon = [];
    foreach ($kandidat as $k) {
        $jumlah_suara = DB::table('pemilih')->where('paslon_dipilih', $k->id)->count();
        $suara_paslon[] = [
            'id' => $k->id,
            'nomor' => $k->no_paslon,
            'ketua' => $k->nama_ketua,
            'wakil' => $k->nama_wakil,
            'suara' => $jumlah_suara
        ];
    }

    // 3. BARU: Hitung partisipasi per kelas
    // Kita kelompokkan (groupBy) berdasarkan kelas, lalu hitung total siswa dan yang sudah memilih
    // 3. BARU: Hitung partisipasi per kelas (Versi PostgreSQL)
    $kelas_stats = DB::table('pemilih')
        ->select(
            'kelas',
            DB::raw('COUNT(*) as total_siswa'),
            // Gunakan CASE WHEN agar PostgreSQL bisa menghitung boolean dengan aman
            DB::raw('SUM(CASE WHEN sudah_memilih = true THEN 1 ELSE 0 END) as sudah_memilih')
        )
        ->whereNotNull('kelas')
        ->where('kelas', '!=', '')
        ->groupBy('kelas')
        ->orderBy('kelas', 'asc')
        ->get();

    return response()->json([
        'status' => 'success',
        'partisipasi' => [
            'total' => $total_pemilih,
            'sudah' => $sudah_memilih,
            'belum' => $belum_memilih,
            'persentase' => $total_pemilih > 0 ? round(($sudah_memilih / $total_pemilih) * 100) : 0
        ],
        'kandidat' => $suara_paslon,
        'kelas' => $kelas_stats // Mengirim data kelas ke React
    ]);
});

// ========== ADMIN CRUD KANDIDAT ==========

// 1. Ambil data kandidat untuk Admin (lengkap dengan jumlah suara)
Route::get('/admin/kandidat', function () {
    $kandidat = DB::table('kandidat')->get();
    foreach ($kandidat as $k) {
        $k->jumlah_suara = DB::table('pemilih')->where('paslon_dipilih', $k->id)->count();
    }
    return response()->json(['status' => 'success', 'data' => $kandidat]);
});

// 2. Tambah Paslon Baru
Route::post('/admin/kandidat', function (Request $request) {
    $fotoPath = null;
    if ($request->hasFile('foto')) {
        $fotoPath = $request->file('foto')->store('kandidat', 'public');
    }

    // Cari nomor paslon terakhir
    $lastPaslon = DB::table('kandidat')->orderBy('no_paslon', 'desc')->first();
    $noPaslon = $lastPaslon ? $lastPaslon->no_paslon + 1 : 1;

    DB::table('kandidat')->insert([
        'no_paslon' => $noPaslon,
        'nama_ketua' => $request->nama_ketua,
        'nama_wakil' => $request->nama_wakil,
        'visi' => $request->visi,
        'misi' => $request->misi,
        'proggram' => $request->program,
        'foto' => $fotoPath,
        'created_at' => now(),
        'updated_at' => now()
    ]);

    return response()->json(['status' => 'success']);
});

// 3. Edit/Update Paslon
Route::post('/admin/kandidat/{id}', function (Request $request, $id) {
    $kandidat = DB::table('kandidat')->where('id', $id)->first();
    if (!$kandidat) return response()->json(['message' => 'Not found'], 404);

    $updateData = [
        'nama_ketua' => $request->nama_ketua,
        'nama_wakil' => $request->nama_wakil,
        'visi' => $request->visi,
        'misi' => $request->misi,
        'proggram' => $request->program,
        'updated_at' => now()
    ];

    if ($request->hasFile('foto')) {
        // Hapus foto lama jika ada
        if ($kandidat->foto) Storage::disk('public')->delete($kandidat->foto);
        // Simpan foto baru
        $updateData['foto'] = $request->file('foto')->store('kandidat', 'public');
    }

    DB::table('kandidat')->where('id', $id)->update($updateData);
    return response()->json(['status' => 'success']);
});

// 4. Hapus Paslon
Route::delete('/admin/kandidat/{id}', function ($id) {
    $kandidat = DB::table('kandidat')->where('id', $id)->first();
    if ($kandidat) {
        if ($kandidat->foto) Storage::disk('public')->delete($kandidat->foto);
        DB::table('kandidat')->where('id', $id)->delete();
    }
    return response()->json(['status' => 'success']);
});

// ========== ADMIN CRUD PEMILIH ==========
// 1. Ambil semua daftar pemilih
Route::get('/admin/pemilih', function () {
    // Mengambil data pemilih, diurutkan berdasarkan nama sesuai abjad
    $pemilih = DB::table('pemilih')->orderBy('nama', 'asc')->get();
    
    return response()->json([
        'status' => 'success',
        'data' => $pemilih
    ]);
});

// 2. Tambah Pemilih Baru secara manual
Route::post('/admin/pemilih', function (Request $request) {
    // Cek apakah NIS sudah ada
    $cekNis = DB::table('pemilih')->where('nis', $request->nis)->first();
    
    if ($cekNis) {
        return response()->json(['message' => 'NIS sudah terdaftar!'], 400);
    }

    DB::table('pemilih')->insert([
        'nis' => $request->nis,
        'nama' => $request->nama,
        'kelas' => $request->kelas, // <-- Sekarang baris ini diaktifkan
        'sudah_memilih' => 0,
        'created_at' => now(),
        'updated_at' => now() 
    ]);

    return response()->json(['status' => 'success']);
});

// 3. Impor Data Pemilih (CSV)
Route::post('/admin/pemilih/import', function (Request $request) {
    if (!$request->hasFile('file')) {
        return response()->json(['message' => 'File tidak ditemukan'], 400);
    }

    $file = $request->file('file');
    $path = $file->getRealPath();
    
    // Baca file CSV
    $data = array_map('str_getcsv', file($path));
    
    // Hapus baris pertama (asumsi baris pertama adalah judul kolom: NIS, Nama, Kelas)
    array_shift($data); 

    $berhasil = 0;
    $gagal = 0;

    foreach ($data as $row) {
        // Pastikan minimal ada data NIS (kolom 1) dan Nama (kolom 2)
        if (count($row) >= 2) { 
            // Sesuaikan dengan delimiter CSV (kadang menggunakan koma, kadang titik koma)
            // Jika datanya menyatu dalam 1 array string panjang, kita pecah pakai titik koma
            $baris = count($row) == 1 ? explode(';', $row[0]) : $row;

            if (isset($baris[0]) && isset($baris[1])) {
                $nis = trim($baris[0]);
                $nama = trim($baris[1]);
                $kelas = isset($baris[2]) ? trim($baris[2]) : null;

                if (!empty($nis) && !empty($nama)) {
                    // Cek agar NIS tidak duplikat
                    $cekNis = DB::table('pemilih')->where('nis', $nis)->first();
                    if (!$cekNis) {
                        DB::table('pemilih')->insert([
                            'nis' => $nis,
                            'nama' => $nama,
                            'kelas' => $kelas,
                            'sudah_memilih' => 0,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                        $berhasil++;
                    } else {
                        $gagal++;
                    }
                }
            }
        }
    }

    return response()->json([
        'status' => 'success',
        'message' => "Selesai: $berhasil ditambahkan, $gagal dilewati (sudah ada)."
    ]);
});

// Ambil daftar siswa yang BELUM memilih (sudah_memilih = 0)
Route::get('/admin/belum-memilih', function () {
    $belumMemilih = DB::table('pemilih')
                    ->where('sudah_memilih', 0)
                    ->orderBy('nama', 'asc') // Urutkan berdasarkan nama
                    ->get();
                    
    return response()->json([
        'status' => 'success',
        'data' => $belumMemilih
    ]);
});

// Ekspor Laporan Hasil Pemilihan ke Excel (CSV)
Route::get('/admin/export/excel', function () {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Rekap E-Voting');

    // --- STYLING ARRAY ---
    $styleHeader = [
        'font' => ['bold' => true, 'size' => 14, 'name' => 'Arial'],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
    ];
    
    $styleTitle = [
        'font' => ['bold' => true, 'name' => 'Arial'],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFECFDF5']],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ];

    $styleTableHeader = [
        'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'name' => 'Arial'],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF10B981']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ];

    $styleBorder = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];

    // --- BAGIAN 1: HEADER ---
    $sheet->mergeCells('A1:E1');
    $sheet->setCellValue('A1', 'LAPORAN HASIL E-VOTING OSIS SMK METLAND CIBITUNG');
    $sheet->getStyle('A1:E1')->applyFromArray($styleHeader);

    $sheet->mergeCells('A2:E2');
    $sheet->setCellValue('A2', 'Waktu Cetak Laporan: ' . now()->format('d M Y H:i:s'));
    $sheet->getStyle('A2:E2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // --- BAGIAN 2: REKAP PARTISIPASI ---
    $total = DB::table('pemilih')->count();
    $sudah = DB::table('pemilih')->where('sudah_memilih', 1)->count();
    $belum = $total - $sudah;
    
    $pct_sudah = $total > 0 ? round(($sudah / $total) * 100) : 0;
    $pct_belum = $total > 0 ? round(($belum / $total) * 100) : 0;

    $sheet->mergeCells('A4:D4');
    $sheet->setCellValue('A4', 'RINGKASAN PARTISIPASI PEMILIH');
    $sheet->getStyle('A4:D4')->applyFromArray($styleTitle);

    $data_partisipasi = [
        ['Total Pemilih Terdaftar', $total, 'Siswa (100%)'],
        ['Sudah Menggunakan Hak Pilih', $sudah, "Siswa ($pct_sudah%)"],
        ['Belum / Tidak Memilih', $belum, "Siswa ($pct_belum%)"]
    ];

    $row = 5;
    foreach ($data_partisipasi as $d) {
        $sheet->mergeCells("A{$row}:B{$row}");
        $sheet->setCellValue("A{$row}", $d[0]);
        $sheet->setCellValue("C{$row}", $d[1]);
        $sheet->setCellValue("D{$row}", $d[2]);
        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleBorder);
        $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;
    }

    $row++; // Spasi

    // --- BAGIAN 3: PEROLEHAN SUARA ---
    $sheet->mergeCells("A{$row}:E{$row}");
    $sheet->setCellValue("A{$row}", 'PEROLEHAN SUARA KANDIDAT');
    $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleTitle);
    $row++;

    $headers = ['No. Urut', 'Nama Ketua', 'Nama Wakil', 'Total Suara', 'Persentase'];
    $col = 'A';
    foreach ($headers as $h) {
        $sheet->setCellValue("{$col}{$row}", $h);
        $col++;
    }
    $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleTableHeader);
    $row++;

    $kandidat = DB::table('kandidat')->orderBy('no_paslon', 'asc')->get();
    foreach ($kandidat as $k) {
        $suara = DB::table('pemilih')->where('paslon_dipilih', $k->id)->count();
        $pct_suara = $sudah > 0 ? round(($suara / $sudah) * 100) : 0;
        
        $sheet->setCellValue("A{$row}", $k->no_paslon);
        $sheet->setCellValue("B{$row}", $k->nama_ketua);
        $sheet->setCellValue("C{$row}", $k->nama_wakil);
        $sheet->setCellValue("D{$row}", $suara);
        $sheet->setCellValue("E{$row}", $pct_suara . '%');
        
        $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleBorder);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("D{$row}:E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;
    }

    // Mengatur lebar kolom agar rapi
    $sheet->getColumnDimension('A')->setWidth(12);
    $sheet->getColumnDimension('B')->setWidth(30);
    $sheet->getColumnDimension('C')->setWidth(30);
    $sheet->getColumnDimension('D')->setWidth(15);
    $sheet->getColumnDimension('E')->setWidth(15);

    // Proses download file XLSX
    $writer = new Xlsx($spreadsheet);
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="Laporan_Hasil_EVoting_OSIS.xlsx"');
    header('Cache-Control: max-age=0');
    
    $writer->save('php://output');
    exit;
});

Route::get('/admin/export/pdf', function () {
    $total = DB::table('pemilih')->count();
    $sudah = DB::table('pemilih')->where('sudah_memilih', 1)->count();
    
    $kandidat = DB::table('kandidat')->orderBy('no_paslon', 'asc')->get();
    foreach ($kandidat as $k) {
        $k->suara = DB::table('pemilih')->where('paslon_dipilih', $k->id)->count();
    }

    $data = [
        'total' => $total,
        'sudah' => $sudah,
        'belum' => $total - $sudah,
        'kandidat' => $kandidat
    ];

    // Load file HTML/Blade tadi menjadi PDF
    $pdf = Pdf::loadView('pdf.laporan', $data);
    
    // Download otomatis
    return $pdf->download('Laporan_Hasil_EVoting_OSIS.pdf');
});

// Ambil jejak aktivitas (Riwayat) dari yang paling baru
Route::get('/admin/riwayat', function () {
    $riwayat = DB::table('riwayat')->orderBy('created_at', 'desc')->get();
    
    return response()->json([
        'status' => 'success',
        'data' => $riwayat
    ]);
});