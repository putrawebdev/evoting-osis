<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kandidat — E-Voting</title>
    <style>
        :root { --primary: #2563eb; --bg: #f8fafc; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: var(--bg); margin: 0; display: flex; height: 100vh; overflow: hidden; }
        .sidebar { width: 260px; background: #ffffff; border-right: 1px solid #e2e8f0; display: flex; flex-direction: column; }
        .sidebar-header { padding: 20px; font-weight: bold; font-size: 14px; color: #64748b; border-bottom: 1px solid #f1f5f9; }
        .sidebar-menu { list-style: none; padding: 0; margin: 0; flex: 1; }
        .sidebar-menu li a { display: flex; align-items: center; padding: 12px 20px; color: #475569; text-decoration: none; font-size: 14px; font-weight: 500; }
        .sidebar-menu li.active a, .sidebar-menu li a:hover { background-color: #eff6ff; color: var(--primary); border-left: 4px solid var(--primary); }
        .sidebar-section-title { padding: 15px 20px 5px; font-size: 11px; font-weight: bold; color: #94a3b8; text-transform: uppercase; }
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; padding: 30px; }
        .card { background: white; border-radius: 10px; border: 1px solid #e2e8f0; padding: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; border-bottom: 1px solid #e2e8f0; text-align: left; font-size: 14px; }
        th { background: #f8fafc; color: #475569; }
        .btn { padding: 8px 14px; border-radius: 6px; border: none; font-size: 13px; font-weight: 600; cursor: pointer; color: white; text-decoration: none; display: inline-block; }
        .btn-primary { background: #2563eb; }
        .btn-warning { background: #d97706; }
        .btn-danger { background: #ef4444; }
        .btn-secondary { background: #64748b; }
        .form-control { width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; margin-top: 5px; box-sizing: border-box; }
        .alert-success { background: #dcfce7; color: #166534; padding: 10px; border-radius: 6px; margin-bottom: 15px; }
        .alert-danger { background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 6px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">ADMIN DASHBOARD</div>
        <ul class="sidebar-menu">
            <div class="sidebar-section-title">Dashboard</div>
            <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <div class="sidebar-section-title">Manajemen</div>
            <li><a href="{{ route('admin.pemilih') }}">Manajemen User</a></li>
            <li class="active"><a href="{{ route('admin.kandidat') }}">Manajemen Kandidat</a></li>
            <div class="sidebar-section-title">Aksi</div>
            <li>
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" style="background: none; border: none; width: 100%; text-align: left; padding: 12px 20px; color: #ef4444; cursor: pointer; font-weight: 500;">Logout Admin</button>
                </form>
            </li>
        </ul>
    </div>

    <div class="main-content">
        <h2>Manajemen Kandidat (Paslon)</h2>

        @if(session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert-danger">{{ $errors->first() }}</div>
        @endif

        <!-- Form Tambah atau Edit Kandidat -->
        <div class="card">
            <h3 style="margin-top:0;">{{ isset($editKandidat) ? 'Edit Data Kandidat Paslon ' . $editKandidat->no_paslon : 'Tambah Kandidat Baru' }}</h3>
            
            <form action="{{ isset($editKandidat) ? route('admin.kandidat.update', $editKandidat->id) : route('admin.kandidat') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if(isset($editKandidat))
                    @method('PUT')
                @endif

                <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                    <div style="flex: 1;">
                        <label>No. Paslon</label>
                        <input type="number" name="no_paslon" class="form-control" value="{{ isset($editKandidat) ? $editKandidat->no_paslon : old('no_paslon') }}" required>
                    </div>
                    <div style="flex: 2;">
                        <label>Nama Ketua</label>
                        <input type="text" name="nama_ketua" class="form-control" value="{{ isset($editKandidat) ? $editKandidat->nama_ketua : old('nama_ketua') }}" required>
                    </div>
                    <div style="flex: 2;">
                        <label>Nama Wakil</label>
                        <input type="text" name="nama_wakil" class="form-control" value="{{ isset($editKandidat) ? $editKandidat->nama_wakil : old('nama_wakil') }}" required>
                    </div>
                </div>
                <div style="margin-bottom: 15px;">
                    <label>Visi</label>
                    <textarea name="visi" class="form-control" rows="2" required>{{ isset($editKandidat) ? $editKandidat->visi : old('visi') }}</textarea>
                </div>
                <div style="margin-bottom: 15px;">
                    <label>Misi</label>
                    <textarea name="misi" class="form-control" rows="2" required>{{ isset($editKandidat) ? $editKandidat->misi : old('misi') }}</textarea>
                </div>
                <div style="margin-bottom: 15px;">
                    <label>Program Unggulan</label>
                    <input type="text" name="proggram" class="form-control" value="{{ isset($editKandidat) ? $editKandidat->proggram : old('proggram') }}">
                </div>
                <div style="margin-bottom: 15px;">
                    <label>Foto Paslon {{ isset($editKandidat) ? '(Biarkan kosong jika tidak ingin mengganti foto)' : '' }}</label>
                    <input type="file" name="foto" class="form-control" accept="image/*" {{ isset($editKandidat) ? '' : 'required' }}>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary">{{ isset($editKandidat) ? 'Perbarui Kandidat' : 'Simpan Kandidat' }}</button>
                    @if(isset($editKandidat))
                        <a href="{{ route('admin.kandidat') }}" class="btn btn-secondary">Batal</a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Tabel Daftar Kandidat -->
        <div class="card">
            <h3 style="margin-top:0;">Daftar Kandidat Terdaftar</h3>
            <table>
                <tr>
                    <th>No</th>
                    <th>Ketua & Wakil</th>
                    <th>Visi & Misi</th>
                    <th>Program</th>
                    <th>Aksi</th>
                </tr>
                @foreach($kandidat as $k)
                <tr>
                    <td><strong>Paslon {{ $k->no_paslon }}</strong></td>
                    <td>{{ $k->nama_ketua }} & {{ $k->nama_wakil }}</td>
                    <td><small>Visi: {{ $k->visi }}<br>Misi: {{ $k->misi }}</small></td>
                    <td>{{ $k->proggram ?? '-' }}</td>
                    <td>
                        <div style="display: flex; gap: 5px;">
                            <a href="{{ route('admin.kandidat.edit', $k->id) }}" class="btn btn-warning" style="padding: 5px 10px; font-size: 11px;">Edit</a>

                            <form action="/admin/kandidat/{{ $k->id }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus kandidat ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" style="padding: 5px 10px; font-size: 11px;">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </table>
        </div>
    </div>
</body>
</html>