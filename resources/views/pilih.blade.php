<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilik Suara — E-Voting OSIS</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 40px 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; }
        .header h1 { margin: 0 0 10px 0; font-size: 26px; color: #0f172a; }
        .header p { color: #64748b; font-size: 15px; margin: 0; }
        .candidates-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
        .card { background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; padding: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between; }
        .photo-box { width: 100%; height: 220px; border-radius: 8px; overflow: hidden; background: #f1f5f9; margin-bottom: 15px; }
        .photo-box img { width: 100%; height: 100%; object-fit: cover; }
        .candidate-info h3 { margin: 0 0 5px 0; font-size: 18px; color: #1e293b; }
        .candidate-info p { color: #64748b; font-size: 14px; margin-bottom: 20px; font-weight: 500; }
        .btn-group { display: flex; gap: 10px; }
        .btn { padding: 10px; border-radius: 6px; font-size: 13px; font-weight: 600; text-align: center; cursor: pointer; text-decoration: none; border: none; }
        .btn-detail { flex: 1; background: #e2e8f0; color: #334155; }
        .btn-choose { flex: 1; background: #2563eb; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Bilik Suara Pemilihan OSIS</h1>
            <p>Halo, <strong>{{ $pemilih->nama }}</strong> ({{ $pemilih->kelas }}) — Silakan tentukan pilihan paslon terbaikmu dengan bijak.</p>
        </div>

        @if($errors->any())
            <div style="background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="candidates-grid">
            @foreach($kandidat as $k)
            <div class="card">
                <div>
                    <!-- Foto Paslon -->
                    <div class="photo-box">
                        <img src="{{ asset('storage/' . $k->foto) }}" alt="Paslon {{ $k->no_paslon }}">
                    </div>

                    <!-- Info Singkat Paslon -->
                    <div class="candidate-info">
                        <h3>Paslon No. {{ $k->no_paslon }}</h3>
                        <p>{{ $k->nama_ketua }} & {{ $k->nama_wakil }}</p>
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="btn-group">
                    <!-- Tombol Lihat Detail -->
                    <a href="{{ route('voter.kandidat.detail', $k->id) }}" class="btn btn-detail">
                        🔍 Lihat Paslon
                    </a>

                    <!-- Tombol Pilih (Mengirimkan id paslon ke submitVote) -->
                    <form action="{{ route('voter.submit') }}" method="POST" style="flex: 1;" onsubmit="return confirm('Apakah Anda yakin ingin memilih Paslon No. {{ $k->no_paslon }}? Pilihan tidak dapat diubah.')">
                        @csrf
                        <input type="hidden" name="kandidat_id" value="{{ $k->id }}">
                        <button type="submit" class="btn btn-choose" style="width: 100%;">
                            ✅ Pilih Paslon
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</body>
</html>