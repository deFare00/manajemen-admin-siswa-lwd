# System Architecture & Design Document (DESIGN.md)
## Sistem Manajemen Siswa Les Privat Coding (Laravel + PostgreSQL + Vercel)

---

### 1. Skema Basis Data Relasional (PostgreSQL / MySQL Migrations)

#### A. Tabel: `students`
| Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | BigIncrements (PK) | Primary Key |
| `student_code` | String (Unique) | Auto-generated ID (contoh: `COD-001`) |
| `name` | String | Nama lengkap siswa |
| `age_level` | String (Nullable) | Usia atau tingkat sekolah (contoh: `SMP Kelas 8`) |
| `parent_email` | String (Nullable) | Email orang tua siswa untuk pengingat pembayaran |
| `programming_lang` | String | Bahasa pemrograman (Python, Web Dev, Scratch, dst.) |
| `schedule_notes` | String (Nullable) | Catatan jadwal belajar (contoh: `Fleksibel`) |
| `learning_system` | Enum (`Paket`, `Bulanan`) | Sistem pembayaran / belajar |
| `package_quota` | Integer (Default: 8) | Sisa sesi paket aktif (diperbarui otomatis via Log) |
| `status` | Enum (`Aktif`, `Cuti`, `Lulus`) | Status keaktifan siswa |
| `timestamps` | Timestamp | `created_at` & `updated_at` |

#### B. Tabel: `meeting_logs`
| Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | BigIncrements (PK) | Primary Key |
| `student_id` | Foreign Key (`students.id`) | Relasi ke siswa |
| `meeting_date` | DateTime | Tanggal & waktu pertemuan fleksibel |
| `session_number` | Integer | Pertemuan ke-berapa |
| `topic` | String | Materi coding yang dipelajari |
| `project_progress` | String (Nullable) | Progress tugas/proyek siswa |
| `rating` | Integer (1-5) | Rating pemahaman siswa |
| `evaluation_notes` | Text (Nullable) | Catatan evaluasi/hambatan dari pengajar |
| `attendance_status` | Enum (`Hadir`, `Izin`, `Alfa`) | Status kehadiran |
| `timestamps` | Timestamp | `created_at` & `updated_at` |

#### C. Tabel: `payments`
| Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | BigIncrements (PK) | Primary Key |
| `student_id` | Foreign Key (`students.id`) | Relasi ke siswa |
| `payment_date` | Date | Tanggal transaksi |
| `package_period` | String | Deskripsi periode/paket (contoh: `Paket 8 Sesi` / `Juli 2026`) |
| `amount` | Decimal (12, 2) | Nominal tagihan / pembayaran |
| `transfer_method` | String | BCA, Mandiri, BNI, BRI, E-Wallet, Cash, Lainnya |
| `payment_status` | Enum (`Lunas`, `Belum Bayar`) | Status kelunasan |
| `timestamps` | Timestamp | `created_at` & `updated_at` |

---

### 2. Arsitektur Kode Laravel (MVC & Eloquent Observers)

#### A. Models & Relasi Eloquent
* `Student` Model:
  * `hasMany(MeetingLog::class)`
  * `hasMany(Payment::class)`
* `MeetingLog` Model:
  * `belongsTo(Student::class)`
* `Payment` Model:
  * `belongsTo(Student::class)`

#### B. Otomatisasi Observer (`StudentObserver` & `MeetingLogObserver`)
1. **Auto Student Code Generator**:
   Saat `Student::creating()`, sistem otomatis menghitung `COD-XXX` berikutnya dan mengisi `student_code`.
2. **Auto Kuota Paket Sync**:
   Saat `MeetingLog::created()` atau `updated()` dengan status `"Hadir"`, sistem menghitung total sesi dibeli dari transaksi `"Lunas"` dikurangi total kehadiran, lalu memperbarui `package_quota` di model `Student`.

#### C. Controllers & RESTful APIs
* `DashboardController`: Menghitung total siswa aktif, total pendapatan bulan berjalan, jumlah tagihan tertunda, dan menyajikan list tagihan belum bayar.
* `StudentController`: CRUD Data Master Siswa.
* `MeetingLogController`: CRUD Log Pertemuan.
* `PaymentController`: CRUD Keuangan & pemicu notifikasi email pengingat.

---

### 3. Konfigurasi Deployment Vercel Serverless (`vercel.json`)

File `vercel.json` dikonfigurasi untuk menjalankan Laravel pada environment Vercel Functions:

```json
{
  "version": 2,
  "framework": null,
  "functions": {
    "api/index.php": {
      "runtime": "vercel-php@0.7.0"
    }
  },
  "routes": [
    {
      "src": "/(.*)",
      "dest": "/api/index.php"
    }
  ],
  "env": {
    "APP_ENV": "production",
    "APP_DEBUG": "false",
    "DB_CONNECTION": "pgsql"
  }
}
```

#### Entry Point Vercel Serverless (`api/index.php`):
Mengarahkan seluruh request Vercel Serverless ke `public/index.php` Laravel.

---

### 4. Langkah-Langkah Deployment ke Vercel

1. **Buat Cloud Database Gratis**:
   * Buat database PostgreSQL di **Vercel Postgres**, **Supabase**, atau **Neon.tech**.
   * Catat string koneksi: `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
2. **Push Repository ke GitHub**:
   * Upload kode Laravel ini ke repositori GitHub Anda.
3. **Deploy di Vercel Dashboard**:
   * Buka [vercel.com](https://vercel.com) > Import Git Repository.
   * Masukkan Environment Variables (`DB_CONNECTION=pgsql`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `APP_KEY`).
   * Klik **Deploy**.
4. **Jalankan Migration**:
   * Jalankan `php artisan migrate` melalui terminal lokal yang terhubung ke Cloud DB, atau via Vercel CLI.