# DOKUMEN PENGUJIAN PERANGKAT LUNAK (SOFTWARE TESTING DOCUMENT)
## JADWALIN — B2B Smart Shift Scheduling Platform
**Platform Penjadwalan Kerja Shift Karyawan Cerdas Berbasis Web & Kepatuhan Regulasi Ketenagakerjaan**

---

### Informasi Dokumen & Tim Pengembang
- **Nama Sistem**: JADWALIN (B2B Smart Shift Scheduling Platform)
- **Mata Kuliah**: Proyek Pembelajaran Berbasis Proyek (Project-Based Learning / PBL) — Semester 5
- **Institusi**: Jurusan Teknologi Informasi, Politeknik Negeri Malang
- **Versi Dokumen**: 1.0 (Dokumen Rencana & Hasil Pengujian)
- **Susunan Tim Pengembang (RACI)**:
  1. **Muhammad Farras A.A** — *Project Manager & Lead Backend Developer*
  2. **Umi Maharani** — *Full-Stack Developer & System Analyst*
  3. **Neyza Ratu Anastasya** — *UI/UX Designer & Technical Writer*
  4. **Muhammad Yusuf** — *Database Designer & System Analyst*

---

## 1. PENDAHULUAN & PENDEKATAN PENGUJIAN

### 1.1 Tujuan Pengujian
Dokumen ini disusun untuk mendokumentasikan spesifikasi skenario pengujian (*Test Scenario*) dan kasus uji (*Test Case*) secara menyeluruh pada platform **Jadwalin**. Tujuan pengujian meliputi:
1. Memastikan seluruh kebutuhan fungsional (FR-01 s.d. FR-25) yang tertuang dalam *System Design Document (SDD)* berjalan sesuai spesifikasi rancangan.
2. Memverifikasi integritas kontrol akses berbasis peran (*Role-Based Access Control / RBAC*) untuk tiga tingkatan aktor: Super Admin, Manajer Operasional, dan Karyawan.
3. Menguji batas toleransi waktu, isolasi transaksi basis data (*concurrency & locking*), serta kalkulasi matematis jam kerja reguler dan lembur sesuai **PP No. 35 Tahun 2021**.
4. Menyediakan artefak uji formal untuk kelengkapan dokumen tugas akhir PBL Semester 5.

### 1.2 Metodologi Pengujian
Pengujian dilakukan menggunakan kombinasi dua pendekatan:
- **Black Box Testing**: Pengujian berfokus pada fungsionalitas input dan output antarmuka pengguna tanpa melihat kode internal:
  - *Equivalence Partitioning*: Membagi himpunan data uji menjadi kelas valid dan tidak valid.
  - *Boundary Value Analysis (BVA)*: Menguji nilai batas kritis (misal: tepat 15 menit *grace period*, 8 jam batas reguler, kuota shift).
  - *Role-Based Access Control (RBAC) Testing*: Menguji rute terlindungi terhadap hak akses yang tidak berhak.
- **Automated Feature & Unit Testing**: Menggunakan framework **PHPUnit** pada Laravel untuk memverifikasi logika model, relasi relasional, dan endpoint HTTP secara otomatis dan regresi.

---

## 2. MATRIKS SKENARIO PENGUJIAN (TEST SCENARIOS)

