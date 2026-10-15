# DOKUMEN PELAPORAN PENEMUAN BUG (BUG REPORT DOCUMENT) — SPRINT 1
## JADWALIN — B2B Smart Shift Scheduling Platform
**Platform Penjadwalan Kerja Shift Karyawan Cerdas Berbasis Web & Kepatuhan Regulasi Ketenagakerjaan**

---

### Informasi Dokumen & Tim Pengembang
- **Nama Sistem**: JADWALIN (B2B Smart Shift Scheduling Platform)
- **Mata Kuliah**: Proyek Pembelajaran Berbasis Proyek (Project-Based Learning / PBL) — Semester 5
- **Institusi**: Jurusan Teknologi Informasi, Politeknik Negeri Malang
- **Fase Proyek**: Evaluasi Sprint 1 (Autentikasi, RBAC, Master Data Organisasi, Presensi & Clock-in/Clock-out, UI Profil)
- **Tiket Terkait**: PBL-08, PBL-09, PBL-11, PBL-26
- **Tanggal Pengujian**: 08 Oktober 2026
- **Metode Pengujian**: Pengujian Cacat Sistem & Skenario Negatif (Non-Human Error), Automated Testing, & Manual Verification
- **Susunan Tim Pengembang (RACI)**:
  1. **Muhammad Farras A.A** — *Project Manager & Lead Backend Developer*
  2. **Muhammad Yusuf** — *Database Designer & System Analyst*
  3. **Umi Maharani** — *Full-Stack Developer & System Analyst*
  4. **Neyza Ratu Anastasya** — *UI/UX Designer & Technical Writer*

---

## 1. RINGKASAN MATRIKS TEMUAN BUG PER TUGAS

| ID Bug | Tiket Tugas | Penanggung Jawab Tugas | Judul Kasus / Bug | Tingkatan (Severity) | Status |
| :--- | :--- | :--- | :--- | :---: | :---: |
| **BG001** | PBL-08 (Core & RBAC) | Muhammad Farras A.A | Akses tidak sah akun Karyawan ke rute Super Admin memicu penolakan *403 Forbidden* | **High** | Selesai (Fixed) |
| **BG002** | PBL-08 (Core & RBAC) | Muhammad Farras A.A | Akses rute terlindungi tanpa autentikasi dipaksa dialihkan (*Redirect*) ke halaman Login | **Medium** | Selesai (Fixed) |
| **BG003** | PBL-09 (Master Data) | Muhammad Yusuf | Sistem menolak penghapusan Departemen yang masih memiliki karyawan aktif | **High** | Selesai (Fixed) |
| **BG004** | PBL-09 (Master Data) | Muhammad Yusuf | Penolakan duplikasi nama Departemen pada entitas perusahaan yang sama (*Unique Constraint*) | **Medium** | Selesai (Fixed) |
| **BG005** | PBL-11 (Presensi) | Umi Maharani | Percobaan eksekusi *Clock-Out* tanpa riwayat *Clock-In* ditolak oleh sistem | **High** | Selesai (Fixed) |
| **BG006** | PBL-11 (Presensi) | Umi Maharani | Percobaan *Clock-In* ganda pada hari penugasan shift yang sama diblokir sistem | **Medium** | Selesai (Fixed) |
| **BG007** | PBL-26 (Profil & UI) | Neyza Ratu Anastasya | Sistem menolak pengunggahan foto avatar di atas batas 2MB (*Payload Size Violation*) | **Medium** | Selesai (Fixed) |
| **BG008** | PBL-26 (Profil & UI) | Neyza Ratu Anastasya | Validasi format nomor telepon menolak karakter alfabet atau pola non-Indonesia (*Regex Violation*) | **Low** | Selesai (Fixed) |

### Statistik Temuan:
- 🔴 **High / Major**: 3 Kasus (BG001, BG003, BG005)
- 🟡 **Medium**: 4 Kasus (BG002, BG004, BG006, BG007)
- 🟢 **Low / Minor**: 1 Kasus (BG008)
- **Total Temuan**: **8 Kasus Uji** (2 Kasus Uji per Masing-Masing Anggota Tim)

---

## 2. DETAIL FORM PELAPORAN BUG PER TUGAS

---

### 📌 Tugas: PBL-08 — Core Engine, Autentikasi & RBAC
**Penanggung Jawab Tugas: Muhammad Farras A.A**

