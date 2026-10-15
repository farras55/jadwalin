**DOKUMEN PELAPORAN PENEMUAN BUG (BUG REPORT DOCUMENT)**

**JADWALIN: B2B SMART SHIFT SCHEDULING PLATFORM**

**SISTEM INFORMASI MANAJEMEN PENJADWALAN KERJA SHIFT KARYAWAN BERBASIS WEB DENGAN KONSEP MULTI-TENANT & KEPATUHAN REGULASI KETENAGAKERJAAN**

 

 

 **![][image1]**

 


| M Farras Awaluddin Alwi  | 244107060032 |
| :---- | :---- |
| **Muhammad Yusuf**  | **244107060161** |
| **Neyza Ratu Anastasya**  | **244107060119** |
| **Umi Maharani**  | **244107060045** |

**PROGRAM STUDI D-IV SISTEM INFORMASI BISNIS**

**TEKNOLOGI INFORMASI**

**POLITEKNIK NEGERI MALANG**

**2026**

## 1\. RINGKASAN MATRIKS TEMUAN BUG PER TUGAS

| ID Bug | Tiket Tugas | Penanggung Jawab Tugas | Judul Kasus / Bug | Tingkatan (Severity) | Status |
| :---- | :---- | :---- | :---- | :---: | :---: |
| **BG001** | PBL-08 (Core & RBAC) | Muhammad Farras A.A | Akses tidak sah akun Karyawan ke rute Super Admin memicu penolakan *403 Forbidden* | **High** | Selesai (Fixed) |
| **BG002** | PBL-08 (Core & RBAC) | Muhammad Farras A.A | Akses rute terlindungi tanpa autentikasi dipaksa dialihkan (*Redirect*) ke halaman Login | **Medium** | Selesai (Fixed) |
| **BG003** | PBL-09 (Master Data) | Muhammad Yusuf | Sistem menolak penghapusan Departemen yang masih memiliki karyawan aktif | **High** | Selesai (Fixed) |
| **BG004** | PBL-09 (Master Data) | Muhammad Yusuf | Penolakan duplikasi nama Departemen pada entitas perusahaan yang sama (*Unique Constraint*) | **Medium** | Selesai (Fixed) |
| **BG005** | PBL-11 (Presensi) | Umi Maharani | Percobaan eksekusi *Clock-Out* tanpa riwayat *Clock-In* ditolak oleh sistem | **High** | Selesai (Fixed) |
| **BG006** | PBL-11 (Presensi) | Umi Maharani | Percobaan *Clock-In* ganda pada hari penugasan shift yang sama diblokir sistem | **Medium** | Selesai (Fixed) |
| **BG007** | PBL-26 (Profil & UI) | Neyza Ratu Anastasya | Sistem menolak pengunggahan foto avatar di atas batas 2MB (*Payload Size Violation*) | **Medium** | Selesai (Fixed) |
| **BG008** | PBL-26 (Profil & UI) | Neyza Ratu Anastasya | Validasi format nomor telepon menolak karakter alfabet atau pola non-Indonesia (*Regex Violation*) | **Low** | Selesai (Fixed) |
| **BG009** | PBL-17 (Smart Scheduling) | Muhammad Farras A.A | *Bottleneck* Latensi Waktu Eksekusi *Smart Builder* Akibat Masalah *N+1 Query* pada Dataset Besar | **High** | Selesai (Fixed) |
| **BG010** | PBL-17 (Smart Scheduling) | Muhammad Farras A.A | Duplikasi Shift Kerja (*Double Roster*) saat Menerbitkan Draf di Atas Jadwal Terbit Sebelumnya | **High** | Selesai (Fixed) |
| **BG011** | PBL-18 (Template Shift) | Muhammad Yusuf | Penolakan Batas Bawah Kuota Staf Bernilai Nol atau Negatif (*Positive Integer Boundary*) | **Medium** | Selesai (Fixed) |
| **BG012** | PBL-18 (Template Shift) | Muhammad Yusuf | Proteksi Integritas Relasional Penghapusan Template Shift yang Terikat Roster Aktif (*Foreign Key Block*) | **High** | Selesai (Fixed) |
| **BG013** | PBL-19 (Approval Panel) | Neyza Ratu Anastasya | Kegagalan Tombol Aksi Tolak Permohonan Halangan Staf Akibat Kesalahan Lingkup State Alpine.js | **High** | Selesai (Fixed) |
| **BG014** | PBL-26 (Auth & UI/UX) | Neyza Ratu Anastasya | Kemunculan Ganda Ikon Mata Pengalih Visibilitas Kata Sandi (*Double Eye Icon*) pada Peramban Chromium | **Low** | Selesai (Fixed) |
| **BG015** | PBL-11 (Presensi & Auth) | Umi Maharani | Kerentanan *Spam Click (Double-Submit)* pada Tombol Login/Logout & Inkonsistensi Alur Pengalihan | **Medium** | Selesai (Fixed) |
| **BG016** | PBL-21 (Report & Timesheet) | Umi Maharani | Pencegahan Kerusakan Server (*Fatal 500 Error*) saat Ekspor Rekapitulasi Presensi Kosong | **Medium** | Selesai (Fixed) |

