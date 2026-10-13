<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Barryvdh\DomPDF\Facade\Pdf;

class PemilihController extends Controller
{
    public function dashboardStats()
    {
        $total_pemilih = DB::table('pemilih')->count();
        $sudah_memilih = DB::table('pemilih')->where('sudah_memilih', 1)->count();
        $belum_memilih = $total_pemilih - $sudah_memilih;

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

        $kelas_stats = DB::table('pemilih')
            ->select(
                'kelas',
                DB::raw('COUNT(*) as total_siswa'),
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
            'kelas' => $kelas_stats
        ]);
    }

    public function index()
    {
        $pemilih = DB::table('pemilih')->orderBy('nama', 'asc')->get();
        return response()->json(['status' => 'success', 'data' => $pemilih]);
    }

    public function store(Request $request)
    {
        $cekNis = DB::table('pemilih')->where('nis', $request->nis)->first();
        if ($cekNis) {
            return response()->json(['message' => 'NIS sudah terdaftar!'], 400);
        }

        DB::table('pemilih')->insert([
            'nis' => $request->nis,
            'nama' => $request->nama,
            'kelas' => $request->kelas,
            'sudah_memilih' => 0,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // ---> MENCATAT RIWAYAT TAMBAH PEMILIH (SATUAN) <---
        DB::table('riwayat')->insert([
            'aktor' => 'Admin', 
            'aksi' => 'Pemilih',
            'detail' => 'Menambahkan data pemilih baru: ' . $request->nama . ' (' . $request->nis . ')',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['status' => 'success']);
    }

    public function import(Request $request)
    {
        if (!$request->hasFile('file')) {
            return response()->json(['message' => 'File tidak ditemukan'], 400);
        }

        $file = $request->file('file');
        $data = array_map('str_getcsv', file($file->getRealPath()));
        array_shift($data);

        $berhasil = 0;
        $gagal = 0;

        foreach ($data as $row) {
            if (count($row) >= 2) {
                $baris = count($row) == 1 ? explode(';', $row[0]) : $row;

                if (isset($baris[0]) && isset($baris[1])) {
                    $nis = trim($baris[0]);
                    $nama = trim($baris[1]);
                    $kelas = isset($baris[2]) ? trim($baris[2]) : null;

                    if (!empty($nis) && !empty($nama)) {
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

        // ---> MENCATAT RIWAYAT IMPORT EXCEL <---
        DB::table('riwayat')->insert([
            'aktor' => 'Admin', 
            'aksi' => 'Pemilih',
            'detail' => "Mengimpor data CSV/Excel: $berhasil data ditambahkan, $gagal dilewati.",
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Selesai: $berhasil ditambahkan, $gagal dilewati (sudah ada)."
        ]);
    }

    public function belumMemilih()
    {
        $belumMemilih = DB::table('pemilih')->where('sudah_memilih', 0)->orderBy('nama', 'asc')->get();
        return response()->json(['status' => 'success', 'data' => $belumMemilih]);
    }

    public function exportExcel()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap E-Voting');

        $styleHeader = ['font' => ['bold' => true, 'size' => 14, 'name' => 'Arial'], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];
        $styleTitle = ['font' => ['bold' => true, 'name' => 'Arial'], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFECFDF5']], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
        $styleTableHeader = ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'name' => 'Arial'], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF10B981']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
        $styleBorder = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];

        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A1', 'LAPORAN HASIL E-VOTING OSIS SMK METLAND CIBITUNG');
        $sheet->getStyle('A1:E1')->applyFromArray($styleHeader);

        $sheet->mergeCells('A2:E2');
        $sheet->setCellValue('A2', 'Waktu Cetak Laporan: ' . now()->format('d M Y H:i:s'));
        $sheet->getStyle('A2:E2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

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

        $row++;
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

        $sheet->getColumnDimension('A')->setWidth(12);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Laporan_Hasil_EVoting_OSIS.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    public function exportPdf()
    {
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

        $pdf = Pdf::loadView('pdf.laporan', $data);
        return $pdf->download('Laporan_Hasil_EVoting_OSIS.pdf');
    }

    public function riwayat()
    {
        $riwayat = DB::table('riwayat')->orderBy('created_at', 'desc')->get();
        return response()->json(['status' => 'success', 'data' => $riwayat]);
    }
}