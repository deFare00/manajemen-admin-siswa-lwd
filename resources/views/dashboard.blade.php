<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Sistem Manajemen Les Privat Coding (Laravel)</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <style>
    :root {
      --bg-main: #0f172a;
      --bg-card: #1e293b;
      --bg-hover: #334155;
      --border-color: #334155;
      --text-main: #f8fafc;
      --text-muted: #94a3b8;
      --accent-blue: #3b82f6;
      --accent-green: #10b981;
      --accent-yellow: #f59e0b;
      --accent-red: #ef4444;
      --accent-purple: #8b5cf6;
      --radius: 12px;
      --shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3), 0 8px 10px -6px rgba(0, 0, 0, 0.3);
    }

    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
    body { color-scheme: dark; background-color: var(--bg-main); color: var(--text-main); display: flex; height: 100vh; overflow: hidden; }

    .sidebar { width: 260px; background-color: #0b1329; border-right: 1px solid var(--border-color); display: flex; flex-direction: column; padding: 1.5rem 1rem; flex-shrink: 0; transition: transform 0.3s ease, left 0.3s ease; }
    .brand { display: flex; align-items: center; justify-content: space-between; padding-bottom: 1.5rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); }
    .brand-left { display: flex; align-items: center; gap: 0.75rem; }
    .brand-icon { width: 40px; height: 40px; background: linear-gradient(135deg, var(--accent-blue), var(--accent-purple)); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
    .brand-title { font-size: 1.1rem; font-weight: 700; color: #ffffff; }
    .brand-subtitle { font-size: 0.75rem; color: var(--text-muted); }
    .sidebar-close { display: none; background: none; border: none; color: var(--text-muted); font-size: 1.5rem; cursor: pointer; }

    .nav-menu { list-style: none; display: flex; flex-direction: column; gap: 0.5rem; flex: 1; }
    .nav-item button { width: 100%; display: flex; align-items: center; gap: 0.85rem; padding: 0.85rem 1rem; background: transparent; border: none; border-radius: var(--radius); color: var(--text-muted); font-size: 0.95rem; font-weight: 500; cursor: pointer; transition: all 0.2s ease; text-align: left; }
    .nav-item button:hover { background-color: var(--bg-hover); color: #ffffff; }
    .nav-item button.active { background: linear-gradient(90deg, rgba(59, 130, 246, 0.2), transparent); border-left: 4px solid var(--accent-blue); color: #ffffff; font-weight: 600; }

    .sidebar-overlay { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0, 0, 0, 0.65); backdrop-filter: blur(4px); z-index: 90; display: none; opacity: 0; transition: opacity 0.3s ease; }
    .sidebar-overlay.active { display: block; opacity: 1; }

    .main-content { flex: 1; display: flex; flex-direction: column; overflow: hidden; background-color: var(--bg-main); width: 100%; }
    .topbar { height: 65px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; padding: 0 1.5rem; background-color: #0d1527; gap: 0.75rem; }
    .topbar-left { display: flex; align-items: center; gap: 0.75rem; }
    .mobile-toggle { display: none; background: transparent; border: none; color: #ffffff; font-size: 1.5rem; cursor: pointer; padding: 0.25rem 0.5rem; border-radius: 6px; }
    .mobile-toggle:hover { background-color: var(--bg-hover); }
    .page-title { font-size: 1.15rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    .btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.6rem 1.2rem; border-radius: var(--radius); font-size: 0.9rem; font-weight: 600; cursor: pointer; border: none; transition: all 0.2s ease; }
    .btn-primary { background: linear-gradient(135deg, var(--accent-blue), #2563eb); color: white; }
    .btn-secondary { background-color: var(--bg-hover); color: var(--text-main); }
    .btn-danger { background-color: rgba(239, 68, 68, 0.2); color: var(--accent-red); border: 1px solid rgba(239, 68, 68, 0.4); }

    .content-body { flex: 1; padding: 1.5rem; overflow-y: auto; }
    .tab-pane { display: none; }
    .tab-pane.active { display: block; }

    .metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem; }
    .metric-card { background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius); padding: 1.25rem; box-shadow: var(--shadow); display: flex; flex-direction: column; gap: 0.5rem; position: relative; overflow: hidden; }
    .metric-card::before { content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%; }
    .card-blue::before { background-color: var(--accent-blue); }
    .card-green::before { background-color: var(--accent-green); }
    .card-red::before { background-color: var(--accent-red); }
    .metric-header { color: var(--text-muted); font-size: 0.85rem; font-weight: 600; text-transform: uppercase; }
    .metric-value { font-size: 1.7rem; font-weight: 700; color: #ffffff; }

    .table-container { background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius); box-shadow: var(--shadow); margin-bottom: 1.5rem; overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .table-header-bar { padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border-color); gap: 1rem; flex-wrap: wrap; }
    .input-control { width: 100%; padding: 0.65rem 1rem; background-color: #0f172a; border: 1px solid var(--border-color); border-radius: 8px; color: #ffffff; font-size: 0.9rem; outline: none; color-scheme: dark; }
    textarea.input-control { resize: vertical; }

    .data-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem; min-width: 650px; }
    .data-table th { background-color: #162032; padding: 0.9rem 1.25rem; color: var(--text-muted); font-weight: 600; border-bottom: 1px solid var(--border-color); white-space: nowrap; }
    .data-table td { padding: 0.9rem 1.25rem; border-bottom: 1px solid rgba(51, 65, 85, 0.5); white-space: nowrap; }

    .badge { display: inline-flex; align-items: center; padding: 0.25rem 0.65rem; border-radius: 20px; font-size: 0.78rem; font-weight: 600; }
    .badge-success { background-color: rgba(16, 185, 129, 0.15); color: #34d399; }
    .badge-warning { background-color: rgba(245, 158, 11, 0.15); color: #fbbf24; }
    .badge-danger { background-color: rgba(239, 68, 68, 0.15); color: #f87171; }
    .badge-info { background-color: rgba(59, 130, 246, 0.15); color: #60a5fa; }

    .modal-overlay { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background-color: rgba(15, 23, 42, 0.8); display: flex; align-items: center; justify-content: center; z-index: 999; opacity: 0; pointer-events: none; transition: opacity 0.25s ease; padding: 1rem; }
    .modal-overlay.active { opacity: 1; pointer-events: auto; }
    .modal-card { background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius); width: 100%; max-width: 550px; max-height: 90vh; display: flex; flex-direction: column; }
    .modal-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; }
    .modal-body { padding: 1.25rem 1.5rem; display: flex; flex-direction: column; gap: 1rem; overflow-y: auto; flex: 1; }
    .modal-footer { padding: 1rem 1.5rem; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 0.75rem; background-color: #162032; }
    .form-group { display: flex; flex-direction: column; gap: 0.4rem; }
    .form-label { font-size: 0.85rem; font-weight: 600; color: var(--text-muted); }

    .toast-container { position: fixed; bottom: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 0.5rem; max-width: calc(100vw - 40px); }
    .toast { padding: 0.9rem 1.25rem; border-radius: var(--radius); background-color: var(--bg-card); border: 1px solid var(--border-color); color: #ffffff; font-size: 0.9rem; box-shadow: var(--shadow); }

    /* 📱 RESPONSIVE MOBILE BREAKPOINTS */
    @media (max-width: 768px) {
      body { flex-direction: column; height: 100vh; }
      .sidebar { position: fixed; top: 0; left: -280px; width: 260px; height: 100vh; z-index: 100; box-shadow: var(--shadow); }
      .sidebar.active { left: 0; }
      .sidebar-close { display: block; }
      .mobile-toggle { display: flex; align-items: center; justify-content: center; }
      .topbar { padding: 0 1rem; }
      .page-title { font-size: 1rem; }
      .content-body { padding: 1rem; }
      .metrics-grid { grid-template-columns: 1fr; gap: 1rem; }
      .table-header-bar { flex-direction: column; align-items: stretch; }
      .table-header-bar .input-control { max-width: 100% !important; }
      .table-header-bar .btn { width: 100%; justify-content: center; }
      .modal-card { max-height: 85vh; }
    }
  </style>
</head>
<body>

  <!-- Overlay for Mobile Sidebar -->
  <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <div class="brand">
      <div class="brand-left">
        <div class="brand-icon">🚀</div>
        <div>
          <div class="brand-title">Laravel Privat</div>
          <div class="brand-subtitle">Vercel & Supabase</div>
        </div>
      </div>
      <button class="sidebar-close" onclick="closeSidebar()">&times;</button>
    </div>
    
    <ul class="nav-menu">
      <li class="nav-item"><button class="active" onclick="switchTab('dashboard', this)"><span>📊</span> Dashboard</button></li>
      <li class="nav-item"><button onclick="switchTab('students', this)"><span>👨‍🎓</span> Data Siswa</button></li>
      <li class="nav-item"><button onclick="switchTab('logs', this)"><span>📝</span> Log Pertemuan</button></li>
      <li class="nav-item"><button onclick="switchTab('payments', this)"><span>💳</span> Keuangan & Pembayaran</button></li>
    </ul>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <div class="topbar">
      <div class="topbar-left">
        <button class="mobile-toggle" onclick="toggleSidebar()">☰</button>
        <div class="page-title" id="pageTitle">📊 Dashboard Ringkasan</div>
      </div>
      <div><button class="btn btn-secondary" onclick="refreshTab()">🔄</button></div>
    </div>

    <div class="content-body">
      <!-- 1. DASHBOARD -->
      <div id="tab-dashboard" class="tab-pane active">
        <div class="metrics-grid">
          <div class="metric-card card-blue"><div class="metric-header">Total Siswa Aktif</div><div class="metric-value" id="dashActive">{{ $activeStudentsCount }}</div></div>
          <div class="metric-card card-green"><div class="metric-header">Pendapatan Bulan Ini</div><div class="metric-value" id="dashIncome">Rp {{ number_format($monthlyIncome, 0, ',', '.') }}</div></div>
          <div class="metric-card card-red"><div class="metric-header">Belum Bayar</div><div class="metric-value" id="dashUnpaid">{{ $unpaidCount }}</div></div>
        </div>

        <!-- PROGRESS SESI BELAJAR SISWA -->
        <div class="table-container">
          <div class="table-header-bar"><h3 style="color: #60a5fa; font-size: 1rem;">📊 Progress Sesi Belajar Siswa</h3></div>
          <table class="data-table">
            <thead>
              <tr><th>Kode</th><th>Nama Siswa</th><th>Bahasa</th><th>Sistem</th><th>Progress Pertemuan</th><th>Sisa Kuota</th><th>Status</th></tr>
            </thead>
            <tbody id="dashProgressBody">
              @forelse($studentsProgress as $st)
                <tr>
                  <td><code>{{ $st->student_code }}</code></td>
                  <td><strong>{{ $st->name }}</strong></td>
                  <td>{{ $st->programming_lang }}</td>
                  <td>{{ $st->learning_system }}</td>
                  <td><span class="badge badge-info" style="font-size:0.88rem; padding: 0.35rem 0.75rem;">Sesi {{ $st->session_progress }}</span></td>
                  <td><strong style="color:var(--accent-blue)">{{ $st->package_quota }} Sesi</strong></td>
                  <td><span class="badge {{ $st->status==='Aktif'?'badge-success':'badge-warning' }}">{{ $st->status }}</span></td>
                </tr>
              @empty
                <tr><td colspan="7" style="text-align:center; padding: 2rem; color: var(--text-muted);">Belum ada data siswa aktif.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <!-- TAGIHAN TERTUNDA -->
        <div class="table-container">
          <div class="table-header-bar"><h3 style="color: #f87171; font-size: 1rem;">⚠️ Siswa Menunggu Pembayaran</h3></div>
          <table class="data-table">
            <thead>
              <tr><th>Tanggal</th><th>Nama Siswa</th><th>Periode / Paket</th><th>Nominal</th><th>Email Ortu</th></tr>
            </thead>
            <tbody id="unpaidBody">
              @forelse($unpaidList as $item)
                <tr>
                  <td>{{ $item->payment_date ? $item->payment_date->format('Y-m-d') : '-' }}</td>
                  <td><strong>{{ $item->student->name ?? '-' }}</strong></td>
                  <td>{{ $item->package_period }}</td>
                  <td><strong style="color: #f87171;">Rp {{ number_format($item->amount, 0, ',', '.') }}</strong></td>
                  <td>{{ $item->student->parent_email ?? 'Kosong' }}</td>
                </tr>
              @empty
                <tr><td colspan="5" style="text-align:center; padding: 2rem; color: var(--text-muted);">🎉 Tidak ada tagihan tertunda!</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <!-- 2. STUDENTS -->
      <div id="tab-students" class="tab-pane">
        <div class="table-container">
          <div class="table-header-bar">
            <input type="text" class="input-control" id="searchStudent" placeholder="Cari nama siswa..." oninput="renderStudents()" style="max-width: 300px;">
            <button class="btn btn-primary" onclick="openStudentModal()">+ Tambah Siswa</button>
          </div>
          <table class="data-table">
            <thead><tr><th>ID</th><th>Nama</th><th>Usia</th><th>Bahasa</th><th>Sistem</th><th>Progress Pertemuan</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody id="studentsBody"></tbody>
          </table>
        </div>
      </div>

      <!-- 3. LOGS -->
      <div id="tab-logs" class="tab-pane">
        <div class="table-container">
          <div class="table-header-bar">
            <input type="text" class="input-control" id="searchLog" placeholder="Cari log..." oninput="renderLogs()" style="max-width: 300px;">
            <button class="btn btn-primary" onclick="openLogModal()">+ Catat Log</button>
          </div>
          <table class="data-table">
            <thead><tr><th>Tanggal & Waktu</th><th>ID</th><th>Nama</th><th>Sesi</th><th>Materi</th><th>Rating</th><th>Presensi</th><th>Aksi</th></tr></thead>
            <tbody id="logsBody"></tbody>
          </table>
        </div>
      </div>

      <!-- 4. PAYMENTS -->
      <div id="tab-payments" class="tab-pane">
        <div class="table-container">
          <div class="table-header-bar">
            <input type="text" class="input-control" id="searchPayment" placeholder="Cari transaksi..." oninput="renderPayments()" style="max-width: 300px;">
            <button class="btn btn-primary" onclick="openPaymentModal()">+ Catat Pembayaran</button>
          </div>
          <table class="data-table">
            <thead><tr><th>Tanggal</th><th>Nama</th><th>Periode</th><th>Nominal</th><th>Metode</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody id="paymentsBody"></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL STUDENT -->
  <div class="modal-overlay" id="studentModal">
    <div class="modal-card">
      <div class="modal-header"><h3 id="studentModalTitle">Tambah Siswa</h3><button type="button" style="background:none; border:none; color:white; font-size:1.5rem; cursor:pointer;" onclick="closeModal('studentModal')">&times;</button></div>
      <form onsubmit="saveStudent(event)">
        <div class="modal-body">
          <input type="hidden" id="studentIdVal">
          <div class="form-group"><label class="form-label">Nama *</label><input type="text" class="input-control" id="studentName" required></div>
          <div class="form-group"><label class="form-label">Usia/Tingkat</label><input type="text" class="input-control" id="studentAge"></div>
          <div class="form-group"><label class="form-label">Email Ortu *</label><input type="email" class="input-control" id="studentEmail"></div>
          <div class="form-group"><label class="form-label">Bahasa Pemrograman</label>
            <select class="input-control" id="studentLang"><option value="Python">Python</option><option value="Web Dev (HTML/CSS/JS)">Web Dev</option><option value="Scratch">Scratch</option><option value="Java">Java</option><option value="C++">C++</option></select>
          </div>
          <div class="form-group"><label class="form-label">Catatan Jadwal (Fleksibel)</label><input type="text" class="input-control" id="studentSchedule" value="Fleksibel"></div>
          <div class="form-group"><label class="form-label">Sistem Belajar</label>
            <select class="input-control" id="studentSystem"><option value="Paket">Paket (8 Sesi)</option><option value="Bulanan">Bulanan</option></select>
          </div>
          <div class="form-group"><label class="form-label">Status</label>
            <select class="input-control" id="studentStatus"><option value="Aktif">Aktif</option><option value="Cuti">Cuti</option><option value="Lulus">Lulus</option></select>
          </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="closeModal('studentModal')">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
      </form>
    </div>
  </div>

  <!-- MODAL LOG -->
  <div class="modal-overlay" id="logModal">
    <div class="modal-card">
      <div class="modal-header"><h3 id="logModalTitle">Catat Log Pertemuan</h3><button type="button" style="background:none; border:none; color:white; font-size:1.5rem; cursor:pointer;" onclick="closeModal('logModal')">&times;</button></div>
      <form onsubmit="saveLog(event)">
        <div class="modal-body">
          <input type="hidden" id="logIdVal">
          <div class="form-group"><label class="form-label">Nama Siswa *</label><select class="input-control" id="logStudentId" required></select></div>
          <div class="form-group">
            <label class="form-label">Tanggal & Waktu Pertemuan * (Bisa Diklik & Bebas Diatur)</label>
            <input type="datetime-local" class="input-control" id="logMeetingDate" required style="cursor: pointer;">
          </div>
          <div class="form-group"><label class="form-label">Sesi Ke *</label><input type="number" class="input-control" id="logSessionNum" value="1" min="1" required></div>
          <div class="form-group"><label class="form-label">Materi Coding *</label><input type="text" class="input-control" id="logTopic" required placeholder="contoh: Loops & Functions Python"></div>
          <div class="form-group"><label class="form-label">Progress Project</label><input type="text" class="input-control" id="logProgress" placeholder="contoh: Game Tic-Tac-Toe 80%"></div>
          <div class="form-group"><label class="form-label">Rating Pemahaman (1-5)</label>
            <select class="input-control" id="logRating"><option value="5">⭐⭐⭐⭐⭐ (5)</option><option value="4">⭐⭐⭐⭐ (4)</option><option value="3">⭐⭐⭐ (3)</option><option value="2">⭐⭐ (2)</option><option value="1">⭐ (1)</option></select>
          </div>
          <div class="form-group"><label class="form-label">Catatan Evaluasi</label><textarea class="input-control" id="logNotes" rows="2" placeholder="Catatan perkembangan siswa..."></textarea></div>
          <div class="form-group"><label class="form-label">Status Kehadiran</label>
            <select class="input-control" id="logAttendance"><option value="Hadir">Hadir</option><option value="Izin">Izin</option><option value="Alfa">Alfa</option></select>
          </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="closeModal('logModal')">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
      </form>
    </div>
  </div>

  <!-- MODAL PAYMENT -->
  <div class="modal-overlay" id="paymentModal">
    <div class="modal-card">
      <div class="modal-header"><h3 id="paymentModalTitle">Catat Pembayaran Tagihan</h3><button type="button" style="background:none; border:none; color:white; font-size:1.5rem; cursor:pointer;" onclick="closeModal('paymentModal')">&times;</button></div>
      <form onsubmit="savePayment(event)">
        <div class="modal-body">
          <input type="hidden" id="paymentIdVal">
          <div class="form-group"><label class="form-label">Nama Siswa *</label><select class="input-control" id="paymentStudentId" required></select></div>
          <div class="form-group"><label class="form-label">Tanggal Bayar *</label><input type="date" class="input-control" id="paymentDate" required style="cursor: pointer;"></div>
          <div class="form-group"><label class="form-label">Periode / Paket *</label><input type="text" class="input-control" id="paymentPeriod" placeholder="Paket 8 Sesi / Juli 2026" required></div>
          <div class="form-group"><label class="form-label">Nominal (Rp) *</label><input type="number" class="input-control" id="paymentAmount" required></div>
          <div class="form-group"><label class="form-label">Metode Transfer</label>
            <select class="input-control" id="paymentMethod"><option value="BCA">BCA</option><option value="Mandiri">Mandiri</option><option value="BNI">BNI</option><option value="BRI">BRI</option><option value="E-Wallet">E-Wallet</option><option value="Cash">Cash</option></select>
          </div>
          <div class="form-group"><label class="form-label">Status Pembayaran</label>
            <select class="input-control" id="paymentStatus"><option value="Lunas">Lunas</option><option value="Belum Bayar">Belum Bayar</option></select>
          </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="closeModal('paymentModal')">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
      </form>
    </div>
  </div>

  <div class="toast-container" id="toastContainer"></div>

  <script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const headersConfig = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    };

    let currentTab = 'dashboard';
    let rawStudents = [], rawLogs = [], rawPayments = [];

    function toggleSidebar() {
      document.getElementById('sidebar').classList.toggle('active');
      document.getElementById('sidebarOverlay').classList.toggle('active');
    }

    function closeSidebar() {
      document.getElementById('sidebar').classList.remove('active');
      document.getElementById('sidebarOverlay').classList.remove('active');
    }

    window.addEventListener('DOMContentLoaded', () => {
      fetch('/api/students').then(r=>r.json()).then(d=>{ rawStudents=d; populateStudentOptions(); });
    });

    function formatForDateTimeInput(dateStr) {
      if (!dateStr) return '';
      let d = new Date(dateStr);
      if (isNaN(d.getTime())) return '';
      d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
      return d.toISOString().slice(0, 16);
    }

    function switchTab(tab, btn) {
      currentTab = tab;
      document.querySelectorAll('.nav-menu button').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
      document.getElementById('tab-' + tab).classList.add('active');
      closeSidebar();
      refreshTab();
    }

    function refreshTab() {
      if (currentTab === 'dashboard') {
        fetch('/api/dashboard/summary').then(r=>r.json()).then(d=>{
          document.getElementById('dashActive').innerText = d.activeStudentsCount;
          document.getElementById('dashIncome').innerText = 'Rp ' + Number(d.monthlyIncome).toLocaleString('id-ID');
          document.getElementById('dashUnpaid').innerText = d.unpaidCount;
        });
        fetch('/api/students').then(r=>r.json()).then(d=>{
          rawStudents = d;
          let activeOnly = d.filter(s=>s.status==='Aktif');
          document.getElementById('dashProgressBody').innerHTML = activeOnly.map(st=>`
            <tr>
              <td><code>${st.student_code}</code></td>
              <td><strong>${st.name}</strong></td>
              <td>${st.programming_lang}</td>
              <td>${st.learning_system}</td>
              <td><span class="badge badge-info" style="font-size:0.88rem; padding: 0.35rem 0.75rem;">Sesi ${st.session_progress}</span></td>
              <td><strong style="color:var(--accent-blue)">${st.package_quota} Sesi</strong></td>
              <td><span class="badge ${st.status==='Aktif'?'badge-success':'badge-warning'}">${st.status}</span></td>
            </tr>
          `).join('');
        });
      }
      if (currentTab === 'students') fetch('/api/students').then(r=>r.json()).then(d=>{rawStudents=d; renderStudents(); populateStudentOptions();});
      if (currentTab === 'logs') fetch('/api/meeting-logs').then(r=>r.json()).then(d=>{rawLogs=d; renderLogs();});
      if (currentTab === 'payments') fetch('/api/payments').then(r=>r.json()).then(d=>{rawPayments=d; renderPayments();});
    }

    function populateStudentOptions() {
      const opts = rawStudents.filter(s=>s.status!=='Lulus').map(s=>`<option value="${s.id}">${s.name} (${s.student_code})</option>`).join('');
      if(document.getElementById('logStudentId')) document.getElementById('logStudentId').innerHTML = opts;
      if(document.getElementById('paymentStudentId')) document.getElementById('paymentStudentId').innerHTML = opts;
    }

    /* -------------------------------------------------------------------------- */
    /* STUDENTS                                                                   */
    /* -------------------------------------------------------------------------- */
    function renderStudents() {
      const q = document.getElementById('searchStudent').value.toLowerCase();
      const filtered = rawStudents.filter(s => s.name.toLowerCase().includes(q) || s.student_code.toLowerCase().includes(q));
      document.getElementById('studentsBody').innerHTML = filtered.map(s => `
        <tr>
          <td><code>${s.student_code}</code></td>
          <td><strong>${s.name}</strong></td>
          <td>${s.age_level||'-'}</td>
          <td>${s.programming_lang}</td>
          <td>${s.learning_system}</td>
          <td><span class="badge badge-info" style="font-size:0.88rem;">Sesi ${s.session_progress}</span></td>
          <td><span class="badge ${s.status==='Aktif'?'badge-success':'badge-warning'}">${s.status}</span></td>
          <td><button class="btn btn-secondary" onclick="openStudentModal(${s.id})">✏️</button> <button class="btn btn-danger" onclick="deleteStudent(${s.id})">🗑️</button></td>
        </tr>
      `).join('');
    }

    function openStudentModal(id=null) {
      document.getElementById('studentIdVal').value = id || '';
      if(id) {
        let s = rawStudents.find(i=>i.id===id);
        if(s) {
          document.getElementById('studentModalTitle').innerText = 'Edit Data Siswa';
          document.getElementById('studentName').value = s.name;
          document.getElementById('studentAge').value = s.age_level || '';
          document.getElementById('studentEmail').value = s.parent_email || '';
          document.getElementById('studentLang').value = s.programming_lang;
          document.getElementById('studentSchedule').value = s.schedule_notes || 'Fleksibel';
          document.getElementById('studentSystem').value = s.learning_system;
          document.getElementById('studentStatus').value = s.status;
        }
      } else {
        document.getElementById('studentModalTitle').innerText = 'Tambah Siswa';
        document.getElementById('studentName').value = '';
        document.getElementById('studentAge').value = '';
        document.getElementById('studentEmail').value = '';
      }
      document.getElementById('studentModal').classList.add('active');
    }

    function saveStudent(e) {
      e.preventDefault();
      let id = document.getElementById('studentIdVal').value;
      let url = id ? '/api/students/' + id : '/api/students';
      let method = id ? 'PUT' : 'POST';
      let payload = {
        name: document.getElementById('studentName').value,
        age_level: document.getElementById('studentAge').value,
        parent_email: document.getElementById('studentEmail').value,
        programming_lang: document.getElementById('studentLang').value,
        schedule_notes: document.getElementById('studentSchedule').value,
        learning_system: document.getElementById('studentSystem').value,
        status: document.getElementById('studentStatus').value,
      };
      fetch(url, { method: method, headers: headersConfig, body: JSON.stringify(payload) })
        .then(r=>r.json()).then(res=>{ closeModal('studentModal'); showToast(res.message); refreshTab(); });
    }

    function deleteStudent(id) {
      if(confirm('Hapus data siswa ini?')) {
        fetch('/api/students/' + id, { method: 'DELETE', headers: headersConfig })
          .then(r=>r.json())
          .then(res=>{ showToast(res.message); refreshTab(); })
          .catch(err => showToast('Gagal menghapus siswa.'));
      }
    }

    /* -------------------------------------------------------------------------- */
    /* LOGS                                                                       */
    /* -------------------------------------------------------------------------- */
    function renderLogs() {
      const q = document.getElementById('searchLog').value.toLowerCase();
      const filtered = rawLogs.filter(l => (l.student ? l.student.name.toLowerCase() : '').includes(q) || l.topic.toLowerCase().includes(q));
      document.getElementById('logsBody').innerHTML = filtered.map(l => `
        <tr>
          <td>${l.meeting_date ? l.meeting_date.substring(0, 16).replace('T', ' ') : '-'}</td>
          <td><code>${l.student ? l.student.student_code : '-'}</code></td>
          <td><strong>${l.student ? l.student.name : '-'}</strong></td>
          <td>#${l.session_number}</td>
          <td>${l.topic}</td>
          <td>${'⭐'.repeat(l.rating)}</td>
          <td><span class="badge ${l.attendance_status==='Hadir'?'badge-success':'badge-warning'}">${l.attendance_status}</span></td>
          <td><button class="btn btn-secondary" onclick="openLogModal(${l.id})">✏️</button> <button class="btn btn-danger" onclick="deleteLog(${l.id})">🗑️</button></td>
        </tr>
      `).join('');
    }

    function openLogModal(id=null) {
      document.getElementById('logIdVal').value = id || '';
      
      if(id) {
        let l = rawLogs.find(i=>i.id===id);
        if(l) {
          document.getElementById('logModalTitle').innerText = 'Edit Log Pertemuan';
          document.getElementById('logStudentId').value = l.student_id;
          document.getElementById('logMeetingDate').value = formatForDateTimeInput(l.meeting_date);
          document.getElementById('logSessionNum').value = l.session_number;
          document.getElementById('logTopic').value = l.topic;
          document.getElementById('logProgress').value = l.project_progress || '';
          document.getElementById('logRating').value = l.rating;
          document.getElementById('logNotes').value = l.evaluation_notes || '';
          document.getElementById('logAttendance').value = l.attendance_status;
        }
      } else {
        document.getElementById('logModalTitle').innerText = 'Catat Log Pertemuan';
        let now = new Date(); now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        document.getElementById('logMeetingDate').value = now.toISOString().slice(0, 16);
        document.getElementById('logTopic').value = '';
        document.getElementById('logProgress').value = '';
        document.getElementById('logNotes').value = '';
      }
      document.getElementById('logModal').classList.add('active');
    }

    function saveLog(e) {
      e.preventDefault();
      let id = document.getElementById('logIdVal').value;
      let url = id ? '/api/meeting-logs/' + id : '/api/meeting-logs';
      let method = id ? 'PUT' : 'POST';
      let payload = {
        student_id: document.getElementById('logStudentId').value,
        meeting_date: document.getElementById('logMeetingDate').value,
        session_number: document.getElementById('logSessionNum').value,
        topic: document.getElementById('logTopic').value,
        project_progress: document.getElementById('logProgress').value,
        rating: document.getElementById('logRating').value,
        evaluation_notes: document.getElementById('logNotes').value,
        attendance_status: document.getElementById('logAttendance').value,
      };
      fetch(url, { method: method, headers: headersConfig, body: JSON.stringify(payload) })
        .then(r=>r.json()).then(res=>{ closeModal('logModal'); showToast(res.message); refreshTab(); });
    }

    function deleteLog(id) {
      if(confirm('Hapus log pertemuan ini?')) {
        fetch('/api/meeting-logs/' + id, { method: 'DELETE', headers: headersConfig })
          .then(r=>r.json()).then(res=>{ showToast(res.message); refreshTab(); });
      }
    }

    /* -------------------------------------------------------------------------- */
    /* PAYMENTS                                                                   */
    /* -------------------------------------------------------------------------- */
    function renderPayments() {
      const q = document.getElementById('searchPayment').value.toLowerCase();
      const filtered = rawPayments.filter(p => (p.student ? p.student.name.toLowerCase() : '').includes(q) || p.package_period.toLowerCase().includes(q));
      document.getElementById('paymentsBody').innerHTML = filtered.map(p => `
        <tr>
          <td>${p.payment_date ? p.payment_date.substring(0, 10) : '-'}</td>
          <td><strong>${p.student ? p.student.name : '-'}</strong></td>
          <td>${p.package_period}</td>
          <td><strong>Rp ${Number(p.amount).toLocaleString('id-ID')}</strong></td>
          <td>${p.transfer_method}</td>
          <td><span class="badge ${p.payment_status==='Lunas'?'badge-success':'badge-danger'}">${p.payment_status}</span></td>
          <td><button class="btn btn-secondary" onclick="openPaymentModal(${p.id})">✏️</button> <button class="btn btn-danger" onclick="deletePayment(${p.id})">🗑️</button></td>
        </tr>
      `).join('');
    }

    function openPaymentModal(id=null) {
      document.getElementById('paymentIdVal').value = id || '';
      let today = new Date().toISOString().slice(0, 10);

      if(id) {
        let p = rawPayments.find(i=>i.id===id);
        if(p) {
          document.getElementById('paymentModalTitle').innerText = 'Edit Transaksi Pembayaran';
          document.getElementById('paymentStudentId').value = p.student_id;
          document.getElementById('paymentDate').value = p.payment_date ? p.payment_date.substring(0, 10) : today;
          document.getElementById('paymentPeriod').value = p.package_period;
          document.getElementById('paymentAmount').value = p.amount;
          document.getElementById('paymentMethod').value = p.transfer_method;
          document.getElementById('paymentStatus').value = p.payment_status;
        }
      } else {
        document.getElementById('paymentModalTitle').innerText = 'Catat Pembayaran Tagihan';
        document.getElementById('paymentDate').value = today;
        document.getElementById('paymentPeriod').value = '';
        document.getElementById('paymentAmount').value = '';
      }
      document.getElementById('paymentModal').classList.add('active');
    }

    function savePayment(e) {
      e.preventDefault();
      let id = document.getElementById('paymentIdVal').value;
      let url = id ? '/api/payments/' + id : '/api/payments';
      let method = id ? 'PUT' : 'POST';
      let payload = {
        student_id: document.getElementById('paymentStudentId').value,
        payment_date: document.getElementById('paymentDate').value,
        package_period: document.getElementById('paymentPeriod').value,
        amount: document.getElementById('paymentAmount').value,
        transfer_method: document.getElementById('paymentMethod').value,
        payment_status: document.getElementById('paymentStatus').value,
      };
      fetch(url, { method: method, headers: headersConfig, body: JSON.stringify(payload) })
        .then(r=>r.json()).then(res=>{ closeModal('paymentModal'); showToast(res.message); refreshTab(); });
    }

    function deletePayment(id) {
      if(confirm('Hapus transaksi pembayaran me ini?')) {
        fetch('/api/payments/' + id, { method: 'DELETE', headers: headersConfig })
          .then(r=>r.json()).then(res=>{ showToast(res.message); refreshTab(); });
      }
    }

    function closeModal(id) { document.getElementById(id).classList.remove('active'); }

    function showToast(msg) {
      const c = document.getElementById('toastContainer');
      const t = document.createElement('div');
      t.className = 'toast'; t.innerText = '✅ ' + msg;
      c.appendChild(t); setTimeout(() => t.remove(), 3000);
    }
  </script>
</body>
</html>