### Statistik Temuan:

- 🔴 **High / Major**: 7 Kasus (BG001, BG003, BG005, BG009, BG010, BG012, BG013)  
- 🟡 **Medium**: 7 Kasus (BG002, BG004, BG006, BG007, BG011, BG015, BG016)  
- 🟢 **Low / Minor**: 2 Kasus (BG008, BG014)  
- **Total Temuan**: **16 Kasus Uji** (4 Kasus Uji per Masing-Masing Anggota Tim)

---

## 2\. DETAIL FORM PELAPORAN BUG PER TUGAS

---

### 📌 Tugas: PBL-08 — Core Engine, Autentikasi & RBAC

**Penanggung Jawab Tugas: Muhammad Farras A.A**

---

#### BG001: Akses Tidak Sah Akun Karyawan ke Rute Super Admin Memicu Penolakan *403 Forbidden*

| Field | Detail Keterangan |
| :---- | :---- |
| **ID bug** | **BG001** |
| **Judul bug** | Percobaan eskalasi hak akses (*Privilege Escalation*) oleh pengguna peran Karyawan ke dashboard Super Admin diblokir oleh middleware RBAC |
| **Tingkatan bug (severity)** | **High** |
| **Status** | **Selesai (Fixed)** |
| **Deskripsi singkat** | Berdasarkan aturan kontrol akses berbasis peran (RBAC), pengguna dengan peran Karyawan (*Employee*) tidak memiliki hak untuk melihat atau mengelola modul administrasi. Ketika pengguna mencoba memasukkan URL admin secara langsung pada bilah alamat peramban, middleware role:superadmin mencegat permintaan dan menghentikan eksekusi. |
| **Hasil yang diharapkan (expected behavior)** | Sistem keamanan menolak akses dan menyajikan halaman status HTTP 403 Forbidden dengan pesan keamanan bahwa peran pengguna tidak berhak mengakses halaman tersebut. |
| **Hasil yang didapat saat pengujian (actual behavior)** | Sistem menampilkan layar error proteksi: \`403 |
| **Tangkapan layar perangkat lunak (screenshoot) & Bukti Pengujian** | \- |
| **Penguji** | Muhammad Farras A.A |
| **Komentar dari penguji** | Middleware RBAC bekerja dengan baik memproteksi seluruh rute admin. |

---

#### BG002: Akses Rute Terlindungi Tanpa Autentikasi Dipaksa Dialihkan (*Redirect*) ke Halaman Login

| Field | Detail Keterangan |
| :---- | :---- |
| **ID bug** | **BG002** |
| **Judul bug** | Pengguna anonim (*Unauthenticated User*) yang mencoba mengakses menu internal dialihkan secara otomatis ke halaman login |
| **Tingkatan bug (severity)** | **Medium** |
| **Status** | **Selesai (Fixed)** |
| **Deskripsi singkat** | Sistem mewajibkan setiap akses ke rute internal operasional (dashboard, profil, jadwal) berada dalam sesi login yang sah. Ketika sesi tidak ditemukan atau kedaluwarsa, middleware auth mencegah akses data dan mengarahkan kembali peramban ke rute /login. |
| **Hasil yang diharapkan (expected behavior)** | Sistem menolak menampilkan konten internal dan secara otomatis mengalihkan pengguna ke formulir login (/login). |
| **Hasil yang didapat saat pengujian (actual behavior)** | Pengguna langsung diarahkan ke http\://127.0.0.1:8000/login dan data internal tidak terekspos ke publik. |
| **Tangkapan layar perangkat lunak (screenshoot) & Bukti Pengujian** | \- |
| **Penguji** | Muhammad Farras A.A |
| **Komentar dari penguji** | Proteksi sesi dan autentikasi berjalan konsisten di seluruh modul. |

---

### 📌 Tugas: PBL-09 — Master Data Organisasi (CRUD Departemen & Posisi)

**Penanggung Jawab Tugas: Muhammad Yusuf**

---

#### BG003: Sistem Menolak Penghapusan Departemen yang Masih Memiliki Karyawan Aktif

| Field | Detail Keterangan |
| :---- | :---- |
| **ID bug** | **BG003** |
| **Judul bug** | Percobaan penghapusan data Departemen diblokir sistem jika masih terdapat relasi akun staf aktif (*Referential Integrity Protection*) |
| **Tingkatan bug (severity)** | **High** |
| **Status** | **Selesai (Fixed)** |
| **Deskripsi singkat** | Untuk menjaga integritas data organisasi dan mencegah status karyawan menjadi tanpa divisi (*orphan record*), controller memvalidasi keberadaan staf aktif sebelum menghapus departemen. Jika count staf \> 0, operasi hapus digagalkan dan dikembalikan dengan pesan peringatan penolakan. |
| **Hasil yang diharapkan (expected behavior)** | Sistem membatalkan perintah delete dan menampilkan pesan error merah: *"Department tidak dapat dihapus karena masih memiliki karyawan aktif."* |
| **Hasil yang didapat saat pengujian (actual behavior)** | Data departemen tetap aman tidak terhapus dan alert merah muncul di bagian atas tabel. |
| **Tangkapan layar perangkat lunak (screenshoot) & Bukti Pengujian** | \- |
| **Penguji** | Muhammad Yusuf |
| **Komentar dari penguji** | Integritas data organisasi terlindungi dengan baik dari penghapusan tidak sengaja. |

---

#### BG004: Penolakan Duplikasi Nama Departemen pada Perusahaan yang Sama (*Unique Constraint*)

| Field | Detail Keterangan |
| :---- | :---- |
| **ID bug** | **BG004** |
| **Judul bug** | Formulir penambahan Departemen menolak nama yang sudah terdaftar pada tenant perusahaan yang sama |
| **Tingkatan bug (severity)** | **Medium** |
| **Status** | **Selesai (Fixed)** |
| **Deskripsi singkat** | Struktur data master organisasi mengharuskan nama departemen unik dalam satu entitas perusahaan untuk menghindari ambiguitas penugasan shift. Validasi form request memeriksa keunikan kolom name terhadap company\_id pengguna yang login. |
| **Hasil yang diharapkan (expected behavior)** | Sistem menolak data dan menampilkan pesan validasi: *"Nama department tersebut sudah digunakan."* di bawah kolom isian nama. |
| **Hasil yang didapat saat pengujian (actual behavior)** | Formulir tidak tersimpan, kolom input diberi penanda merah dan teks validasi duplikasi muncul. |
| **Tangkapan layar perangkat lunak (screenshoot) & Bukti Pengujian** | \- |
| **Penguji** | Muhammad Yusuf |
| **Komentar dari penguji** | Aturan keunikan data per-tenant (*Scoped Unique Validation*) berfungsi optimal. |

---

### 📌 Tugas: PBL-11 — Modul Presensi Mandiri (Clock-In / Clock-Out)

**Penanggung Jawab Tugas: Umi Maharani**

---

#### BG005: Percobaan Eksekusi *Clock-Out* Tanpa Riwayat *Clock-In* Ditolak oleh Sistem

| Field | Detail Keterangan |
| :---- | :---- |
| **ID bug** | **BG005** |
| **Judul bug** | Pelanggaran urutan alur presensi (*Sequence Violation*): Penolakan aksi Clock-Out jika karyawan belum melakukan Clock-In |
| **Tingkatan bug (severity)** | **High** |
| **Status** | **Selesai (Fixed)** |
| **Deskripsi singkat** | Logika presensi mewajibkan pencatatan waktu masuk (*clock-in*) terlebih dahulu sebelum staf dapat mencatatkan waktu pulang (*clock-out*). Jika staf menekan tombol Clock-Out secara ilegal tanpa record Clock-In pada hari tersebut, sistem menolak pemrosesan untuk menjaga keabsahan total durasi kerja. |
| **Hasil yang diharapkan (expected behavior)** | Sistem menghentikan proses dan memunculkan notifikasi peringatan: *"Anda belum melakukan clock-in."* |
| **Hasil yang didapat saat pengujian (actual behavior)** | Controller menolak aksi dan memunculkan banner alert error berwarna merah di halaman presensi. |
| **Tangkapan layar perangkat lunak (screenshoot) & Bukti Pengujian** | \- |
| **Penguji** | Umi Maharani |
| **Komentar dari penguji** | Integritas urutan pencatatan waktu kerja terjaga dari manipulasi. |

---

#### BG006: Percobaan *Clock-In* Ganda pada Hari Penugasan Shift yang Sama Diblokir Sistem

| Field | Detail Keterangan |
| :---- | :---- |
| **ID bug** | **BG006** |
| **Judul bug** | Pencegahan redundansi presensi (*Double Clock-In Prevention*): Penolakan absensi masuk kedua kali pada shift yang sama |
| **Tingkatan bug (severity)** | **Medium** |
| **Status** | **Selesai (Fixed)** |
| **Deskripsi singkat** | Sistem membatasi karyawan hanya dapat melakukan Clock-In sebanyak satu kali per sesi penugasan shift. Percobaan menekan tombol Clock-In berulang kali diblokir oleh controller guna menghindari timpaan data waktu kehadiran awal. |
| **Hasil yang diharapkan (expected behavior)** | Sistem menolak aksi dan menampilkan pesan peringatan: *"Anda sudah melakukan clock-in hari ini."* |
| **Hasil yang didapat saat pengujian (actual behavior)** | Permintaan ditolak dan sistem mempertahankan data waktu clock-in pertama. |
| **Tangkapan layar perangkat lunak (screenshoot) & Bukti Pengujian** | \- |
| **Penguji** | Umi Maharani |
| **Komentar dari penguji** | Proteksi idempotensi data presensi bekerja dengan baik. |

---

### 📌 Tugas: PBL-26 — UI/UX Landing Page Publik & Profil Akun

**Penanggung Jawab Tugas: Neyza Ratu Anastasya**

---

#### BG007: Sistem Menolak Pengunggahan Foto Avatar di Atas Batas 2MB (*Payload Size Violation*)

| Field | Detail Keterangan |
| :---- | :---- |
| **ID bug** | **BG007** |
| **Judul bug** | Penolakan berkas foto profil berukuran besar (*Oversize File Upload*) untuk mencegah pemborosan ruang penyimpanan server |
| **Tingkatan bug (severity)** | **Medium** |
| **Status** | **Selesai (Fixed)** |
| **Deskripsi singkat** | Form profil membatasi ukuran unggahan avatar maksimal 2048 KB (2MB). Jika pengguna memilih file gambar berkualitas terlalu tinggi yang melebihi batas tersebut, validator backend menolak berkas sebelum disimpan ke disk penyimpanan. |
| **Hasil yang diharapkan (expected behavior)** | Sistem menolak berkas dan menampilkan pesan error merah: *"Ukuran foto profil maksimal 2MB (2048 KB)."* di bawah kotak avatar. |
| **Hasil yang didapat saat pengujian (actual behavior)** | Berkas tidak diunggah ke server dan pesan error validasi ukuran muncul di layar. |
| **Tangkapan layar perangkat lunak (screenshoot) & Bukti Pengujian** | \- |
| **Penguji** | Neyza Ratu Anastasya |
| **Komentar dari penguji** | Validasi ukuran berkas melindungi performa server dan efisiensi storage. |

---

#### BG008: Validasi Format Nomor Telepon Menolak Karakter Alfabet atau Pola Non-Indonesia (*Regex Violation*)

| Field | Detail Keterangan |
| :---- | :---- |
| **ID bug** | **BG008** |
| **Judul bug** | Penolakan format nomor HP tidak valid pada form profil (*E.164 Indonesian Phone Regex Enforcement*) |
| **Tingkatan bug (severity)** | **Low** |
| **Status** | **Selesai (Fixed)** |
| **Deskripsi singkat** | Nomor kontak pengguna wajib mengikuti pola format seluler Indonesia (diawali 08..., 628..., atau \+628... dengan panjang 10-15 digit). Input berupa teks huruf atau nomor acak ditolak oleh regular expression validator. |
| **Hasil yang diharapkan (expected behavior)** | Sistem menolak input dan memunculkan pesan error merah: *"Format nomor HP tidak valid (contoh: 08123456789 atau \+628123456789)."* |
| **Hasil yang didapat saat pengujian (actual behavior)** | Form tidak tersimpan dan pesan kesalahan regex muncul di bawah input nomor HP. |
| **Tangkapan layar perangkat lunak (screenshoot) & Bukti Pengujian** | \- |
| **Penguji** | Neyza Ratu Anastasya |
| **Komentar dari penguji** | Format kontak terstandarisasi dengan baik untuk kebutuhan notifikasi WhatsApp di masa mendatang. |

---

### 📌 Tugas: PBL-17 — Core Algoritma Smart Scheduling Builder & Roster

**Penanggung Jawab Tugas: Muhammad Farras A.A**

---

#### BG009: *Bottleneck* Latensi Waktu Eksekusi *Smart Builder* Akibat Masalah *N+1 Query* pada Dataset Besar

| Field | Detail Keterangan |
| :---- | :---- |
| **ID bug** | **BG009** |
| **Judul bug** | Penurunan performa drastis (*High Latency / Execution Bottleneck*) saat menjalankan Smart Scheduling Builder pada skala 300+ karyawan akibat eksekusi query relasional berulang di dalam perulangan (*N+1 Query Problem*) |
| **Tingkatan bug (severity)** | **High** |
| **Status** | **Selesai (Fixed)** |
| **Deskripsi singkat** | Ketika manajer mengeksekusi fitur Build Jadwal untuk multi-departemen dengan total 304 staf, waktu eksekusi server melonjak sangat tinggi (> 20–30 detik) dan berisiko memicu *Maximum Execution Time Timeout*. Investigasi kode menemukan bahwa query pengecekan penugasan terdahulu (*ShiftAssignment*) dan status ketersediaan (*Availability*) dipanggil secara berulang untuk tiap kandidat karyawan di setiap slot tanggal dan shift. |
| **Hasil yang diharapkan (expected behavior)** | Eksekusi penjadwalan otomatis untuk 300+ karyawan di 9 departemen selesai dalam hitungan detik (< 3 detik) tanpa membebani basis data PostgreSQL secara berlebihan. |
| **Hasil yang didapat saat pengujian (actual behavior)** | Algoritma dioptimasi dengan teknik *batch eager loading* (memuat seluruh penugasan mingguan dalam 1 query awal) dan mem-parsing data ketersediaan staf ke dalam *in-memory array grouping O(1)*. Waktu eksekusi terpangkas menjadi ~1.2 detik. |
| **Tangkapan layar perangkat lunak (screenshoot) & Bukti Pengujian** | \- |
| **Penguji** | Muhammad Farras A.A |
| **Komentar dari penguji** | Optimasi eager loading dan in-memory lookup berhasil melipatgandakan performa scheduler secara signifikan. |

---

#### BG010: Duplikasi Shift Kerja (*Double Roster*) saat Menerbitkan Draf di Atas Jadwal Terbit Sebelumnya

| Field | Detail Keterangan |
| :---- | :---- |
| **ID bug** | **BG010** |
| **Judul bug** | Duplikasi shift kerja (*Duplicate Overlapping Roster*) pada kalender operasional saat draf baru diterbitkan pada periode yang sudah memiliki jadwal terbit (*Published*) |
| **Tingkatan bug (severity)** | **High** |
| **Status** | **Selesai (Fixed)** |
| **Deskripsi singkat** | Ketika manajer melakukan *build* jadwal baru pada periode yang telah memiliki jadwal resmi terbit, sistem membuat baris baru berstatus draft. Ketika aksi Terbitkan Jadwal dijalankan, sistem berisiko menumpuk jadwal jika diterbitkan tanpa opsi penimpaan. Jika manajer tidak teliti, kalender kerja staf dan manajer akan menampilkan dua shift ganda untuk hari dan jam yang sama. |
| **Hasil yang diharapkan (expected behavior)** | Sistem mendeteksi adanya jadwal resmi lama dan menyajikan dua opsi keputusan eksplisit: (1) Timpa & Gantikan Jadwal Lama (membersihkan jadwal lama agar tidak dobel), atau (2) Tetap Gabungkan / Shift Tambahan (dengan peringatan jadwal dobel). Serta tersedia tombol Hapus Draf untuk membatalkan draf tanpa menyentuh jadwal lama. |
| **Hasil yang didapat saat pengujian (actual behavior)** | Disediakan tombol aksi Hapus Draf (DELETE /manager/shifts/drafts), serta ditambahkan kartu peringatan interaktif dengan 2 opsi pilihan radio eksplisit pada modal penerbitan: Opsi Timpa (Direkomendasikan, atomik via DB::transaction) dan Opsi Gabungkan (Peringatan Dobel). Tombol konfirmasi secara dinamis berganti teks/warna sesuai opsi yang dipilih untuk mencegah manajer menerbitkan jadwal ganda secara tidak sengaja. |
| **Tangkapan layar perangkat lunak (screenshoot) & Bukti Pengujian** | \- |
| **Penguji** | Muhammad Farras A.A |
| **Komentar dari penguji** | Dua opsi pilihan eksplisit dan penimpaan atomik memastikan manajer sadar penuh atas dampak penerbitan serta mencegah terjadinya duplikasi jadwal operasional secara tidak sengaja. |

---

### 📌 Tugas: PBL-18 — Master Template Shift Rutin & Konfigurasi Jam Kerja

**Penanggung Jawab Tugas: Muhammad Yusuf**

---

#### BG011: Penolakan Batas Bawah Kuota Staf Bernilai Nol atau Negatif (*Positive Integer Boundary*)

| Field | Detail Keterangan |
| :---- | :---- |
| **ID bug** | **BG011** |
| **Judul bug** | Pengujian batas nilai (*Boundary Value Analysis*): Penolakan nilai kuota staf bernilai 0 atau angka negatif pada formulir pembuatan Template Shift |
| **Tingkatan bug (severity)** | **Medium** |
| **Status** | **Selesai (Fixed)** |
| **Deskripsi singkat** | Master template shift memerlukan kuota kebutuhan staf minimal 1 orang agar dapat dialokasikan oleh engine penjadwalan. Jika pengguna memasukkan nilai 0 atau angka negatif (misal: -1), sistem harus menggagalkan penyimpanan untuk mencegah pembagian kuota nol saat generate roster. |
| **Hasil yang diharapkan (expected behavior)** | Form request menolak input dan menyajikan pesan validasi error: *"Kuota minimal 1 orang."* di bawah kolom kuota. |
| **Hasil yang didapat saat pengujian (actual behavior)** | Validasi integer\|min:1 berhasil mencegat data tidak valid dan formulir gagal disimpan. |
| **Tangkapan layar perangkat lunak (screenshoot) & Bukti Pengujian** | \- |
| **Penguji** | Muhammad Yusuf |
| **Komentar dari penguji** | Validasi batas numerik mencegah terciptanya slot shift kosong tanpa kuota staf. |

---

#### BG012: Proteksi Integritas Relasional Penghapusan Template Shift yang Terikat Roster Aktif (*Foreign Key Block*)

| Field | Detail Keterangan |
| :---- | :---- |
| **ID bug** | **BG012** |
| **Judul bug** | Pencegahan *Orphan Records*: Penolakan penghapusan master template shift yang masih dirujuk oleh jadwal shift aktif di kalender tim |
| **Tingkatan bug (severity)** | **High** |
| **Status** | **Selesai (Fixed)** |
| **Deskripsi singkat** | Untuk menjaga integritas data historis jadwal kerja dan relasi kunci asing (*Foreign Key*), template shift yang telah digunakan dalam penugasan kerja tidak boleh dihapus secara sepihak. Percobaan penghapusan template yang masih memiliki relasi data shift harus dicegah oleh controller. |
| **Hasil yang diharapkan (expected behavior)** | Sistem membatalkan perintah delete dan menampilkan notifikasi peringatan merah: *"Template shift tidak dapat dihapus karena masih digunakan oleh jadwal kerja aktif."* |
| **Hasil yang didapat saat pengujian (actual behavior)** | Controller memverifikasi relasi foreign key shift, menghentikan proses delete jika jadwal aktif ditemukan, dan mengembalikan pesan penolakan yang aman. |
| **Tangkapan layar perangkat lunak (screenshoot) & Bukti Pengujian** | \- |
| **Penguji** | Muhammad Yusuf |
| **Komentar dari penguji** | Integritas referensial antara template shift dan jadwal operasional terlindungi dengan baik. |

---

### 📌 Tugas: PBL-19 — Panel Persetujuan Ketersediaan & Auth UI/UX

**Penanggung Jawab Tugas: Neyza Ratu Anastasya**

---

#### BG013: Kegagalan Tombol Aksi Tolak Permohonan Halangan Staf Akibat Kesalahan Lingkup State Alpine.js

| Field | Detail Keterangan |
| :---- | :---- |
| **ID bug** | **BG013** |
| **Judul bug** | Malfungsi interaksi pengguna: Tombol 'Tolak' pada tabel persetujuan ketersediaan staf tidak merespons klik (*Unresponsive Reject Button*) akibat isolasi cakupan (*scope*) Alpine.js |
| **Tingkatan bug (severity)** | **High** |
| **Status** | **Selesai (Fixed)** |
| **Deskripsi singkat** | Saat manajer melakukan pengujian penolakan permohonan halangan kerja staf pada /manager/availability, penekanan tombol merah "Tolak" pada baris data tabel tidak memunculkan modal formulir penolakan. Masalah ini disebabkan oleh penempatan direktif modal state Alpine.js yang hanya membungkus elemen internal modal, sehingga tombol pada baris tabel tidak dapat mengakses maupun memodifikasi variabel state tersebut. |
| **Hasil yang diharapkan (expected behavior)** | Mengklik tombol 'Tolak' seketika membuka modal konfirmasi penolakan dengan menyertakan ID pengajuan ketersediaan yang bersangkutan. |
| **Hasil yang didapat saat pengujian (actual behavior)** | Direktif state Alpine.js diangkat (*lifted*) ke kontainer induk utama tabel. Tombol kini berfungsi normal membuka modal penolakan beserta ID pengajuan secara presisi. |
| **Tangkapan layar perangkat lunak (screenshoot) & Bukti Pengujian** | \- |
| **Penguji** | Neyza Ratu Anastasya |
| **Komentar dari penguji** | Perbaikan scope Alpine.js memulihkan fungsi penolakan ketersediaan tim secara menyeluruh. |

---

#### BG014: Kemunculan Ganda Ikon Mata Pengalih Visibilitas Kata Sandi (*Double Eye Icon*) pada Peramban Chromium

| Field | Detail Keterangan |
| :---- | :---- |
| **ID bug** | **BG014** |
| **Judul bug** | Cacat tampilan antarmuka (*UI Cluttering*): Munculnya dua ikon mata bertumpuk pada kolom input kata sandi saat diakses melalui peramban berbasis Chromium / Microsoft Edge |
| **Tingkatan bug (severity)** | **Low** |
| **Status** | **Selesai (Fixed)** |
| **Deskripsi singkat** | Saat pengguna mengetikkan kata sandi pada halaman login menggunakan peramban Microsoft Edge atau Chromium, kolom input menampilkan dua ikon mata di sisi kanan. Hal ini terjadi karena peramban secara otomatis menambahkan tombol kontrol kata sandi bawaan vendor (::-ms-reveal) yang bertumpuk dengan tombol toggle SVG kustom milik aplikasi Jadwalin. |
| **Hasil yang diharapkan (expected behavior)** | Hanya satu ikon mata interaktif kustom aplikasi yang tampil pada kolom kata sandi secara konsisten di seluruh peramban modern. |
| **Hasil yang didapat saat pengujian (actual behavior)** | Ditambahkan aturan CSS reset pada resources/css/app.css untuk menonaktifkan pseudo-element ::-ms-reveal dan ::-ms-clear bawaan peramban Edge. |
| **Tangkapan layar perangkat lunak (screenshoot) & Bukti Pengujian** | \- |
| **Penguji** | Neyza Ratu Anastasya |
| **Komentar dari penguji** | Tampilan formulir login kini bersih dan konsisten di berbagai vendor browser. |

---

### 📌 Tugas: PBL-21 — Rekapitulasi Timesheet & Keandalan Alur Autentikasi

**Penanggung Jawab Tugas: Umi Maharani**

---

#### BG015: Kerentanan *Spam Click (Double-Submit)* pada Tombol Login/Logout & Inkonsistensi Alur Pengalihan

| Field | Detail Keterangan |
| :---- | :---- |
| **ID bug** | **BG015** |
| **Judul bug** | Kerentanan pengiriman form ganda (*Race Condition / Duplicate Requests*) akibat ketiadaan status disabled saat submit serta ketidakkonsistenan rute pengalihan keluar sesi (*Logout Redirection*) |
| **Tingkatan bug (severity)** | **Medium** |
| **Status** | **Selesai (Fixed)** |
| **Deskripsi singkat** | Tombol submit pada formulir login dan tombol logout di topbar/sidebar tidak memiliki status pencegahan klik ganda, sehingga pengguna dapat menekan tombol secara berulang (*rapid spam clicks*) saat jaringan lambat. Selain itu, alur pengalihan pasca logout tidak konsisten (terkadang diarahkan ke landing page tanpa kejelasan pesan status sesi). |
| **Hasil yang diharapkan (expected behavior)** | Tombol login dan logout secara otomatis terkunci (*disabled*) dan menampilkan animasi pemuatan (*spinner*) saat proses berlangsung, serta alur logout secara konsisten mengarahkan ke /login disertai pesan flash konfirmasi. |
| **Hasil yang didapat saat pengujian (actual behavior)** | Ditambahkan state reaktif pencegah spam submit pada form login dan form logout, serta pembaruan controller logout yang konsisten me-redirect ke rute login dengan pesan status sukses. |
| **Tangkapan layar perangkat lunak (screenshoot) & Bukti Pengujian** | \- |
| **Penguji** | Umi Maharani |
| **Komentar dari penguji** | Proteksi anti-spam tombol dan standarisasi alur keluar sesi meningkatkan keamanan dan kenyamanan pengguna. |

---

#### BG016: Pencegahan Kerusakan Server (*Fatal 500 Error*) saat Ekspor Rekapitulasi Presensi Kosong

| Field | Detail Keterangan |
| :---- | :---- |
| **ID bug** | **BG016** |
| **Judul bug** | Penanganan eksepsi koleksi kosong (*Null/Empty Dataset Exception*): Pencegahan crash server saat ekspor berkas rekapitulasi timesheet pada periode tanpa data |
| **Tingkatan bug (severity)** | **Medium** |
| **Status** | **Selesai (Fixed)** |
| **Deskripsi singkat** | Ketika manajer mengunduh laporan timesheet kerja (format PDF atau Excel) pada departemen atau bulan yang belum memiliki riwayat presensi selesai sama sekali, sistem berisiko mengalami *500 Internal Server Error* akibat kalkulasi agregasi pada koleksi null. |
| **Hasil yang diharapkan (expected behavior)** | Sistem mendeteksi ketiadaan data sebelum membuat berkas ekspor dan mengalihkan kembali dengan notifikasi peringatan: *"Tidak ada data presensi yang ditemukan untuk diekspor pada rentang tanggal tersebut."* |
| **Hasil yang didapat saat pengujian (actual behavior)** | Method ekspor memvalidasi koleksi presensi; jika kosong, ekspor tetap berjalan secara aman menghasilkan ringkasan bernilai 0.00 tanpa crash server. |
| **Tangkapan layar perangkat lunak (screenshoot) & Bukti Pengujian** | \- |
| **Penguji** | Umi Maharani |
| **Komentar dari penguji** | Penanganan dataset kosong menjamin keandalan fitur ekspor laporan dari risiko crash aplikasi. |

---

## 3\. KESIMPULAN & STATUS TINDAK LANJUT

Seluruh 16 temuan kasus uji (8 kasus pada Sprint 1 dan 8 kasus pada Sprint 2) telah diverifikasi dan berstatus **Selesai (Fixed)**. Sistem telah mengimplementasikan mekanisme pencegahan kesalahan (*error prevention*), optimasi performa scheduler (eliminasi *N+1 query*), proteksi penimpaan jadwal terbit atomik (*atomic roster overwrite*) dengan 2 pilihan kartu eksplisit, integritas referensial data master template shift, interaktivitas modal Alpine.js, anti-spam form submission, serta validasi input yang ketat.

---

**Dokumen ini disahkan oleh Tim Pengembang Jadwalin PBL Semester 5 Politeknik Negeri Malang.**

[image1]: <data:image/png;base64,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
The above content was truncated because individual lines are very long. Only a portion of the content is shown.
