# JADWALIN &mdash; B2B Smart Shift Scheduling Platform

<p align="center">
  <img src="public/images/logo.svg" width="120" height="120" alt="Jadwalin Logo">
</p>

<p align="center">
  <strong>Platform B2B Berbasis Web untuk Otomatisasi Penjadwalan Roster Kerja Karyawan</strong><br>
  <em>Proyek PBL (Project-Based Learning) &mdash; Semester 5</em>
</p>

---

## 🚀 Tech Stack

- **Framework**: Laravel 11.x (PHP 8.2+)
- **Styling**: Tailwind CSS (Custom Ungu/Indigo Theme `#6C5CE7`)
- **Interactivity**: Alpine.js
- **Database**: PostgreSQL (Supabase) / SQLite (Local/Testing)
- **Icons**: Lucide Icons
- **Testing**: PHPUnit / Pest Feature Tests

---

## 🛠️ Panduan Onboarding Rekan Tim (Local Setup)

Ikuti langkah-langkah di bawah ini untuk menjalankan repositori ini di perangkat lokal Anda:

### 1. Clone & Masuk ke Direktori
```bash
git clone <repository-url>
cd jadwalin
```

### 2. Install Dependensi PHP & Node.js
```bash
composer install
npm install
```

### 3. Konfigurasi Environment (`.env`)
Salin file `.env.example` ke `.env` lalu generate encryption key:
```bash
cp .env.example .env
php artisan key:generate
```
*Catatan: Pastikan konfigurasi database di file `.env` sudah sesuai dengan koneksi database lokal / Supabase Anda.*

### 4. Migrasi & Seeding Database
Jalankan migrasi 14 tabel basis data beserta data awal seeder:
```bash
php artisan migrate:fresh --seed
```

### 5. Jalankan Development Server
Buka dua terminal terpisah:
```bash
# Terminal 1: Vite Asset Server
npm run dev

# Terminal 2: Laravel Server
php artisan serve
```
Buka peramban pada tautan: [**http://127.0.0.1:8000**](http://127.0.0.1:8000).

---

## 👤 Akun Uji Coba (Pre-seeded Accounts)

Semua akun menggunakan kata sandi bawaan: **`password123`**

| Role | Email Login | Hak Akses Utama |
| :--- | :--- | :--- |
| **Super Admin** | `owner@senjawisata.co.id` | Konfigurasi platform, kelola perusahaan tenant, master data |
| **Manager** | `manager@senjawisata.co.id` | Plotting jadwal shift, persetujuan tukar shift, pantau presensi tim |
| **Karyawan** | `karyawan@senjawisata.co.id` | Jadwal personal, ajukan tukar shift, catat absensi mandiri, ketersediaan |

---

## 📐 Panduan Arsitektur & Template Proyek

Proyek ini telah dilengkapi struktur fondasi siap pakai:

### 1. Desain & Komponen Layout
- **Layout Utama**: Bungkus view modul Anda dengan `<x-app-layout>`:
  ```blade
  <x-app-layout>
      <x-slot name="header">
          <h1 class="text-xl font-bold text-[#2D2A3E]">Nama Modul</h1>
      </x-slot>
      
      {{-- Konten Modul Anda --}}
  </x-app-layout>
  ```
- **Responsif Otomatis**: Layout secara otomatis beralih antara *Sidebar Sticky* di laptop dan *Mobile Drawer Off-Canvas* di layar HP.
- **Palet Warna Resmi**:
  - Primary: `#6C5CE7` (`bg-[#6C5CE7]`, `text-[#6C5CE7]`)
  - Hover / Deep: `#4F46E5`
  - Background: `#F5F3FF`
  - Border: `#E2E0F7`
  - Text: `#2D2A3E`

### 2. Struktur Routing & RBAC
Rute telah dikelompokkan dengan proteksi middleware `role:{role}` di [`routes/web.php`](routes/web.php):
- Prefix `/admin/` &rarr; name: `admin.*` (Hanya `superadmin`)
- Prefix `/manager/` &rarr; name: `manager.*` (Hanya `manager`)
- Prefix `/employee/` &rarr; name: `employee.*` (Hanya `employee`)

Saat mengembangkan fitur baru, arahkan route di `routes/web.php` ke Controller fitur Anda menggantikan view `placeholder`.

---

## 🧪 Menjalankan Automated Tests

Pastikan seluruh pengujian tetap hijau (*passing*) sebelum melakukan *push*:
```bash
php artisan test
```
Semua 23 pengujian unit & feature saat ini berstatus **100% PASSING**.
