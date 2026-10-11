<!DOCTYPE html>
<html>
<head>
    <title>Laporan Hasil E-Voting</title>
    <style>
        body { font-family: sans-serif; color: #333; line-height: 1.5; }
        .header { text-align: center; border-bottom: 2px solid #10B981; padding-bottom: 10px; margin-bottom: 20px; }
        .header h2 { margin: 0; color: #10B981; text-transform: uppercase; }
        .header p { margin: 5px 0 0; font-size: 14px; color: #666; }
        
        .section-title { background-color: #ECFDF5; padding: 8px; font-weight: bold; margin-top: 20px; border-left: 4px solid #10B981; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; font-size: 14px; }
        th { background-color: #10B981; color: white; text-align: center; }
        .text-center { text-align: center; }
        
        .summary-box { width: 100%; margin-top: 10px; }
        .summary-box td { border: none; padding: 5px; border-bottom: 1px dashed #eee; }
    </style>
</head>
<body>

    <div class="header">
        <h2>Laporan Hasil E-Voting OSIS</h2>
        <h2>SMK Metland Cibitung</h2>
        <p>Waktu Cetak: {{ \Carbon\Carbon::now()->format('d M Y H:i:s') }}</p>
    </div>

    <div class="section-title">Ringkasan Partisipasi Pemilih</div>
    <table class="summary-box">
        <tr>
            <td>Total Pemilih Terdaftar</td>
            <td class="text-center"><b>{{ $total }}</b> Siswa</td>
            <td>(100%)</td>
        </tr>
        <tr>
            <td>Sudah Menggunakan Hak Pilih</td>
            <td class="text-center"><b>{{ $sudah }}</b> Siswa</td>
            <td>({{ $total > 0 ? round(($sudah / $total) * 100) : 0 }}%)</td>
        </tr>
        <tr>
            <td>Belum / Tidak Memilih</td>
            <td class="text-center"><b>{{ $belum }}</b> Siswa</td>
            <td>({{ $total > 0 ? round(($belum / $total) * 100) : 0 }}%)</td>
        </tr>
    </table>

    <div class="section-title">Perolehan Suara Pasangan Calon</div>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Ketua & Wakil</th>
                <th>Total Suara</th>
                <th>Persentase</th>
            </tr>
        </thead>
        <tbody>
            @foreach($kandidat as $k)
            <tr>
                <td class="text-center">{{ $k->no_paslon }}</td>
                <td><b>{{ $k->nama_ketua }}</b><br><span style="font-size:12px; color:#555;">& {{ $k->nama_wakil }}</span></td>
                <td class="text-center"><b>{{ $k->suara }}</b></td>
                <td class="text-center">{{ $sudah > 0 ? round(($k->suara / $sudah) * 100) : 0 }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>