| ID Skenario | Modul Sistem | Deskripsi Skenario Pengujian | Aktor Terkait | Jenis Pengujian |
| :--- | :--- | :--- | :--- | :--- |
| **TS-01** | Autentikasi & RBAC | Verifikasi proses autentikasi akun, proteksi rute, dan pengalihan dinamis dashboard berdasarkan peran pengguna. | Superadmin, Manager, Employee | Positif, Negatif, Keamanan |
| **TS-02** | Master Data Organisasi | Pengujian pengelolaan data Divisi/Departemen dan Posisi Jabatan serta validasi keunikan entitas per tenant. | Superadmin | Positif, Negatif, Integritas Data |
| **TS-03** | Ketersediaan Waktu (*Availability*) | Pengujian pengajuan halangan kerja oleh staf (*auto-buffer* 30 menit) dan alur persetujuan/penolakan oleh manajer. | Employee, Manager | Positif, Negatif |
| **TS-04** | Master Template & Penjadwalan | Pengujian pembuatan master template shift, alokasi jadwal staf, serta pencegahan bentrok jadwal kerja. | Manager | Positif, Boundary |
| **TS-05** | Presensi Mandiri (*Attendance*) | Pengujian pencatatan *Clock-In*, toleransi keterlambatan (*grace period* 15 menit), pencegahan *double clock-in*, dan *Clock-Out* serta kalkulasi lembur. | Employee | Positif, Negatif, Boundary |
| **TS-06** | Pertukaran Shift (*Shift Swap*) | Pengujian pertukaran shift dua tahap (*Two-Phase Approval*): persetujuan rekan sejawat (*Peer Approval*) dan persetujuan final Manajer. | Employee, Manager | Positif, Negatif, Transaksional |
| **TS-07** | Siaran & Klaim Open Shift | Pengujian penerbitan shift terbuka dan isolasi klaim bersamaan (*Race Condition Prevention*) menggunakan *row-level lock*. | Manager, Employee | Positif, Konkurensi |
| **TS-08** | Timesheet & Ekspor Laporan | Pengujian penyaringan data presensi tim dan pengunduhan berkas laporan rekapitulasi kerja staf (PDF/Excel). | Manager, Superadmin | Positif |

---

## 3. SPESIFIKASI KASUS UJI RINCI (DETAILED TEST CASES)

### Modul 1: Autentikasi & Kontrol Akses Peran (TS-01)

| Test Case ID | Skenario | Deskripsi Kasus Uji | Pra-kondisi | Langkah Pengujian | Data Uji | Hasil yang Diharapkan | Status |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :---: |
| **TC-AUTH-001** | TS-01 | Login dengan kredensial valid (Super Admin) | Akun superadmin aktif di database | 1. Akses halaman `/login`<br>2. Masukkan email & password benar<br>3. Klik tombol 'Log in' | Email: `owner@senjawisata.co.id`<br>Password: `password123` | Login berhasil, dialihkan otomatis ke `/admin/dashboard`, judul dashboard menampilkan 'Super Admin'. | **PASS** |
| **TC-AUTH-002** | TS-01 | Login dengan kredensial valid (Manager) | Akun manajer aktif di database | 1. Akses halaman `/login`<br>2. Masukkan kredensial manajer<br>3. Klik 'Log in' | Email: `manager@senjawisata.co.id`<br>Password: `password123` | Login berhasil, dialihkan otomatis ke `/manager/dashboard`, judul dashboard menampilkan 'Manager'. | **PASS** |
| **TC-AUTH-003** | TS-01 | Login dengan kredensial valid (Employee) | Akun karyawan aktif di database | 1. Akses halaman `/login`<br>2. Masukkan kredensial staf<br>3. Klik 'Log in' | Email: `karyawan@senjawisata.co.id`<br>Password: `password123` | Login berhasil, dialihkan otomatis ke `/employee/dashboard`, menu karyawan aktif. | **PASS** |
| **TC-AUTH-004** | TS-01 | Login dengan kata sandi tidak valid | Pengguna terdaftar di sistem | 1. Masukkan email valid<br>2. Masukkan kata sandi salah<br>3. Klik 'Log in' | Email: `owner@senjawisata.co.id`<br>Password: `salah123` | Sistem menolak login, tetap di `/login`, menampilkan pesan kesalahan: *"These credentials do not match our records."* | **PASS** |
| **TC-AUTH-005** | TS-01 | Pengalihan dinamis rute pembantu `/dashboard` | Sesi aktif sebagai pengguna dengan peran tertentu | 1. Login sebagai karyawan<br>2. Ketik URL `/dashboard` di bilah alamat browser | User Role: `employee` | Sistem mengenali peran dan otomatis me-redirect ke `/employee/dashboard`. | **PASS** |
| **TC-AUTH-006** | TS-01 | Proteksi rute lintas peran (Karyawan akses rute Admin) | Pengguna terautentikasi sebagai `employee` | 1. Login sebagai karyawan<br>2. Paksa navigasi ke URL `/admin/dashboard` atau `/admin/companies` | Role: `employee`<br>URL: `/admin/*` | Akses ditolak dengan status respons HTTP `403 Forbidden`. | **PASS** |
| **TC-AUTH-007** | TS-01 | Proteksi rute tamu (Akses halaman terproteksi tanpa login) | Sesi dalam keadaan logout | 1. Akses langsung URL `/admin/dashboard` tanpa login | Tamu (Guest) | Sistem memblokir akses dan mengalihkan pengguna ke halaman `/login`. | **PASS** |

