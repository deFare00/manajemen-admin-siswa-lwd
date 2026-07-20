# Panduan Deployment Vercel + Supabase PostgreSQL

Dokumen ini berisi panduan lengkap deployment **Laravel App** ke **Vercel** yang terhubung ke database **Supabase**.

---

## 🔑 Kredensial Environment Variables Vercel

Salin seluruh variabel berikut ke bagian **Environment Variables** saat membuat project di Vercel:

| Key | Value |
| :--- | :--- |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | `base64:c3VwZXJzZWNyZXRrZXlmb3JsYXJhdmVsYXBwbGljYXRpb24xMjM=` |
| `DB_CONNECTION` | `pgsql` |
| `DB_HOST` | `aws-1-ap-south-1.pooler.supabase.com` |
| `DB_PORT` | `6543` |
| `DB_DATABASE` | `postgres` |
| `DB_USERNAME` | `postgres.jruuwqfqmmjdkclfixbe` |
| `DB_PASSWORD` | `Defarhan-101` |
| `DB_SSLMODE` | `require` |

---

## 🚀 Langkah 1: Push Kode ke GitHub

Jalankan perintah berikut di Terminal VS Code Anda:

```bash
# 1. Inisialisasi Git local
git init
git add .
git commit -m "Sistem Manajemen Les Privat Coding"

# 2. Hubungkan ke GitHub (Ganti username-anda & repo-anda)
git branch -M main
git remote add origin https://github.com/username-anda/repo-anda.git
git push -u origin main
```

---

## 🌐 Langkah 2: Import & Deploy di Vercel

1. Buka [vercel.com](https://vercel.com) dan login.
2. Klik tombol **Add New...** > **Project**.
3. Piliha repositori GitHub Anda.
4. Buka bagian **Environment Variables**, masukkan 10 variabel di atas.
5. Klik **Deploy**! Dalam 1-2 menit aplikasi Anda akan aktif secara online.