---

#### BG001: Akses Tidak Sah Akun Karyawan ke Rute Super Admin Memicu Penolakan *403 Forbidden*

| Field | Detail Keterangan |
| :--- | :--- |
| **ID bug** | **BG001** |
| **Judul bug** | Percobaan eskalasi hak akses (*Privilege Escalation*) oleh pengguna peran Karyawan ke dashboard Super Admin diblokir oleh middleware RBAC |
| **Tingkatan bug (severity)** | **High** |
| **Status** | **Selesai (Fixed)** |
| **Deskripsi singkat** | Berdasarkan aturan kontrol akses berbasis peran (RBAC), pengguna dengan peran Karyawan (*Employee*) tidak memiliki hak untuk melihat atau mengelola modul administrasi. Ketika pengguna mencoba memasukkan URL admin secara langsung pada bilah alamat peramban, middleware `role:superadmin` mencegat permintaan dan menghentikan eksekusi. |
| **Hasil yang diharapkan (expected behavior)** | Sistem keamanan menolak akses dan menyajikan halaman status HTTP `403 Forbidden` dengan pesan keamanan bahwa peran pengguna tidak berhak mengakses halaman tersebut. |
| **Hasil yang didapat saat pengujian (actual behavior)** | Sistem menampilkan layar error proteksi: `403 | USER DOES NOT HAVE THE RIGHT ROLES.`, mencegah kebocoran data administratif. |
| **Tangkapan layar perangkat lunak (screenshoot) & Bukti Pengujian** | - |
| **Penguji** | Muhammad Farras A.A |
| **Komentar dari penguji** | Middleware RBAC bekerja dengan baik memproteksi seluruh rute admin. |

---

#### BG002: Akses Rute Terlindungi Tanpa Autentikasi Dipaksa Dialihkan (*Redirect*) ke Halaman Login

| Field | Detail Keterangan |
| :--- | :--- |
| **ID bug** | **BG002** |
| **Judul bug** | Pengguna anonim (*Unauthenticated User*) yang mencoba mengakses menu internal dialihkan secara otomatis ke halaman login |
| **Tingkatan bug (severity)** | **Medium** |
| **Status** | **Selesai (Fixed)** |
| **Deskripsi singkat** | Sistem mewajibkan setiap akses ke rute internal operasional (dashboard, profil, jadwal) berada dalam sesi login yang sah. Ketika sesi tidak ditemukan atau kedaluwarsa, middleware `auth` mencegah akses data dan mengarahkan kembali peramban ke rute `/login`. |
| **Hasil yang diharapkan (expected behavior)** | Sistem menolak menampilkan konten internal dan secara otomatis mengalihkan pengguna ke formulir login (`/login`). |
| **Hasil yang didapat saat pengujian (actual behavior)** | Pengguna langsung diarahkan ke `http://127.0.0.1:8000/login` dan data internal tidak terekspos ke publik. |
| **Tangkapan layar perangkat lunak (screenshoot) & Bukti Pengujian** | - |
| **Penguji** | Muhammad Farras A.A |
| **Komentar dari penguji** | Proteksi sesi dan autentikasi berjalan konsisten di seluruh modul. |

---

### 📌 Tugas: PBL-09 — Master Data Organisasi (CRUD Departemen & Posisi)
**Penanggung Jawab Tugas: Muhammad Yusuf**

---

#### BG003: Sistem Menolak Penghapusan Departemen yang Masih Memiliki Karyawan Aktif

| Field | Detail Keterangan |
| :--- | :--- |
| **ID bug** | **BG003** |
| **Judul bug** | Percobaan penghapusan data Departemen diblokir sistem jika masih terdapat relasi akun staf aktif (*Referential Integrity Protection*) |
| **Tingkatan bug (severity)** | **High** |
| **Status** | **Selesai (Fixed)** |
| **Deskripsi singkat** | Untuk menjaga integritas data organisasi dan mencegah status karyawan menjadi tanpa divisi (*orphan record*), controller memvalidasi keberadaan staf aktif sebelum menghapus departemen. Jika count staf > 0, operasi hapus digagalkan dan dikembalikan dengan pesan peringatan penolakan. |
| **Hasil yang diharapkan (expected behavior)** | Sistem membatalkan perintah delete dan menampilkan pesan error merah: *"Department tidak dapat dihapus karena masih memiliki karyawan aktif."* |
| **Hasil yang didapat saat pengujian (actual behavior)** | Data departemen tetap aman tidak terhapus dan alert merah muncul di bagian atas tabel. |
| **Tangkapan layar perangkat lunak (screenshoot) & Bukti Pengujian** | - |
| **Penguji** | Muhammad Yusuf |
| **Komentar dari penguji** | Integritas data organisasi terlindungi dengan baik dari penghapusan tidak sengaja. |