---

### Modul 2: Master Data Organisasi — Departemen & Posisi (TS-02)

| Test Case ID | Skenario | Deskripsi Kasus Uji | Pra-kondisi | Langkah Pengujian | Data Uji | Hasil yang Diharapkan | Status |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :---: |
| **TC-DEPT-001** | TS-02 | Tambah departemen baru dengan input valid | Login sebagai Super Admin, buka `/admin/departments` | 1. Klik tombol 'Tambah Departemen'<br>2. Masukkan nama & deskripsi<br>3. Klik 'Simpan' | Nama: `Kitchen & Culinary`<br>Deskripsi: `Divisi dapur utama` | Record tersimpan di tabel `departments` dengan `company_id` terkait, dialihkan ke indeks dengan pesan sukses. | **PASS** |
| **TC-DEPT-002** | TS-02 | Tambah departemen dengan nama duplikat pada perusahaan yang sama | Departemen `Food & Beverage` sudah ada di tenant | 1. Buka form tambah departemen<br>2. Masukkan nama yang sudah terdaftar<br>3. Klik 'Simpan' | Nama: `Food & Beverage`<br>Deskripsi: `Duplikat nama` | Sistem menolak penyimpanan, validasi menampilkan pesan: *"Nama departemen sudah digunakan di perusahaan ini."* | **PASS** |
| **TC-DEPT-003** | TS-02 | Perbarui data departemen | Departemen sasaran tersedia | 1. Klik 'Edit' pada baris departemen<br>2. Ubah deskripsi departemen<br>3. Klik 'Perbarui' | Deskripsi baru: `Pembaruan deskripsi` | Data departemen terbarui di basis data, pesan sukses ditampilkan. | **PASS** |
| **TC-DEPT-004** | TS-02 | Tambah posisi jabatan baru terhubung departemen | Departemen `Food & Beverage` tersedia | 1. Buka halaman `/admin/positions`<br>2. Pilih departemen F&B<br>3. Masukkan nama posisi<br>4. Simpan | Posisi: `Head Barista`<br>Departemen: `Food & Beverage` | Posisi tersimpan dengan relasi FK `department_id` yang tepat. | **PASS** |
| **TC-DEPT-005** | TS-02 | Hapus data posisi jabatan | Posisi belum terikat penugasan pengguna aktif | 1. Klik tombol 'Hapus' pada posisi<br>2. Konfirmasi penghapusan | ID Posisi terpilih | Record posisi terhapus dari tabel `positions`, daftar terbarui. | **PASS** |

---

### Modul 3: Pengajuan Ketersediaan Waktu / Halangan Kerja (TS-03)

