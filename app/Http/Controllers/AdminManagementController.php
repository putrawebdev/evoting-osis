<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pemilih;
use App\Models\Kandidat;
use Illuminate\Support\Facades\Storage;

class AdminManagementController extends Controller
{
    // ================= MANAJEMEN USER / PEMILIH (FR-19) =================
    public function indexPemilih()
    {
        $pemilih = Pemilih::paginate(15);
        return view('admin.pemilih', compact('pemilih'));
    }

    public function storePemilih(Request $request)
    {
        $request->validate([
            'nis' => 'required|string|unique:pemilih,nis',
            'nama' => 'required|string',
            'kelas' => 'required|string',
        ]);

        Pemilih::create([
            'nis' => $request->nis,
            'nama' => $request->nama,
            'kelas' => $request->kelas,
            'sudah_memilih' => false
        ]);

        return back()->with('success', 'Data pemilih berhasil ditambahkan.');
    }

    // Menampilkan data siswa untuk diedit
    public function editPemilih($id)
    {
        $editPemilih = Pemilih::findOrFail($id);
        $pemilih = Pemilih::paginate(15);
        return view('admin.pemilih', compact('editPemilih', 'pemilih'));
    }

    // Memproses update data siswa
    public function updatePemilih(Request $request, $id)
    {
        $pemilih = Pemilih::findOrFail($id);

        $request->validate([
            'nis' => 'required|string|unique:pemilih,nis,' . $id,
            'nama' => 'required|string',
            'kelas' => 'required|string',
        ]);

        $pemilih->update([
            'nis' => $request->nis,
            'nama' => $request->nama,
            'kelas' => $request->kelas,
        ]);

        return redirect()->route('admin.pemilih')->with('success', 'Data pemilih berhasil diperbarui.');
    }

    public function destroyPemilih($id)
    {
        Pemilih::findOrFail($id)->delete();
        return back()->with('success', 'Data pemilih berhasil dihapus.');
    }

    // Memproses import data pemilih dari file CSV/Excel
    public function importPemilih(Request $request)
    {
        $request->validate([
            'file_csv' => 'required|file|mimes:csv,txt,xlsx,xls|max:2048'
        ]);

        $file = $request->file('file_csv');
        $path = $file->getRealPath();

        // Membaca baris file CSV
        if (($handle = fopen($path, 'r')) !== FALSE) {
            $rowNo = 0;
            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                $rowNo++;
                // Lewati baris pertama jika itu adalah Header (misal: NIS, Nama, Kelas)
                if ($rowNo == 1 && (strtolower($data[0]) == 'nis' || strtolower($data[0]) == '"nis"')) {
                    continue;
                }

                // Pastikan format baris minimal memiliki NIS, Nama, Kelas
                if (!empty($data[0]) && !empty($data[1]) && !empty($data[2])) {
                    $nis = trim($data[0]);
                    $nama = trim($data[1]);
                    $kelas = trim($data[2]);

                    // Masukkan ke database atau abaikan jika NIS sudah ada
                    Pemilih::updateOrCreate(
                        ['nis' => $nis],
                        [
                            'nama' => $nama,
                            'kelas' => $kelas,
                            'sudah_memilih' => false
                        ]
                    );
                }
            }
            fclose($handle);
        }