---

#### BG004: Penolakan Duplikasi Nama Departemen pada Perusahaan yang Sama (*Unique Constraint*)

| Field | Detail Keterangan |
| :--- | :--- |
| **ID bug** | **BG004** |
| **Judul bug** | Formulir penambahan Departemen menolak nama yang sudah terdaftar pada tenant perusahaan yang sama |
| **Tingkatan bug (severity)** | **Medium** |
| **Status** | **Selesai (Fixed)** |
| **Deskripsi singkat** | Struktur data master organisasi mengharuskan nama departemen unik dalam satu entitas perusahaan untuk menghindari ambiguitas penugasan shift. Validasi form request memeriksa keunikan kolom `name` terhadap `company_id` pengguna yang login. |
| **Hasil yang diharapkan (expected behavior)** | Sistem menolak data dan menampilkan pesan validasi: *"Nama department tersebut sudah digunakan."* di bawah kolom isian nama. |
| **Hasil yang didapat saat pengujian (actual behavior)** | Formulir tidak tersimpan, kolom input diberi penanda merah dan teks validasi duplikasi muncul. |
| **Tangkapan layar perangkat lunak (screenshoot) & Bukti Pengujian** | - |
| **Penguji** | Muhammad Yusuf |
| **Komentar dari penguji** | Aturan keunikan data per-tenant (*Scoped Unique Validation*) berfungsi optimal. |

---

### 📌 Tugas: PBL-11 — Modul Presensi Mandiri (Clock-In / Clock-Out)
**Penanggung Jawab Tugas: Umi Maharani**

---

#### BG005: Percobaan Eksekusi *Clock-Out* Tanpa Riwayat *Clock-In* Ditolak oleh Sistem

| Field | Detail Keterangan |
| :--- | :--- |
| **ID bug** | **BG005** |
| **Judul bug** | Pelanggaran urutan alur presensi (*Sequence Violation*): Penolakan aksi Clock-Out jika karyawan belum melakukan Clock-In |
| **Tingkatan bug (severity)** | **High** |
| **Status** | **Selesai (Fixed)** |
| **Deskripsi singkat** | Logika presensi mewajibkan pencatatan waktu masuk (*clock-in*) terlebih dahulu sebelum staf dapat mencatatkan waktu pulang (*clock-out*). Jika staf menekan tombol Clock-Out secara ilegal tanpa record Clock-In pada hari tersebut, sistem menolak pemrosesan untuk menjaga keabsahan total durasi kerja. |
| **Hasil yang diharapkan (expected behavior)** | Sistem menghentikan proses dan memunculkan notifikasi peringatan: *"Anda belum melakukan clock-in."* |
| **Hasil yang didapat saat pengujian (actual behavior)** | Controller menolak aksi dan memunculkan banner alert error berwarna merah di halaman presensi. |
| **Tangkapan layar perangkat lunak (screenshoot) & Bukti Pengujian** | - |
| **Penguji** | Umi Maharani |
| **Komentar dari penguji** | Integritas urutan pencatatan waktu kerja terjaga dari manipulasi. |

---

#### BG006: Percobaan *Clock-In* Ganda pada Hari Penugasan Shift yang Sama Diblokir Sistem

