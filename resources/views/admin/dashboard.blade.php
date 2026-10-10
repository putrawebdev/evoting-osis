<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Voting Dashboard — Osis</title>
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #2563eb;
            --bg-color: #f8fafc;
            --sidebar-bg: #ffffff;
            --text-color: #1e293b;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            margin: 0;
            display: flex;
            height: 100vh;
            overflow: hidden;
        }
        /* Sidebar */
        .sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            border-right: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
        }
        .sidebar-header {
            padding: 20px;
            font-weight: bold;
            font-size: 14px;
            letter-spacing: 0.5px;
            color: #64748b;
            border-bottom: 1px solid #f1f5f9;
        }
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
            flex: 1;
        }
        .sidebar-menu li a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #475569;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
        }
        .sidebar-menu li a:hover, .sidebar-menu li.active a {
            background-color: #eff6ff;
            color: var(--primary-color);
            border-left: 4px solid var(--primary-color);
        }
        .sidebar-section-title {
            padding: 15px 20px 5px;
            font-size: 11px;
            font-weight: bold;
            color: #94a3b8;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }
        .topbar {
            background: #ffffff;
            padding: 15px 30px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .topbar h1 {
            font-size: 18px;
            margin: 0;
            font-weight: 700;
        }
        .content-body {
            padding: 30px;
        }

        /* Cards Statistik */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #ffffff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .stat-info h3 {
            margin: 0 0 5px 0;
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .stat-info p {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
            color: #0f172a;
        }
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }
        .bg-blue { background-color: #3b82f6; }
        .bg-green { background-color: #10b981; }

        /* Panel Grafik */
        .card-panel {
            background: #ffffff;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            padding: 25px;
            margin-bottom: 30px;
        }
        .card-panel h3 {
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 16px;
        }

        /* Action Buttons Bottom */
        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }
        .btn {
            padding: 10px 18px;
            border-radius: 6px;
            border: none;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: white;
        }
        .btn-success { background-color: #10b981; }
        .btn-danger { background-color: #ef4444; }
        .btn-primary { background-color: #3b82f6; }
    </style>
</head>
<body>

    <!-- Sidebar Navigasi -->
    <div class="sidebar">
        <div class="sidebar-header">
            ADMIN DASHBOARD
        </div>
        <ul class="sidebar-menu">
            <div class="sidebar-section-title">Dashboard</div>
            <li class="active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>

            <div class="sidebar-section-title">Manajemen</div>
            <li><a href="{{ route('admin.pemilih') }}">Manajemen User</a></li>
            <li><a href="{{ route('admin.kandidat') }}">Manajemen Kandidat</a></li>

            <div class="sidebar-section-title">Hasil</div>
            <li>
                <form action="{{ route('admin.logout') }}" method="POST" style="padding: 0; margin: 0;">
                    @csrf
                    <button type="submit" style="background: none; border: none; width: 100%; text-align: left; padding: 12px 20px; color: #ef4444; cursor: pointer; font-size: 14px; font-weight: 500;">
                        Logout Admin
                    </button>
                </form>
            </li>
        </ul>
    </div>

    <!-- Main Content Area -->
    <div class="main-content">
        <div class="topbar">
            <h1>METSCHOO E-VOTING</h1>
            <div style="font-size: 14px; font-weight: 600; color: #475569;">
                Administrator ▾
            </div>
        </div>

        <div class="content-body">
            <!-- Kartu Statistik Utama -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Total Siswa / Pemilih</h3>
                        <p>{{ $total_pemilih }}</p>
                    </div>
                    <div class="stat-icon bg-blue">👥</div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Total Suara Masuk</h3>
                        <p>{{ $sudah_memilih }}</p>
                    </div>
                    <div class="stat-icon bg-green">📥</div>
                </div>
            </div>

            <!-- Panel Grafik Perolehan Suara -->
            <div class="card-panel">
                <h3>Data Perolehan Suara Kandidat</h3>
                <div style="position: relative; height: 320px; width: 100%;">
                    <canvas id="voteBarChart"></canvas>
                </div>
            </div>

            <!-- Tombol Aksi Bawah (Ekspor Excel / Laporan) -->
            <div class="action-buttons">
                <a href="{{ route('admin.export.excel') }}" class="btn btn-success">
                    📊 Export ke Excel
                </a>
            </div>
        </div>
    </div>

    <!-- Script Chart.js Model Batang (Bar Chart) -->
    <script>
        const ctx = document.getElementById('voteBarChart').getContext('2d');
        
        const labels = [
            @foreach($kandidat as $paslon)
                'Paslon {{ $paslon->no_paslon }}: {{ $paslon->nama_ketua }} & {{ $paslon->nama_wakil }}',
            @endforeach
        ];
        
        const dataValues = [
            @foreach($kandidat as $paslon)
                {{ $paslon->pemilih_count }},
            @endforeach
        ];

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jumlah Suara',
                    data: dataValues,
                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    </script>
</body>
</html>