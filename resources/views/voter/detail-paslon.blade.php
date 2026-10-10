<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Paslon No. {{ $kandidat->no_paslon }} — E-Voting OSIS</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 40px 20px; }
        .container { max-width: 700px; margin: 0 auto; background: white; border-radius: 12px; border: 1px solid #e2e8f0; padding: 30px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .btn-back { display: inline-block; margin-bottom: 20px; color: #2563eb; text-decoration: none; font-weight: 600; font-size: 14px; }
        .photo-container { width: 100%; height: 350px; border-radius: 8px; overflow: hidden; background: #f1f5f9; margin-bottom: 20px; }
        .photo-container img { width: 100%; height: 100%; object-fit: cover; }
        h1 { margin-top: 0; font-size: 24px; color: #0f172a; }
        .section-title { font-size: 15px; font-weight: bold; color: #475569; margin-top: 20px; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        .box-content { background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 14px; line-height: 1.6; white-space: pre-line; }
        .btn-choose { display: block; width: 100%; text-align: center; background: #2563eb; color: white; padding: 12px; border-radius: 6px; text-decoration: none; font-weight: bold; margin-top: 30px; box-sizing: border-box; }
    </style>
</head>
<body>
    <div class="container">
        <a href="{{ route('voter.index') }}" class="btn-back">← Kembali ke Daftar Kandidat</a>

        <div class="photo-container">
            <img src="{{ asset('storage/' . $kandidat->foto) }}" alt="Paslon {{ $kandidat->no_paslon }}">
        </div>

        <h1>Paslon No. {{ $kandidat->no_paslon }}: {{ $kandidat->nama_ketua }} & {{ $kandidat->nama_wakil }}</h1>

        <div class="section-title">Visi</div>
        <div class="box-content">{{ $kandidat->visi }}</div>

        <div class="section-title">Misi</div>
        <div class="box-content">{{ $kandidat->misi }}</div>

        <div class="section-title">Program Unggulan</div>
        <div class="box-content">{{ $kandidat->proggram ?? 'Belum ada program khusus yang dicantumkan.' }}</div>

        <!-- Tombol Pilih Langsung dari Halaman Detail -->
        <form action="{{ route('voter.submit', $kandidat->id) }}" method="POST" onsubmit="return confirm('Yakin ingin memilih Paslon No. {{ $kandidat->no_paslon }}?')">
            @csrf
            <button type="submit" class="btn-choose">
                ✅ Gunakan Hak Suara untuk Paslon Ini
            </button>
        </form>
    </div>
</body>
</html>