        return back()->with('success', 'Data DPT pemilih berhasil di-import dari file!');
    }

    // ================= MANAJEMEN KANDIDAT / PASLON (FR-18) =================
    public function indexKandidat()
    {
        $kandidat = Kandidat::all();
        return view('admin.kandidat', compact('kandidat'));
    }

    public function storeKandidat(Request $request)
    {
        $request->validate([
            'no_paslon' => 'required|integer|unique:kandidat,no_paslon',
            'nama_ketua' => 'required|string',
            'nama_wakil' => 'required|string',
            'visi' => 'required|string',
            'misi' => 'required|string',
            'proggram' => 'nullable|string',
            'foto' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        // Simpan foto ke folder public/storage/kandidat
        $fotoPath = $request->file('foto')->store('kandidat', 'public');

        Kandidat::create([
            'no_paslon' => $request->no_paslon,
            'nama_ketua' => $request->nama_ketua,
            'nama_wakil' => $request->nama_wakil,
            'visi' => $request->visi,
            'misi' => $request->misi,
            'proggram' => $request->proggram,
            'foto' => $fotoPath
        ]);

        return back()->with('success', 'Kandidat berhasil ditambahkan.');
    }

    public function destroyKandidat($id)
    {
        $kandidat = Kandidat::findOrFail($id);
        
        // Cek apakah kandidat sudah dipilih dalam pemilu (tidak boleh dihapus setelah pemilihan dimulai/ada suara)
        if ($kandidat->pemilih()->count() > 0) {
            return back()->withErrors(['error' => 'Kandidat yang sudah memiliki suara tidak dapat dihapus!']);
        }

        if ($kandidat->foto) {
            Storage::disk('public')->delete($kandidat->foto);
        }
        
        $kandidat->delete();
        return back()->with('success', 'Kandidat berhasil dihapus.');
    }

    // Menampilkan halaman/data kandidat untuk diedit
    public function editKandidat($id)
    {
        $editKandidat = Kandidat::findOrFail($id);
        $kandidat = Kandidat::all();
        return view('admin.kandidat', compact('editKandidat', 'kandidat'));
    }

    // Memproses update data kandidat
    public function updateKandidat(Request $request, $id)
    {
        $kandidat = Kandidat::findOrFail($id);

        $request->validate([
            'no_paslon' => 'required|integer|unique:kandidat,no_paslon,' . $id,
            'nama_ketua' => 'required|string',
            'nama_wakil' => 'required|string',
            'visi' => 'required|string',
            'misi' => 'required|string',
            'proggram' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048' // Foto opsional saat edit (bisa tidak diisi jika tidak ingin diganti)
        ]);

        $dataUpdate = [
            'no_paslon' => $request->no_paslon,
            'nama_ketua' => $request->nama_ketua,
            'nama_wakil' => $request->nama_wakil,
            'visi' => $request->visi,
            'misi' => $request->misi,
            'proggram' => $request->proggram,
        ];

        // Jika ada upload foto baru
        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($kandidat->foto && Storage::disk('public')->exists($kandidat->foto)) {
                Storage::disk('public')->delete($kandidat->foto);
            }
            // Simpan foto baru
            $dataUpdate['foto'] = $request->file('foto')->store('kandidat', 'public');
        }

        $kandidat->update($dataUpdate);

        return redirect()->route('admin.kandidat')->with('success', 'Data kandidat berhasil diperbarui.');
    }

    // ================= EKSPOR DATA & BERITA ACARA (FR-20) =================
    public function exportExcel()
    {
        $filename = "laporan-data-pemilih-osis-" . date('Y-m-d') . ".xls";
        $pemilih = Pemilih::with('kandidat')->get();

        $headers = [
            "Content-type" => "application/vnd.ms-excel",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($pemilih) {
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            echo '<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
            echo '<style>
                    .title { font-size: 16px; font-weight: bold; text-align: center; }
                    .subtitle { font-size: 13px; font-weight: bold; text-align: center; margin-bottom: 15px; }
                    th { background-color: #2563eb; color: white; font-weight: bold; text-align: center; border: 1px solid #000; padding: 8px; }
                    td { border: 1px solid #d1d5db; padding: 6px; text-align: left; }
                    .center { text-align: center; }
                  </style>';
            echo '</head><body>';
            
            echo '<table>';
            // Kop Laporan
            echo '<tr><td colspan="5" class="title">LAPORAN DATA PEMILIH & STATUS PEMILIHAN OSIS</td></tr>';
            echo '<tr><td colspan="5" class="subtitle">SMK METLAND CIBITUNG - PERIODE 2026/2027</td></tr>';
            echo '<tr><td colspan="5"></td></tr>'; // Baris kosong

            // Header Tabel
            echo '<tr>';
            echo '<th>No</th>';
            echo '<th>NIS / ID User</th>';
            echo '<th>Nama Lengkap</th>';
            echo '<th>Kelas</th>';
            echo '<th>Status Memilih</th>';
            echo '</tr>';

            if ($pemilih->isEmpty()) {
                echo '<tr><td colspan="5" class="center">Data tidak ditemukan.</td></tr>';
            } else {
                $no = 1;
                foreach ($pemilih as $p) {
                    echo '<tr>';
                    echo '<td class="center">' . $no++ . '</td>';
                    echo '<td>' . $p->nis . '</td>';
                    echo '<td>' . htmlspecialchars($p->nama) . '</td>';
                    echo '<td>' . htmlspecialchars($p->kelas) . '</td>';
                    echo '<td class="center">' . ($p->sudah_memilih ? 'Sudah Memilih' : 'Belum Memilih') . '</td>';
                    echo '</tr>';
                }
            }

            echo '</table>';
            echo '</body></html>';
        };

        return response()->stream($callback, 200, $headers);
    }
}