| Field | Detail Keterangan |
| :--- | :--- |
| **ID bug** | **BG006** |
| **Judul bug** | Pencegahan redundansi presensi (*Double Clock-In Prevention*): Penolakan absensi masuk kedua kali pada shift yang sama |
| **Tingkatan bug (severity)** | **Medium** |
| **Status** | **Selesai (Fixed)** |
| **Deskripsi singkat** | Sistem membatasi karyawan hanya dapat melakukan Clock-In sebanyak satu kali per sesi penugasan shift. Percobaan menekan tombol Clock-In berulang kali diblokir oleh controller guna menghindari timpaan data waktu kehadiran awal. |
| **Hasil yang diharapkan (expected behavior)** | Sistem menolak aksi dan menampilkan pesan peringatan: *"Anda sudah melakukan clock-in hari ini."* |
| **Hasil yang didapat saat pengujian (actual behavior)** | Permintaan ditolak dan sistem mempertahankan data waktu clock-in pertama. |
| **Tangkapan layar perangkat lunak (screenshoot) & Bukti Pengujian** | - |
| **Penguji** | Umi Maharani |
| **Komentar dari penguji** | Proteksi idempotensi data presensi bekerja dengan baik. |

---

### 📌 Tugas: PBL-26 — UI/UX Landing Page Publik & Profil Akun
**Penanggung Jawab Tugas: Neyza Ratu Anastasya**

---

#### BG007: Sistem Menolak Pengunggahan Foto Avatar di Atas Batas 2MB (*Payload Size Violation*)

| Field | Detail Keterangan |
| :--- | :--- |
| **ID bug** | **BG007** |
| **Judul bug** | Penolakan berkas foto profil berukuran besar (*Oversize File Upload*) untuk mencegah pemborosan ruang penyimpanan server |
| **Tingkatan bug (severity)** | **Medium** |
| **Status** | **Selesai (Fixed)** |
| **Deskripsi singkat** | Form profil membatasi ukuran unggahan avatar maksimal 2048 KB (2MB). Jika pengguna memilih file gambar berkualitas terlalu tinggi yang melebihi batas tersebut, validator backend menolak berkas sebelum disimpan ke disk penyimpanan. |
| **Hasil yang diharapkan (expected behavior)** | Sistem menolak berkas dan menampilkan pesan error merah: *"Ukuran foto profil maksimal 2MB (2048 KB)."* di bawah kotak avatar. |
| **Hasil yang didapat saat pengujian (actual behavior)** | Berkas tidak diunggah ke server dan pesan error validasi ukuran muncul di layar. |
| **Tangkapan layar perangkat lunak (screenshoot) & Bukti Pengujian** | - |
| **Penguji** | Neyza Ratu Anastasya |
| **Komentar dari penguji** | Validasi ukuran berkas melindungi performa server dan efisiensi storage. |

---

#### BG008: Validasi Format Nomor Telepon Menolak Karakter Alfabet atau Pola Non-Indonesia (*Regex Violation*)

| Field | Detail Keterangan |
| :--- | :--- |
| **ID bug** | **BG008** |
| **Judul bug** | Penolakan format nomor HP tidak valid pada form profil (*E.164 Indonesian Phone Regex Enforcement*) |
| **Tingkatan bug (severity)** | **Low** |
| **Status** | **Selesai (Fixed)** |
| **Deskripsi singkat** | Nomor kontak pengguna wajib mengikuti pola format seluler Indonesia (diawali `08...`, `628...`, atau `+628...` dengan panjang 10-15 digit). Input berupa teks huruf atau nomor acak ditolak oleh regular expression validator. |
| **Hasil yang diharapkan (expected behavior)** | Sistem menolak input dan memunculkan pesan error merah: *"Format nomor HP tidak valid (contoh: 08123456789 atau +628123456789)."* |
| **Hasil yang didapat saat pengujian (actual behavior)** | Form tidak tersimpan dan pesan kesalahan regex muncul di bawah input nomor HP. |
| **Tangkapan layar perangkat lunak (screenshoot) & Bukti Pengujian** | - |
| **Penguji** | Neyza Ratu Anastasya |
| **Komentar dari penguji** | Format kontak terstandarisasi dengan baik untuk kebutuhan notifikasi WhatsApp di masa mendatang. |

---

## 3. KESIMPULAN & STATUS TINDAK LANJUT

Seluruh 8 temuan kasus uji skenario negatif dan integritas sistem (Non-Human Error) pada Sprint 1 telah diverifikasi dan berstatus **Selesai (Fixed)**. Sistem telah mengimplementasikan mekanisme pencegahan kesalahan (*error prevention*), proteksi hak akses (RBAC), integritas referensial data, serta validasi input yang ketat sebelum melangkah ke Sprint 2.

---
**Dokumen ini disahkan oleh Tim Pengembang Jadwalin PBL Semester 5 Politeknik Negeri Malang.**
