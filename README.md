<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# 📝 About This App

Aplikasi Manajemen Optik adalah sistem berbasis web yang dirancang untuk membantu operasional toko optik secara terintegrasi. Aplikasi ini menggabungkan fitur **Point of Sale (POS)** dengan **rekam medis pasien**, sehingga proses penjualan dan pelayanan dapat berjalan lebih efisien dalam satu platform.

Pengguna dapat mengelola transaksi penjualan, mencatat hasil pemeriksaan mata, serta memantau riwayat pasien secara terstruktur.

---

## 🎯 Fitur Utama

### 💳 Point of Sale (POS)
- Transaksi penjualan produk optik (frame, lensa, aksesoris)
- Cetak struk
- Manajemen pembayaran

### 👁️ Rekam Medis Pasien
- Input hasil pemeriksaan mata (minus, plus, silinder, dll)
- Riwayat pemeriksaan pasien
- Data resep kacamata

### 📦 Manajemen Produk & Stok
- Kelola data produk
- Monitoring stok barang
- Kategori produk

### 👥 Manajemen Pelanggan
- Data pasien/customer
- Riwayat transaksi & pemeriksaan

### 📊 Laporan
- Laporan penjualan
- Laporan stok
- Riwayat transaksi

# 📘 Laravel Project Setup & Git Collaboration Guide

Panduan ini dibuat untuk membantu tim dalam menjalankan project Laravel serta workflow Git yang rapi.

---

## 🚀 1. Clone Repository

```bash
git clone <repository-url>
cd <nama-project>
```

---

## ⚙️ 2. Install Dependencies

### Install PHP Dependencies (Composer)

```bash
composer install
```

### Install JavaScript Dependencies (NPM)

```bash
npm install
```

---

## 🔑 3. Setup Environment

Copy file environment:

```bash
cp .env.example .env
```

Generate app key:

```bash
php artisan key:generate
```

Atur konfigurasi database di file `.env`

---

## 🗄️ 4. Database Migration & Seeding

```bash
php artisan migrate --seed
```

---

## ▶️ 5. Run Project

```bash
php artisan serve
npm run dev
```

Akses di browser:

```
http://localhost:8000
```

---

# 🔀 Git Workflow (Team Collaboration)

## 🌿 Branch Structure

* `main` → production (stabil)
* `develop` → development (tempat kerja)
* `feature/*` → fitur per developer

---

## 🧑‍💻 Cara Mulai Kerja

### 1. Pindah ke branch develop

```bash
git checkout develop
```

### 2. Ambil update terbaru

```bash
git pull origin develop
```

### 3. Buat branch fitur (opsional tapi disarankan)

```bash
git checkout -b feature/nama-fitur
```

---

## ✏️ Setelah Coding

### 1. Tambahkan perubahan

```bash
git add .
```

### 2. Commit perubahan

```bash
git commit -m "feat: deskripsi perubahan"
```

### 3. Push ke remote

Jika pakai branch fitur:

```bash
git push origin feature/nama-fitur
```

Jika langsung ke develop:

```bash
git push origin develop
```

---

## 🔁 Update Code dari Tim

```bash
git pull origin develop
```

---

## 🔀 Merge ke Main

Jika fitur sudah selesai dan stabil:

```bash
git checkout main
git pull origin main
git merge develop
git push origin main
```

Atau gunakan Pull Request (disarankan)

---

## ⚠️ Best Practices

* Jangan commit langsung ke `main`
* Selalu `git pull` sebelum mulai kerja
* Gunakan nama commit yang jelas (feat, fix, chore, dll)
* Gunakan branch `feature/*` untuk setiap fitur

---

## 🧪 Troubleshooting

### Error 500 saat import/export

* Cek log: `storage/logs/laravel.log`
* Pastikan format file sesuai
* Validasi data sebelum import

---

## 📌 Catatan

Pastikan:

* PHP versi sesuai
* Composer & Node.js sudah terinstall
* Database sudah dibuat

---

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