| Test Case ID | Skenario | Deskripsi Kasus Uji | Pra-kondisi | Langkah Pengujian | Data Uji | Hasil yang Diharapkan | Status |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :---: |
| **TC-AVL-001** | TS-03 | Pengajuan halangan kerja dengan proteksi *Auto-Buffer* | Karyawan login di `/employee/availability` | 1. Pilih tanggal halangan<br>2. Tentukan jam mulai & selesai<br>3. Isi alasan halangan<br>4. Kirim pengajuan | Waktu: 09:00 - 12:00<br>Alasan: `Praktikum Kuliah` | Data tersimpan dengan status `pending`, sistem otomatis memperhitungkan buffer proteksi 30 menit sebelum dan sesudah (08:30 - 12:30). | **PASS** |
| **TC-AVL-002** | TS-03 | Pengajuan halangan dengan format jam tidak valid (Jam Selesai < Jam Mulai) | Form pengajuan ketersediaan terbuka | 1. Isi tanggal halangan<br>2. Masukkan jam selesai lebih awal dari jam mulai<br>3. Kirim | Jam Mulai: 14:00<br>Jam Selesai: 11:00 | Validasi form gagal, sistem menampilkan error: *"Jam selesai harus setelah jam mulai."* | **PASS** |
| **TC-AVL-003** | TS-03 | Persetujuan (*Approval*) halangan staf oleh Manajer | Terdapat pengajuan berstatus `pending` dari anggota tim | 1. Manajer membuka daftar ketersediaan tim<br>2. Klik tombol 'Setujui' (*Approve*) | ID Pengajuan ketersediaan | Status record berubah menjadi `approved`, waktu staf otomatis diisolasi dari plotting jadwal *Smart Scheduler*. | **PASS** |
| **TC-AVL-004** | TS-03 | Penolakan (*Rejection*) halangan staf oleh Manajer | Terdapat pengajuan berstatus `pending` | 1. Manajer membuka pengajuan<br>2. Masukkan catatan alasan penolakan<br>3. Klik 'Tolak' | Status: `rejected`<br>Catatan: `Kebutuhan operasional event` | Status berubah menjadi `rejected`, notifikasi penolakan masuk ke akun staf terkait. | **PASS** |

---

### Modul 4: Presensi Mandiri (*Clock-In*, Toleransi, & *Clock-Out*) (TS-05)

| Test Case ID | Skenario | Deskripsi Kasus Uji | Pra-kondisi | Langkah Pengujian | Data Uji | Hasil yang Diharapkan | Status |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :---: |
| **TC-ATT-001** | TS-05 | Tampilan dashboard presensi karyawan | Staf terdaftar memiliki jadwal hari ini | 1. Buka menu `/employee/attendance` | Penugasan shift tanggal hari ini | Dashboard menampilkan kartu status shift hari ini, jam jadwal mulai/selesai, serta tombol aksi aktif. | **PASS** |
| **TC-ATT-002** | TS-05 | *Clock-In* tepat waktu (Di dalam batas toleransi *Grace Period* 15 mnt) | Jadwal shift: 08:00 - 17:00, pengaturan toleransi = 15 mnt | 1. Staf melakukan Clock-In pada pukul 08:00 s.d. 08:15<br>2. Tekan tombol 'Clock In' | Waktu sistem: `08:05:00` | Status penugasan menjadi `present`, `late_minutes = 0`, tombol berubah menjadi 'Clock Out'. | **PASS** |
| **TC-ATT-003** | TS-05 | *Clock-In* terlambat (Melewati batas *Grace Period* 15 mnt) | Jadwal shift: 08:00 - 17:00, toleransi = 15 mnt | 1. Staf menekan tombol Clock-In pada pukul 08:16 atau lebih | Waktu sistem: `08:16:00` | Status penugasan menjadi `late`, `late_minutes = 16`, waktu clock-in aktual tercatat presisi. | **PASS** |
| **TC-ATT-004** | TS-05 | Pencegahan *Clock-In* ganda (*Double Clock-In*) | Staf sudah berhasil melakukan Clock-In pada hari ini | 1. Kirim ulang request POST ke `/employee/attendance/clock-in` | Record telah terisi `clock_in_time` | Sistem menolak request dan mengembalikan pesan peringatan: *"Anda sudah melakukan clock-in hari ini."* | **PASS** |
| **TC-ATT-005** | TS-05 | Percobaan *Clock-Out* tanpa *Clock-In* terlebih dahulu | Staf belum mencatat Clock-In pada shift terkait | 1. Kirim request POST ke `/employee/attendance/clock-out` | `clock_in_time = null` | Sistem menolak aksi dan menampilkan pesan error: *"Anda belum melakukan clock-in."* | **PASS** |
| **TC-ATT-006** | TS-05 | *Clock-Out* normal & kalkulasi jam lembur (PP 35/2021) | Staf clock-in pukul 08:00, batas jam kerja reguler = 8 jam | 1. Staf menekan tombol 'Clock Out' pada pukul 17:00 (durasi total 9 jam) | Total jam = 9 jam kerja | `clock_out_time` tersimpan, sistem menghitung `regular_hours = 8` dan `overtime_hours = 1`, status shift menjadi `completed`. | **PASS** |

