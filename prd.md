# Product Requirement Document (PRD)
## Sistem Manajemen Siswa Les Privat Coding (Laravel + Cloud Database + Vercel)

### 1. Pendahuluan & Latar Belakang
Sebagai seorang pengajar les privat coding, pengelolaan data siswa, log progres pembelajaran, dan status pembayaran sering kali tersebar dan sulit dipantau jika dilakukan secara manual. Untuk memastikan profesionalisme dan keberlanjutan usaha privat, diperlukan sebuah aplikasi web modern berbasis **Laravel (PHP 8.2+)** yang terhubung dengan **Cloud PostgreSQL Database (Vercel Postgres / Supabase / Neon)** dan siap dideploy ke **Vercel**.

### 2. Tujuan Sistem
* **Database Master Siswa Terpusat**: Menyediakan basis data profil siswa, kontak email orang tua, sistem belajar, dan status keaktifan yang terstruktur.
* **Pencatatan Pertemuan Harian**: Mencatat setiap log pertemuan, progres materi coding, rating pemahaman (1-5), serta presensi dengan jadwal mengajar yang fleksibel.
* **Manajemen Keuangan Transparan**: Mengelola administrasi arus kas (pembayaran paket/bulanan) agar tidak ada tagihan yang terlewat.
* **Dashboard Analytics Real-Time**: Menyediakan ringkasan KPI (Total Siswa Aktif, Pendapatan Bulan Ini, Total Sesi, dan Tagihan Belum Bayar).
* **Kemudahan Deployment**: Siap dideploy secara serverless ke platform **Vercel** dengan cloud database terpisah.

---

### 3. Struktur Modul & Arsitektur Data
Aplikasi terdiri dari 4 modul utama:
1. **Dashboard Analytics**: Halaman utama berisi metrik bisnis (Siswa Aktif, Pendapatan Bulan Ini, Belum Bayar, Total Sesi) dan tabel daftar tagihan tertunda.
2. **Data Master Siswa (`students`)**: Database profil siswa, kontak ortu, bahasa pemrograman, catatan jadwal fleksibel, sistem belajar (Paket/Bulanan), sisa kuota sesi, dan status (Aktif, Cuti, Lulus).
3. **Log Pertemuan (`meeting_logs`)**: Riwayat tanggal & waktu pertemuan, materi coding, rating pemahaman (1-5), progress project, catatan evaluasi, dan presensi (Hadir, Izin, Alfa).
4. **Keuangan & Pembayaran (`payments`)**: Rekam jejak arus kas masuk, tanggal bayar, nominal, periode/paket, metode transfer (BCA, Mandiri, E-Wallet, Cash), dan status kelunasan (Lunas, Belum Bayar).

---

### 4. Kebutuhan Fungsional & Fitur Utama (Laravel Eloquent & Business Logic)
* **Otomatisasi ID Siswa (Student Code)**: Membuat ID Siswa unik (Format: `COD-001`, `COD-002`, dst.) secara otomatis via Laravel Observer / Model Event ketika siswa baru dibuat.
* **Kalkulasi Sisa Kuota Paket Otomatis**: Setiap kali log pertemuan disimpan dengan presensi "Hadir", Eloquent Observer akan memperbarui sisa kuota sesi paket siswa di tabel `students` secara otomatis.
* **Dropdown Relasional Dinamis**: Dropdown nama siswa pada form Log Pertemuan dan Keuangan mengambil relasi data `Student` yang aktif.
* **Pengingat Pembayaran (Email Notification)**: Fitur pengiriman email pengingat pembayaran ke email orang tua untuk transaksi berstatus "Belum Bayar" menggunakan Laravel Mail.

---

### 5. Kebutuhan Non-Fungsional
* **Kecepatan & Performa**: Aplikasi dibangun dengan arsitektur serverless ringan menggunakan Laravel 11/10 dan disajikan via Vercel Serverless Functions.
* **Desain UI Premium**: Antarmuka berbasis Blade / Web UI dengan tema Dark Slate modern, responsive, interaktif (AJAX CRUD / Modal Form).
* **Penyimpanan Data Terdistribusi**: Menggunakan Cloud PostgreSQL (Supabase / Vercel Postgres / Neon) untuk menjamin persistensi data di lingkungan serverless.