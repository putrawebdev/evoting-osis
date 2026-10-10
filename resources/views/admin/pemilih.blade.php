<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User — E-Voting</title>
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
            <li class="active"><a href="{{ route('admin.pemilih') }}">Manajemen User</a></li>
            <li><a href="{{ route('admin.kandidat') }}">Manajemen Kandidat</a></li>
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
        <h2>Manajemen Data Pemilih (DPT)</h2>

        @if(session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert-danger">{{ $errors->first() }}</div>
        @endif

        <!-- Form Tambah atau Edit Pemilih -->
        <div class="card">
            <h3 style="margin-top:0;">{{ isset($editPemilih) ? 'Edit Data Pemilih' : 'Tambah Pemilih Baru' }}</h3>
            
            <form action="{{ isset($editPemilih) ? route('admin.pemilih.update', $editPemilih->id) : route('admin.pemilih') }}" method="POST">
                @csrf
                @if(isset($editPemilih))
                    @method('PUT')
                @endif

                <div style="display: flex; gap: 15px;">
                    <div style="flex: 1;">
                        <label>NIS</label>
                        <input type="text" name="nis" class="form-control" value="{{ isset($editPemilih) ? $editPemilih->nis : old('nis') }}" required>
                    </div>
                    <div style="flex: 2;">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" value="{{ isset($editPemilih) ? $editPemilih->nama : old('nama') }}" required>
                    </div>
                    <div style="flex: 1;">
                        <label>Kelas</label>
                        <input type="text" name="kelas" class="form-control" value="{{ isset($editPemilih) ? $editPemilih->kelas : old('kelas') }}" required>
                    </div>
                    <div style="align-self: flex-end; display: flex; gap: 5px;">
                        <button type="submit" class="btn btn-primary" style="height: 38px;">{{ isset($editPemilih) ? 'Perbarui' : 'Simpan' }}</button>
                        @if(isset($editPemilih))
                            <a href="{{ route('admin.pemilih') }}" class="btn btn-secondary" style="height: 38px; line-height: 22px; box-sizing: border-box;">Batal</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- Form Import Excel / CSV -->
        <div class="card" style="background: #f8fafc; border: 1px dashed #cbd5e1;">
            <h3 style="margin-top:0; font-size: 15px;">📥 Import Massal DPT (Format CSV)</h3>
            <p style="font-size: 13px; color: #64748b; margin-bottom: 15px;">
                Format kolom baris file CSV: <strong>NIS, Nama, Kelas</strong> (Baris pertama header bebas/bisa dilewati).
            </p>
            
            <form action="{{ route('admin.pemilih.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div style="display: flex; gap: 15px; align-items: center;">
                    <div style="flex: 1;">
                        <input type="file" name="file_csv" class="form-control" accept=".csv, .txt, .xlsx" required style="background: white;">
                    </div>
                    <button type="submit" class="btn" style="background-color: #0d9488; height: 38px;">Upload & Import</button>
                </div>
            </form>
        </div>
        
        <!-- Tabel Daftar Pemilih -->
        <div class="card">
            <h3 style="margin-top:0;">Daftar Siswa Terdaftar</h3>
            <table>
                <tr>
                    <th>NIS</th>
                    <th>Nama</th>
                    <th>Kelas</th>
                    <th>Status Memilih</th>
                    <th>Aksi</th>
                </tr>
                @foreach($pemilih as $p)
                <tr>
                    <td>{{ $p->nis }}</td>
                    <td>{{ $p->nama }}</td>
                    <td>{{ $p->kelas }}</td>
                    <td>
                        @if($p->sudah_memilih)
                            <span style="color: green; font-weight: bold;">Sudah Memilih</span>
                        @else
                            <span style="color: red;">Belum Memilih</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; gap: 5px;">
                            <!-- Tombol Edit -->
                            <a href="{{ route('admin.pemilih.edit', $p->id) }}" class="btn btn-warning" style="padding: 5px 10px; font-size: 11px;">Edit</a>
                            
                            <form action="/admin/pemilih/{{ $p->id }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" style="padding: 5px 10px; font-size: 11px;">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </table>
            <div style="margin-top: 15px;">{{ $pemilih->links() }}</div>
        </div>
    </div>
</body>
</html>