---

### Modul 5: Pertukaran Shift Antar Rekan (*Peer-to-Peer Shift Swap*) (TS-06)

| Test Case ID | Skenario | Deskripsi Kasus Uji | Pra-kondisi | Langkah Pengujian | Data Uji | Hasil yang Diharapkan | Status |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :---: |
| **TC-SWP-001** | TS-06 | Pengajuan permohonan tukar shift ke rekan kerja (*Transfer / Exchange*) | Karyawan A memiliki jadwal shift aktif, rekan B satu departemen | 1. Buka menu `/employee/swaps`<br>2. Pilih shift sendiri & pilih rekan kerja target<br>3. Kirim permohonan | Pengaju: Karyawan A<br>Target: Karyawan B<br>Tipe: `exchange` | Record pada tabel `shift_swaps` tercipta dengan status `pending_peer`, notifikasi terkirim ke Karyawan B. | **PASS** |
| **TC-SWP-002** | TS-06 | Penolakan pertukaran oleh rekan target | Permohonan berstatus `pending_peer` | 1. Karyawan B membuka daftar permohonan tukar<br>2. Klik tombol 'Tolak' | Status swap saat ini: `pending_peer` | Status permohonan diperbarui menjadi `rejected_by_peer`, alur berhenti tanpa membebani manajer. | **PASS** |
| **TC-SWP-003** | TS-06 | Persetujuan rekan target (*Peer Approval*) | Permohonan berstatus `pending_peer` | 1. Karyawan B mengklik tombol 'Setujui' | Status swap saat ini: `pending_peer` | Status permohonan naik ke tingkat `pending_manager`, permohonan muncul di antrean persetujuan manajer. | **PASS** |
| **TC-SWP-004** | TS-06 | Persetujuan final manajer & mutasi jadwal atomik | Permohonan berstatus `pending_manager` | 1. Manajer membuka `/manager/swaps`<br>2. Meninjau detail pertukaran<br>3. Klik 'Setujui Final' | Status swap: `pending_manager` | Transaksi basis data atomik (`DB::transaction`): pemilik `shift_assignments` berpindah secara tepat, status swap `approved`. | **PASS** |

---

### Modul 6: Penanganan Konkurensi Klaim Open Shift (TS-07)

| Test Case ID | Skenario | Deskripsi Kasus Uji | Pra-kondisi | Langkah Pengujian | Data Uji | Hasil yang Diharapkan | Status |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :---: |
| **TC-OPN-001** | TS-07 | Penerbitan Open Shift oleh Manajer | Terdapat slot shift yang belum terisi kuotanya | 1. Buka jadwal tim<br>2. Tandai shift sebagai `open_shift = true`<br>3. Tentukan kuota staf | Shift kuota terbuka: 1 staf | Shift muncul pada daftar klaim terbuka di dashboard seluruh staf divisi yang memenuhi kriteria. | **PASS** |
| **TC-OPN-002** | TS-07 | Pencegahan slot ganda pada klaim simultan (*Pessimistic Locking / Race Condition*) | Tersedia 1 kuota Open Shift | 1. Dua staf (Staf A dan Staf B) mengirim permintaan klaim pada milidetik yang bersamaan | Dua request POST simultan pada slot shift ID yang sama | Staf A (pemenang kunci `lockForUpdate`) berhasil mengklaim shift; Staf B menerima respon kegagalan *"Kuota shift telah habis"*. Tidak terjadi slot ganda. | **PASS** |

