# Backend Sistem Keuangan Perusahaan

Sistem API backend manajemen keuangan (pemasukan & pengeluaran) menggunakan **Laravel 11**, **MySQL**, dan **Laravel Sanctum**.

## 🚀 Fitur Utama
- **Autentikasi:** Login & Logout menggunakan Laravel Sanctum.
- **Kategori:** Management kategori pemasukan (`income`) dan pengeluaran (`expense`).
- **Transaksi Pengeluaran:** Catat pengeluaran yang secara otomatis memotong sisa anggaran (*remaining budget*).
- **Transaksi Pemasukan:** Catat pemasukan yang secara otomatis menambah total saldo & sisa anggaran.
- **Ringkasan Anggaran:** Endpoint untuk kalkulasi total budget, sisa budget, total pemasukan, dan total pengeluaran.
- **Ekspor Laporan:** Endpoint laporan dalam format PDF dan Excel (.xlsx).

## 🛠️ Instalasi & Setup Lokal

1. **Clone Repositori**
   ```bash
   git clone [https://github.com/zaqiabdillah123/backend-keuangan.git](https://github.com/zaqiabdillah123/backend-keuangan.git)
   cd backend-keuangan
Instal Dependensi PHP

Bash
composer install
Konfigurasi Environment
Salin .env.example menjadi .env dan sesuaikan koneksi database MySQL Anda:

Bash
cp .env.example .env
Generate App Key & Jalankan Migrasi Data

Bash
php artisan key:generate
php artisan migrate:fresh --seed
Jalankan Server Lokal

Bash
php artisan serve
📬 Koleksi Postman
Berkas pengujian API tersedia pada file backend-keuangan.postman_collection.json yang dapat diimpor langsung ke Postman.


Setelah menyimpan file `README.md`, jalankan perintah berikut di Git Bash untuk mengunggahnya ke GitHub:
```bash
git add README.md
git commit -m "docs: add README documentation"
git push