---

### Modul 7: Rekapitulasi Timesheet & Ekspor Laporan (TS-08)

| Test Case ID | Skenario | Deskripsi Kasus Uji | Pra-kondisi | Langkah Pengujian | Data Uji | Hasil yang Diharapkan | Status |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :---: |
| **TC-RPT-001** | TS-08 | Filter laporan timesheet berdasarkan bulan dan departemen | Terdapat data presensi shift yang telah selesai | 1. Akses `/manager/timesheets`<br>2. Pilih periode bulan & divisi<br>3. Klik filter | Bulan: `Oktober 2026`<br>Departemen: `F&B` | Tabel rekapitulasi menyajikan total jam reguler, total jam lembur, dan jumlah keterlambatan staf divisi tersebut. | **PASS** |
| **TC-RPT-002** | TS-08 | Unduh laporan timesheet format spreadsheet/PDF | Data hasil filter tampil di layar | 1. Klik tombol 'Ekspor Excel / PDF' | Format dokumen terpilih | Berkas laporan terunduh dengan struktur kolom yang rapi dan kalkulasi total yang akurat. | **PASS** |

---

## 4. HASIL INTEGRASI AUTOMATED TESTING (PENGUJIAN OTOMATIS)

Pengujian kode diuji secara otomatis menggunakan test suite PHPUnit bawaan repositori:

| Kelompok Pengujian | Berkas Kode Uji | Jumlah Uji | Hasil Eksekusi |
| :--- | :--- | :---: | :---: |
| **Model & Relasi (Unit)** | `tests/Unit/ModelsTest.php` | 16 Tests | **PASS (100%)** |
| **Hak Akses & Menu (Feature)** | `tests/Feature/NavigationAndRoleMenuTest.php` | 3 Tests | **PASS (100%)** |
| **Master Data CRUD (Feature)** | `tests/Feature/Admin/DepartmentPositionTest.php` | 5 Tests | **PASS (100%)** |
| **Presensi & Lembur (Feature)** | `tests/Feature/Employee/AttendanceTest.php` | 6 Tests | **PASS (100%)** |
| **Profil Pengguna (Feature)** | `tests/Feature/ProfileTest.php` | 4 Tests | **PASS (100%)** |

---

## 5. KESIMPULAN HASIL PENGUJIAN

Berdasarkan seluruh skenario pengujian fungsional dan otomatis yang telah dieksekusi:
1. **Keandalan Logika Bisnis**: Mekanisme pembagian jam reguler dan lembur sesuai aturan ketenagakerjaan, serta deteksi keterlambatan berbasis *grace period* 15 menit telah terverifikasi bekerja dengan benar.
2. **Keamanan & Isolasi Hak Akses**: Penerapan *Role-Based Access Control* (RBAC) pada level rute HTTP dan pengalihan otomatis peran berhasil mengisolasi hak akses antara Super Admin, Manajer, dan Karyawan tanpa kebocoran otorisasi.
3. **Integritas Transaksi**: Mekanisme transaksi atomik pada alur persetujuan tukar shift dan penguncian baris (*row lock*) pada klaim shift terbuka menjamin ketiadaan anomali data (*race condition*).
4. **Kesiapan Dokumen**: Dokumen ini memenuhi standar uji perangkat lunak untuk penyusunan laporan proyek akhir PBL Semester 5.
