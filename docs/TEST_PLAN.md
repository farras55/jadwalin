## 1\. Test Plan Identifier

| Parameter | Keterangan |
| :---- | :---- |
| **Nama dokumen** | Rencana Pengujian (Test Plan) Jadwalin: B2B Smart Shift Scheduling Platform |
| **Nomor Identifier** | Jadwalin\_TP\_1.0 |
| **Versi** | 1.0 / Revisi 0 |
| **Tanggal** | 08 Oktober 2026 |
| **Perangkat lunak yang diuji** | Jadwalin: B2B Smart Shift Scheduling Platform v1.0 (web, Laravel 11 \+ PostgreSQL Supabase, Multi-Tenant B2B) |
| **Standar dan template** | IEEE Std 829; template IEEE 829 Test Plan Example |

### Riwayat Revisi:

| Revisi | Tanggal | Deskripsi | Penyusun |
| :---: | :---: | :---- | :---: |
| 0 | 08-10-2026 | Penyusunan awal test plan komprehensif berdasarkan SKPL & SDD Revisi 0 | QA Team (Jadwalin) |

---

## 2\. References

Dokumen yang menjadi acuan dalam menyusun test plan ini:

1. **Dokumen Spesifikasi Kebutuhan Perangkat Lunak (SKPL) & System Design Document (SDD) Jadwalin**  
   Bagian yang dipakai adalah identifikasi 18 use case, 25 functional requirements (FR-01 s.d. FR-25), spesifikasi non-fungsional, arsitektur multi-tenant company\_id, pemodelan basis data 14 entitas, serta algoritma *Smart Scheduling Builder*.  
2. **Proposal PBL Jadwalin (Semester 5\)**  
   Bagian yang dipakai: ruang lingkup sistem, kriteria keberhasilan proyek, pembagian peran tim (RACI Matrix), dan linimasa sprint pengembangan.  
3. **Peraturan Pemerintah (PP) No. 35 Tahun 2021**  
   Ketentuan batas jam kerja reguler (maksimal 40 jam/minggu), batas lembur (maksimal 4 jam/hari atau 18 jam/minggu), serta hak istirahat mingguan karyawan.  
4. **IEEE Std 829-2008**, *Standard for Software and System Test Documentation*.  
5. **Dokumentasi Resmi Laravel 11.x Testing Framework**, PHPUnit, Pest PHP, dan Tailwind CSS/Alpine.js sebagai acuan standar implementasi tooling pengujian.

---

## 3\. Introduction

Dokumen ini dibuat sebagai pedoman pelaksanaan pengujian pada sistem **Jadwalin: B2B Smart Shift Scheduling Platform**. Jadwalin adalah platform otomasi penyusunan roster shift kerja karyawan berbasis web cerdas yang mengintegrasikan kepatuhan regulasi ketenagakerjaan Indonesia. Sistem ini mengadopsi model arsitektur *Multi-Tenant B2B*, di mana satu basis data bersama diisolasi secara logis antar perusahaan/cabang menggunakan partisi company\_id.

Sistem dibangun menggunakan **Laravel 11.x** dan **PostgreSQL (Supabase)** serta dioperasikan oleh tiga tingkatan aktor:

1. **Super Admin / Owner**: Pengelola entitas tenant perusahaan, lisensi, pengaturan regulasi ketenagakerjaan, departemen, dan pengguna global.  
2. **Manager (Manajer Operasional)**: Pengelola master shift template, perancang draf jadwal tim (*Smart Scheduling Builder*), pemverifikasi halangan waktu staf, persetujuan akhir tukar shift, penerbit shift terbuka (*Open Shift*), dan pemantau presensi tim.  
3. **Employee (Karyawan / Staf)**: Pengguna kalender shift pribadi, pengaju ketersediaan waktu kerja (*Availability* dengan auto-buffer), pelaku presensi mandiri (*Clock-In/Clock-Out*), pengaju/penerima tukar shift (*Shift Swap*), dan pengklaim shift terbuka (*Open Shift Claim*).

### Test plan ini mendukung tujuan berikut:

1. Mendefinisikan ruang lingkup, strategi, dan tingkatan pengujian secara berjenjang mulai dari *Unit Testing* per fitur/model, *Integration Testing* lintas modul dan basis data, hingga *End-to-End (E2E) Testing* berbasis skenario pengguna nyata.  
2. Mengkomunikasikan kepada pihak terkait (developer, QA tester, dosen pembimbing/pengampu) mengenai item yang diuji, jadwal pengujian, kebutuhan lingkungan uji, dan pembagian tanggung jawab.  
3. Menetapkan kriteria kelulusan (*Pass/Fail Criteria*) serta kriteria penghentian sementara (*Suspension Criteria*) dan pelanjutan (*Resumption Criteria*).  
4. Menjadi fondasi acuan dalam penyusunan berkas *Test Scenario*, *Test Cases*, eksekusi pengetesan lapangan, dan penerbitan *Test Summary Report*.

Pengujian dilakukan bertahap, dimulai dari unit kode secara terisolasi, integrasi antar-relasi database dan middleware RBAC, hingga pengujian alur bisnis utuh lewat browser. Pengujian juga mencakup validasi kebutuhan non-fungsional (keamanan isolasi data, waktu respon komputasi jadwal, dan responsivitas layar mobile).

---

## 4\. Test Items

Item yang diuji adalah aplikasi web Jadwalin yang dikelompokkan ke dalam 10 modul utama berikut:

| ID | Test Item | Cakupan Pengujian | Versi |
| :---- | :---- | :---- | :---: |
| **MOD-01** | Modul Autentikasi dan Otorisasi (RBAC) | Registrasi tenant, login, proteksi session/CSRF, logout, serta middleware pengalihan peran (superadmin, manager, employee). | v1.0 |
| **MOD-02** | Lapisan Multi-Tenant B2B | Pemisahan data berbasis company\_id, pencegahan *cross-tenant data leakage*, dan pembatasan akses data operasional antar entitas bisnis. | v1.0 |
| **MOD-03** | Modul Master Organisasi & Pengguna | Pengelolaan data Perusahaan, Cabang, Departemen, Posisi Jabatan, serta akun pengguna per level peran. | v1.0 |
| **MOD-04** | Modul Parameter Regulasi Ketenagakerjaan | Konfigurasi batas jam kerja reguler (40 jam/minggu), batas lembur (18 jam/minggu), toleransi keterlambatan (*grace period* 15 mnt), dan jeda istirahat (*auto-buffer* 30 mnt). | v1.0 |
| **MOD-05** | Modul Ketersediaan Waktu (*Availability*) | Formulir pendaftaran waktu halangan kerja staf, penambahan *buffer time* 30 menit otomatis, serta verifikasi persetujuan/penolakan oleh manajer. | v1.0 |
| **MOD-06** | Modul Master Template Shift | Pengaturan template jam kerja rutin, kode warna visual, durasi jam, dan kuota default kebutuhan staf per divisi. | v1.0 |
| **MOD-07** | Modul Smart Scheduling Builder | Algoritma penyusunan roster otomatis bebas bentrok, filter staf berhalangan (*approved availability*), dan peringatan batas maksimal jam kerja. | v1.0 |
| **MOD-08** | Modul Presensi Mandiri (*Attendance*) | Pencatatan *Clock-In*, deteksi menit keterlambatan (*Late Status*), *Clock-Out*, otomatisasi *Auto Clock-Out*, serta kalkulasi jam lembur (PP 35/2021). | v1.0 |
| **MOD-09** | Modul Pertukaran Shift (*Shift Swap*) | Alur tukar shift mandiri (*Transfer & Exchange*) dengan verifikasi dua tahap (*Two-Phase Approval*: Persetujuan Rekan Sejawat & Manajer). | v1.0 |
| **MOD-10** | Modul Open Shift & Konkurensi | Penerbitan jadwal shift kosong dan penanganan klaim rebutan shift secara simultan (*Race Condition Prevention*) menggunakan *Pessimistic Row Lock*. | v1.0 |
| **MOD-11** | Modul Rekapitulasi Timesheet & Laporan | Penyaringan data jam kerja reguler vs lembur, kalkulasi akumulasi bulanan, dan ekspor dokumen laporan (PDF & Excel Spreadsheet). | v1.0 |

Pada **unit testing**, komponen yang diuji adalah method model Eloquent, controller, service layer, dan validasi request form. Pada **integration testing**, yang diuji adalah kolaborasi antarmodul, middleware RBAC dengan route, dan integritas transaksi database PostgreSQL. Pada **E2E testing**, aplikasi diuji secara komprehensif melalui web browser pada skenario alur kerja nyata. Nomor build dan commit Git dicatat secara berkala pada *Test Execution Log*.

---

## 5\. Features To Be Tested

Fitur yang termasuk dalam pengujian diprioritaskan menggunakan klasifikasi:

- **H (High)**: Fitur inti operasional dan keamanan data. Jika gagal, alur bisnis utama berhenti.  
- **M (Medium)**: Fitur pendukung kelancaran alur operasional.  
- **L (Low)**: Fitur tampilan antarmuka, tata letak visual, atau informasi pelengkap.

Level pengujian diklasifikasikan menjadi: **U** \= Unit Testing, **I** \= Integration Testing, **E** \= End-to-End Testing.

### 5.1 Fitur Fungsional yang Diuji

| ID | Fitur | Aktor | Prior. | Modul | Level |
| :---- | :---- | :---: | :---: | :---: | :---: |
| **F-01** | Autentikasi (Login, Logout, Session, Password Hashing) | Semua | H | MOD-01 | U, I, E |
| **F-02** | Pengalihan dinamis rute peran (/dashboard → role dashboard) | Semua | H | MOD-01 | U, I, E |
| **F-03** | Isolasi data multi-tenant berbasis company\_id | Semua | H | MOD-02 | U, I, E |
| **F-04** | Pengelolaan profil perusahaan dan zona waktu operasional | Super Admin | M | MOD-03 | U, I |
| **F-05** | CRUD Departemen & Divisi kerja operasional | Super Admin | H | MOD-03 | U, I, E |
| **F-06** | CRUD Posisi/Jabatan karyawan terhubung ke departemen | Super Admin | H | MOD-03 | U, I, E |
| **F-07** | Pengelolaan akun pengguna, penetapan peran, dan status keaktifan | Super Admin | H | MOD-03 | U, I, E |
| **F-08** | Pengaturan parameter batas jam kerja dan grace period (PP 35/2021) | Super Admin | H | MOD-04 | U, I |
| **F-09** | Pengajuan waktu halangan kerja mandiri oleh staf | Employee | H | MOD-05 | U, I, E |
| **F-10** | Penambahan *buffer time* 30 menit otomatis pada jadwal halangan | Sistem | H | MOD-05 | U, I |
| **F-11** | Review dan persetujuan/penolakan halangan kerja staf | Manager | H | MOD-05 | U, I, E |
| **F-12** | Pengelolaan template master shift (jam, durasi, kode warna, kuota) | Manager | H | MOD-06 | U, I, E |
| **F-13** | Eksekusi *Smart Scheduling Builder* dengan deteksi bentrok jadwal | Manager | H | MOD-07 | U, I, E |
| **F-14** | Publikasi jadwal draf kerja tim (*Publish Roster*) | Manager | H | MOD-07 | U, I, E |
| **F-15** | Tampilan kalender jadwal kerja mingguan personal staf | Employee | M | MOD-07 | U, I, E |
| **F-16** | Presensi masuk (*Clock-In*) dengan toleransi *grace period* 15 menit | Employee | H | MOD-08 | U, I, E |
| **F-17** | Deteksi status keterlambatan (*Late Status*) dan pencatatan menit terlambat | Sistem | H | MOD-08 | U, I |
| **F-18** | Pencegahan *Clock-In* ganda pada penugasan shift yang sama | Employee | H | MOD-08 | U, I, E |
| **F-19** | Presensi keluar (*Clock-Out*) dan kalkulasi pemisahan jam lembur | Employee | H | MOD-08 | U, I, E |
| **F-20** | Pencegahan *Clock-Out* tanpa *Clock-In* terlebih dahulu | Employee | M | MOD-08 | U, I |
| **F-21** | Otomatisasi penutupan shift lewat waktu (*Auto Clock-Out Worker*) | Sistem | M | MOD-08 | U, I |
| **F-22** | Pengajuan permohonan tukar shift ke rekan kerja (*Swap Request*) | Employee | H | MOD-09 | U, I, E |
| **F-23** | Konfirmasi persetujuan/penolakan dari rekan target (*Peer Approval*) | Employee | H | MOD-09 | U, I, E |
| **F-24** | Persetujuan final manajer dan mutasi kepemilikan shift atomik | Manager | H | MOD-09 | U, I, E |
| **F-25** | Penerbitan jadwal shift kosong tak bertuan (*Open Shift Broadcast*) | Manager | H | MOD-10 | U, I, E |
| **F-26** | Klaim slot shift kosong dengan isolasi kunci baris (*lockForUpdate*) | Employee | H | MOD-10 | U, I, E |
| **F-27** | Monitoring kehadiran langsung (*Realtime Attendance Board*) | Manager | M | MOD-08 | U, I, E |
| **F-28** | Rekapitulasi timesheet kerja tim dan filter periode/divisi | Manager | M | MOD-11 | U, I, E |
| **F-29** | Ekspor laporan timesheet berkala ke format Excel dan PDF | Manager, Admin | M | MOD-11 | U, I, E |

### 5.2 Kebutuhan Non-Fungsional yang Diuji

| ID | Fitur | Fokus Pengujian | Prior. | Level |
| :---- | :---- | :---- | :---: | :---: |
| **NF-01** | Performa & Waktu Respon | \- Waktu eksekusi algoritma *Smart Scheduling Builder* untuk 50 staf tidak melebihi 3 detik pada kondisi beban standar. \- Waktu respon rata-rata pemrosesan HTTP CRUD presensi di bawah 300 milidetik. | H | I, E |
| **NF-02** | Kompatibilitas Browser & Mobile View | Antarmuka responsif (*Mobile-First*) diuji pada peramban Google Chrome, Mozilla Firefox, dan Safari (tampilan mobile dan desktop). | M | E |
| **NF-03** | Keamanan & Integritas Transaksi | \- Pembatasan rute RBAC (percobaan akses URL tanpa otorisasi mengembalikan respons 403 Forbidden). \- Integritas mutasi penugasan shift menggunakan transaksi basis data atomik (DB::transaction). \- Perlindungan proteksi token CSRF pada seluruh form POST/PATCH/DELETE. \- Perlindungan sesi pengguna pasca-logout. | H | I, E |
| **NF-04** | Usability & Desain Bahasa | Antarmuka berbahasa Indonesia yang konsisten, pesan peringatan kesalahan form jelas, serta penataan tata letak kalender shift intuitif. | L | E |

---

## 6\. Features Not To Be Tested

Bagian ini menetapkan batasan hal-hal yang tidak diuji, baik yang berada di luar lingkup pengembangan aplikasi maupun batasan metodologi yang ditetapkan oleh tim QA.

### 6.1 Di Luar Lingkup Pengembangan (Out-of-Scope)

1. **Integrasi Payment Gateway**: Sistem tidak melakukan transfer gaji antar bank secara otomatis; data rekapitulasi diserahkan via dokumen ekspor timesheet ke tim finance.  
2. **Perangkat Keras Pemindai Biometrik Sidik Jari Fisik**: Presensi dilakukan mandiri melalui aplikasi berbasis web terotentikasi, bukan via mesin absensi eksternal.  
3. **Pelacakan Posisi GPS Secara Live Realtime Berkelanjutan**: Sistem tidak memantau pergerakan fisik staf secara *background live-tracking*.  
4. **Pengiriman Notifikasi SMS/WhatsApp Berbayar**: Notifikasi sistem memanfaatkan fasilitas *In-App Notification* dan email internal platform.  
5. **Manajemen Pajak PPh 21 dan Iuran BPJS Ketenagakerjaan**: Sistem berfokus pada penjadwalan dan jam kerja, bukan kalkulasi perpajakan gaji bersih.

### 6.2 Dikecualikan dari Pengujian (Testing Exclusions)

1. **Aplikasi Mobile Native**: Pengujian perangkat bergerak dilakukan melalui web browser perangkat (*Mobile Web Responsive View*).  
2. **Unit Test Kode Bawaan Framework**: Kode internal framework Laravel, library Composer, dan pustaka Tailwind CSS tidak diuji unit secara mandiri.  
3. **Browser Legacy Non-Modern**: Browser usang yang tidak mendukung standar CSS Modern (seperti Internet Explorer) tidak diuji.  
4. **Pengukuran Uptime 24/7 Jangka Panjang**: Uji ketersediaan sistem hanya diverifikasi sepanjang siklus pengujian lokal dan *staging environment*.  
5. **Stress Test Skala Enterprise Ekstrem**: Pengujian konkurensi difokuskan pada skenario perebutan kuota shift oleh puluhan karyawan, bukan serangan *Distributed Denial of Service (DDoS)* ribuan request per detik.  
6. **Hosting & Konfigurasi Server Produksi**: Konfigurasi deployment tingkat infrastruktur bare-metal berada di luar batasan evaluasi tugas perkuliahan PBL ini.

---

## 7\. Approach (Pendekatan & Strategi Pengujian)

### 7.1 Strategi Tingkat Pengujian

Pengujian dilaksanakan secara berjenjang dari unit terkecil hingga alur operasional utuh. Tahap berikutnya baru dieksekusi apabila kriteria kelulusan tahap sebelumnya telah terpenuhi.

| Aspek | Unit Testing | Integration Testing | E2E Testing |
| :---- | :---- | :---- | :---- |
| **Tujuan** | Memastikan kebenaran logika method model, validasi data, dan relasi secara terisolasi. | Memastikan interaksi antar-modul, rute controller, middleware RBAC, dan transaksi database PostgreSQL berjalan benar. | Memastikan alur bisnis operasional lengkap bekerja sempurna dari sudut pandang interaksi pengguna di peramban web. |
| **Penguji** | Lead Backend Developer & Database Designer (White-box). | Backend Developer & QA Tester. | QA Tester & Seluruh Anggota Tim (Black-box). |
| **Objek dan Teknik** | \- 14 Model Eloquent, Relasi, Mutator, dan Aturan Form Request. \- Teknik Black-box & White-box. | \- Interaksi Controller, Service Layer, Middleware Role, dan Database Supabase. \- Pengujian data persisten sungguhan. | \- Skenario interaksi Use Case yang melibatkan Super Admin, Manajer, dan Karyawan. \- Pengujian browser pada mode desktop & mobile viewport. |
| **Tools (Usulan)** | PHPUnit, Mockery, Carbon Test Time Mock, Database Refresh. | Laravel Feature Test, PostgreSQL Database, Postman API. | Google Chrome, Firefox, Mobile View DevTools, Manual Guided Script. |
| **Data Uji** | Model Factory & Seeder Minimalis. | Seeder percontohan multi-tenant (JadwalinSeeder), data shift dummy. | Data simulasi operasional lengkap (data tenant PT Senja Wisata Nusantara). |
| **Pencatatan** | Laporan test runner PHPUnit CLI (php artisan test). | Test execution log \+ assertion log. | Lembar kasus uji terdokumentasi \+ tangkapan layar bukti uji (*screenshots*). |

---

### 7.2 Unit Testing per Modul

Setiap modul diuji pada lapisan model, relasi relasional, dan *business logic rule*. Dependensi eksternal (seperti waktu dan tanggal) dimanipulasi menggunakan bantuan *Carbon Test Time*.

| Modul | Objek yang Diuji | Kunci Kondisi Uji | Est. TC |
| :---- | :---- | :---- | :---: |
| **MOD-01** | Otentikasi dan helper peran pada model User (isSuperadmin(), isManager(), isEmployee()). | Role valid, role salah, verifikasi hashed password Bcrypt, helper employment type. | 16 |
| **MOD-02** | Isolasi tenant: kepemilikan Foreign Key company\_id pada 14 model relasional. | Kueri lintas company\_id, penolakan data tanpa referensi perusahaan valid. | 14 |
| **MOD-03** | Relasi Master Data: Company, Department, Position, dan keunikan nama departemen per tenant. | Nama departemen kembar dalam satu perusahaan, integritas relasi foreign key posisi ke departemen. | 18 |
| **MOD-04** | Model CompanySetting dan parameter ketenagakerjaan (maks reguler 40 jam, lembur 18 jam, toleransi keterlambatan). | Nilai batas negatif, nilai default konfigurasi, casting data boolean & integer. | 12 |
| **MOD-05** | Model Availability dan kalkulasi penambahan jeda waktu *Auto-Buffer* 30 menit sebelum dan sesudah. | Jam selesai sebelum jam mulai, tumpang tindih waktu halangan, status pending, approved, rejected. | 16 |
| **MOD-06** | Model ShiftTemplate dan default kuota kebutuhan staf per departemen. | Format jam 24-jam, kode warna hex visual, durasi jam kerja shift. | 14 |
| **MOD-07** | Model Shift dan deteksi bentrok jadwal pada ShiftAssignment. | Penugasan ganda staf pada jam yang sama, pelanggaran batas maksimal kuota shift harian. | 22 |
| **MOD-08** | Model ShiftAssignment, fungsi pencatatan *Clock-In*, *Clock-Out*, toleransi 15 mnt, dan pemisahan jam lembur. | Clock-in tepat waktu vs terlambat, pencegahan clock-in ganda, clock-out tanpa clock-in, kalkulasi reguler \> 8 jam. | 24 |
| **MOD-09** | Model ShiftSwap, status permohonan dua tahap, dan helper swap type (isTransfer(), isExchange()). | Transisi status pending\_peer → pending\_manager → approved, validasi kepemilikan shift. | 20 |
| **MOD-10** | Model OpenShiftClaim, status klaim terbuka, dan integritas kunci baris kuota. | Pengambilan kuota yang sudah bernilai nol, klaim oleh staf yang telah memiliki jadwal bentrok. | 18 |
| **MOD-11** | Model TimesheetReport, kalkulasi agregat total jam kerja bulanan dan total denda menit terlambat. | Akurasi sumasi jam reguler dan jam lembur per individu staf. | 16 |

> **Total Estimasi**: ±190 kasus uji pada level Unit Test otomatis.

---

### 7.3 Integration Testing

Pengujian integrasi menggunakan pendekatan *bottom-up* dimulai dari pengujian modul autentikasi dan isolasi tenant, dilanjutkan dengan modul alur transaksi operasional presensi dan pertukaran jadwal.

| ID | Modul Terkait | Skenario Integrasi | Kebutuhan Fitur |
| :---- | :---- | :---- | :---- |
| **INT-01** | MOD-01, MOD-02 | Karyawan berhasil login dan hanya dapat membaca jadwal shift miliknya pada perusahaan yang bersangkutan. Percobaan membuka rute admin ditolak (403). | F-01, F-02, F-03 |
| **INT-02** | MOD-03, MOD-01 | Super Admin membuat departemen dan posisi baru, kemudian mendaftarkan akun karyawan pada posisi tersebut. Akun dapat login dan mewarisi data departemen. | F-05, F-06, F-07 |
| **INT-03** | MOD-05, MOD-07 | Pengajuan halangan waktu staf yang telah disetujui (*Approved Availability*) otomatis terbaca oleh mesin *Smart Scheduling* sebagai slot terlarang. | F-09, F-10, F-13 |
| **INT-04** | MOD-06, MOD-07 | Manajer memilih template shift dan mengalokasikan kuota; sistem membentuk draf penugasan pada tabel shift\_assignments berstatus *Draft*. | F-12, F-13, F-14 |
| **INT-05** | MOD-08, MOD-04 | Karyawan melakukan *Clock-In* 16 menit pasca jam mulai shift; sistem mencatat status late dan menghitung late\_minutes \= 16 mengacu pada grace\_period \= 15\. | F-16, F-17, F-08 |
| **INT-06** | MOD-08, MOD-04 | Karyawan bekerja selama 10 jam operasional; saat *Clock-Out*, sistem memecah jam kerja menjadi 8 jam reguler dan 2 jam lembur sesuai PP 35/2021. | F-19, F-08 |
| **INT-07** | MOD-09, MOD-01 | Staf A mengajukan tukar shift ke Staf B. Status permohonan berpindah ke antrean Staf B. Staf B menyetujui, permohonan otomatis berpindah ke antrean Manajer. | F-22, F-23 |
| **INT-08** | MOD-09, MOD-02 | Manajer memberikan persetujuan final tukar shift; dalam blok DB::transaction, kepemilikan jadwal Staf A dan Staf B saling bertukar tanpa inkonsistensi data. | F-24, NF-03 |
| **INT-09** | MOD-10, MOD-01 | Dua staf mengirimkan request klaim pada 1 slot Open Shift secara bersamaan; transaksi dengan lockForUpdate() meloloskan staf pertama dan menolak staf kedua. | F-25, F-26, NF-03 |
| **INT-10** | MOD-08, MOD-11 | Data presensi staf yang berstatus selesai (*completed*) otomatis diagregasi ke dalam tampilan dashboard monitoring manajer dan rekap timesheet. | F-27, F-28, F-29 |

---

### 7.4 End-to-End (E2E) Testing

E2E dijalankan melalui browser web oleh penguji manusia dengan memainkan skenario peran pengguna secara bergantian pada lingkungan pengujian *staging*:

| ID | Skenario E2E | Langkah Utama & Hasil yang Diharapkan | Prioritas |
| :---- | :---- | :---- | :---: |
| **E2E-01** | **Siklus Lengkap Penjadwalan Kerja Tim (Normal Path)** | Super Admin menyiapkan departemen & akun staf → Manajer menyusun jadwal kerja mingguan via Smart Scheduler → Manajer mempublikasikan jadwal → Karyawan melihat penugasan pada kalender shift pribadinya. | **H** |
| **E2E-02** | **Siklus Presensi Mandiri & Perhitungan Lembur** | Karyawan membuka dashboard mobile → melakukan Clock-In tepat waktu → status berubah menjadi *Present* → karyawan menyelesaikan jam kerja → melakukan Clock-Out → sistem merangkum jam kerja reguler dan lembur. | **H** |
| **E2E-03** | **Siklus Presensi Terlambat (Late Penalty Flow)** | Karyawan melakukan Clock-In melampaui batas *grace period* 15 menit → sistem menandai badge kuning *Late* → menit terlambat muncul di dashboard staf & terakumulasi pada laporan manajer. | **H** |
| **E2E-04** | **Siklus Pengajuan Halangan Kerja Ber-buffer** | Karyawan mendaftarkan jadwal kuliah (10:00 \- 12:00) → Manajer menerima permohonan & memberikan persetujuan → jadwal staf terkunci (09:30 \- 12:30) sehingga manajer tidak dapat menugaskan staf pada jam tersebut. | **H** |
| **E2E-05** | **Siklus Pertukaran Shift Dua Tahap (Two-Phase Swap)** | Staf A mengajukan pertukaran shift ke Staf B → Staf B meninjau dan menyetujui di antarmukanya → notifikasi masuk ke Manajer → Manajer meninjau dan menyetujui final → jadwal kalender kedua staf otomatis bertukar. | **H** |
| **E2E-06** | **Siklus Penolakan Tukar Shift (Rejection Flow)** | Staf A mengajukan tukar shift → Staf B menolak karena berhalangan → permohonan berstatus ditolak (*Rejected*) → alur berhenti tanpa perlu diproses manajer. | **M** |
| **E2E-07** | **Siklus Perebutan Klaim Shift Terbuka (Open Shift Race)** | Manajer menerbitkan 1 slot Open Shift lembur → dua staf mengklik tombol klaim dalam waktu hampir bersamaan → sistem memberikan konfirmasi berhasil kepada pemenang dan pesan 'kuota habis' kepada pengklaim kedua. | **H** |
| **E2E-08** | **Pembatasan Akses Lintas Peran & Tenant (Security Test)** | Karyawan mencoba membuka URL dashboard manajer atau data master perusahaan milik tenant lain → sistem menampilkan halaman error 403 Forbidden / 404 Not Found. | **H** |
| **E2E-09** | **Ekspor Laporan Rekapitulasi Timesheet Bulanan** | Manajer memfilter rekapitulasi presensi tim per bulan & departemen → mengunduh dokumen Excel/PDF → berkas terunduh dengan angka jam kerja reguler dan lembur yang valid. | **M** |
| **E2E-10** | **Uji Tampilan Antarmuka Berbagai Perangkat** | Skenario presensi dan peninjauan jadwal dijalankan pada peramban seluler (viewport 375px) dan desktop (viewport 1440px) → navigasi tombol sentuh dan layout grid kalender beradaptasi rapi. | **M** |

---

### 7.5 Pengujian Non-Fungsional

1. **Security & Data Isolation**: Uji proteksi rute RBAC (INT-01, E2E-08), pemeriksaan sanitasi input formulir terhadap injeksi SQL (bawaan ORM Eloquent), proteksi Cross-Site Request Forgery (CSRF token), dan hashing sandi Bcrypt.  
2. **Response Time**: Pengukuran waktu eksekusi komputasi penyusunan roster otomatis dan kecepatan muat kalender jadwal dengan data dummy 50 staf.  
3. **Usability & Bahasa**: Verifikasi konsistensi istilah antarmuka berbahasa Indonesia pada tombol aksi, pesan error validasi form, dan label status (*Tepat Waktu*, *Terlambat*, *Menunggu Persetujuan*).  
4. **Kompatibilitas Tampilan**: Pengujian kesesuaian antarmuka pada Chrome, Firefox, dan Safari baik pada layar ponsel maupun monitor desktop.

---

### 7.6 Manajemen Defect dan Regression Testing

Setiap kegagalan kasus uji dicatat sebagai *defect* pada issue tracker internal (GitHub Issues / Jira) dengan menyertakan langkah reproduksi, hasil aktual, bukti tangkapan layar, dan tingkat keparahan (*severity*):

| Severity | Definisi Kriteria | Tindakan Penanganan |
| :---- | :---- | :---- |
| **Critical** | Sistem mengalami *crash* total, kegagalan login seluruh peran, data antar-perusahaan bocor, atau transaksi basis data korup. | Perbaikan darurat segera; seluruh pengujian terkait dihentikan sementara. |
| **High** | Fitur prioritas utama (H) gagal fungsi (misal: *Clock-In* tidak tersimpan, kalkulasi lembur salah, atau slot open shift ter-booking ganda). | Wajib diperbaiki dan lulus *re-test* sebelum rilis build berikutnya. |
| **Medium** | Fitur pendukung (M) bermasalah namun masih terdapat alur alternatif (*workaround*), atau kegagalan filter laporan timesheet. | Dijadwalkan perbaikan pada iterasi sprint berjalan. |
| **Low** | Ketidaksempurnaan kosmetik antarmuka, salah ketik kata (*typo*), atau inkonsistensi warna tombol. | Diperbaiki saat waktu luang tim tersedia. |

Setelah perbaikan di-deploy oleh developer, tester wajib melakukan **Re-test** pada kasus terkait dan mengeksekusi **Regression Test** pada modul yang bersinggungan untuk memastikan perbaikan tidak merusak fitur yang sudah stabil.

---

## 8\. Pass/Fail Criteria

Sebuah kasus uji dinyatakan **Pass** apabila hasil aktual persis sesuai dengan hasil yang diharapkan (*expected result*), dinyatakan **Fail** jika terdapat ketidaksesuaian hasil, dan **Blocked** jika pengujian terhalang oleh kegagalan fitur pendahulu.

| Level Pengujian | Kriteria Kelulusan (Pass Criteria) |
| :---- | :---- |
| **Unit Testing** | \- 100% kasus uji unit berhasil dieksekusi. \- Tingkat kelulusan minimal **95% Pass** dan seluruh kasus prioritas H berstatus Pass. \- Tidak ada defect berkategori *Critical* atau *High* yang masih terbuka. \- Kode tes lolos pada test runner PHPUnit bawaan. |
| **Integration Testing** | \- 100% skenario integrasi berhasil disimulasikan. \- Seluruh skenario prioritas H berstatus Pass dan minimal 95% keseluruhan berstatus Pass. \- Uji isolasi data multi-tenant dan proteksi rute RBAC lolos **100%**. |
| **E2E Testing** | \- Seluruh skenario prioritas H (E2E-01 s.d. E2E-08) lulus **100%**. \- Pengguna Karyawan dan Manajer dapat menyelesaikan seluruh siklus bisnis (jadwal → presensi → tukar shift → rekap) tanpa galat sistem. \- Zero Critical/High Defect yang belum terselesaikan. \- Cacat berkategori Medium maksimal 3 dengan rencana perbaikan terlampir. |

---

## 9\. Suspension Criteria and Resumption Requirements

### Kriteria Penghentian Sementara (Suspension Criteria):

Pengujian dihentikan sementara apabila:

1. Layanan otentikasi (login/logout) atau operasi dasar CRUD gagal berfungsi pada modul yang sedang diuji.  
2. Ditemukan kebocoran data antar-perusahaan (*multi-tenant leakage*) atau celah bypass otorisasi peran.  
3. Basis data mengalami galat migrasi atau server lokal mengalami crash berulang sehingga lebih dari 25% kasus uji berstatus *Blocked*.  
4. Alur utama presensi (*Clock-In/Clock-Out*) tidak dapat menyimpan waktu ke basis data.

### Kriteria Pelanjutan (Resumption Requirements):

Pengujian dapat dilanjutkan kembali apabila:

1. *Defect* penyebab telah diperbaiki oleh tim pengembang dan diverifikasi melalui *Smoke Test* (uji coba login dan alur dasar 1 transaksi presensi).  
2. Lingkungan basis data dipulihkan ke status bersih (*seeded state*) dan build baru berhasil dijalankan tanpa galat kompilasi aset.

---

## 10\. Test Deliverables

| Fase Pengujian | Artefak Deliverable yang Dihasilkan | Penanggung Jawab |
| :---- | :---- | :---: |
| **Sebelum Pengujian** | \- Dokumen Rencana Pengujian (*Test Plan \- Jadwalin\_TP\_1.0*) \- Matriks Skenario & Kasus Uji Detail (*Test Scenario & Test Cases*) \- Data Uji Seeder (JadwalinSeeder.php) & panduan lingkungan uji | QA / Test Lead |
| **Selama Pengujian** | \- Log Eksekusi Pengujian (*Test Execution Run Log*) \- Laporan Hasil Uji Otomatis PHPUnit (php artisan test) \- Laporan Cacat Sistem (*Bug & Defect Log*) berserta bukti screenshot | Seluruh Tester & Developer |
| **Setelah Pengujian** | \- Laporan Ringkasan Pengujian (*Test Summary Report*) \- Matriks Keterlacakan Kebutuhan (*Traceability Matrix*) \- Rekomendasi kesiapan rilis produk untuk sidang PBL | QA / Test Lead |

---

## 11\. Testing Tasks

Daftar aktivitas pengujian yang wajib diselesaikan:

1. Menyusun dan menyepakati dokumen *Software Test Plan*.  
2. Mempelajari dokumen SKPL/SDD dan menyusun rincian *Test Scenario* dan *Test Cases*.  
3. Menyiapkan lingkungan server pengujian lokal, koneksi database Supabase/PostgreSQL, dan seeder data percontohan multi-tenant.  
4. Menulis dan menjalankan *Unit Testing* pada model dan helper Laravel menggunakan PHPUnit.  
5. Melaksanakan *Integration Testing* (INT-01 s.d. INT-10) untuk memvalidasi interaksi database dan middleware RBAC.  
6. Melaksanakan *End-to-End Testing* (E2E-01 s.d. E2E-10) melalui antarmuka browser web.  
7. Mendokumentasikan *defect*, berkoordinasi dengan developer untuk perbaikan, dan melakukan *re-test* serta *regression test*.  
8. Menguji responsivitas tampilan mobile dan kompatibilitas peramban web.  
9. Menyusun *Test Summary Report* final dan mengajukan lembar persetujuan.

---

## 12\. Environmental Needs

| Komponen | Spesifikasi & Kebutuhan Lingkungan Uji |
| :---- | :---- |
| **Perangkat Keras (Hardware)** | \- Laptop penguji dengan prosesor minimal Intel Core i3 / AMD Ryzen 3, RAM minimal 8 GB, dan media penyimpanan SSD. \- Perangkat seluler (Android / iOS) untuk uji responsivitas browser mobile. |
| **Perangkat Lunak (Software)** | \- Sistem Operasi: Windows 10/11 64-bit. \- PHP 8.2+ dengan ekstensi pdo\_pgsql, mbstring, openssl. \- Composer 2.x dan Node.js 20.x. \- Framework Laravel 11.x dan Vite bundler. |
| **Basis Data (Database)** | PostgreSQL 15+ (Cloud Supabase Database / Local PostgreSQL). |
| **Peramban Web (Browser)** | Google Chrome (v120+), Mozilla Firefox (v120+), Safari iOS (versi terkini). |
| **Tools Pengujian** | \- Test Runner: PHPUnit 10.x / 11.x bawaan Laravel CLI. \- HTTP API Testing: Postman (opsional untuk endpoint JSON). \- Browser DevTools untuk simulasi viewport mobile dan audit jaringan. |
| **Data Uji (Test Data)** | \- 1 Perusahaan dummy (PT Senja Wisata Nusantara). \- 1 Akun Super Admin (owner@senjawisata.co.id). \- 1 Akun Manajer Global (manager@senjawisata.co.id). \- Akun Karyawan percontohan (karyawan@senjawisata.co.id). \- Data departemen F\&B, posisi Barista, dan master shift pagi/siang. |

---

## 13\. Responsibilities (Pembagian Tugas Pengujian)

| Nama Anggota Tim | Peran Proyek | Tanggung Jawab dalam Pengujian |
| :---- | :---- | :---- |
| **Muhammad Farras A.A** | Project Manager & Lead Backend Developer | \- Mengesahkan dokumen Test Plan dan jadwal sprint pengujian. \- Mengembangkan dan mengeksekusi *Unit & Feature Testing* otomatis pada backend Laravel. \- Memvalidasi kebenaran algoritma *Smart Scheduling* dan penguncian transaksi atomik (*Pessimistic Locking*). \- Melakukan pengujian silang (*cross-testing*) pada alur persetujuan manajer. |
| **Umi Maharani** | Full-Stack Developer & System Analyst | \- Meninjau keselarasan kasus uji terhadap dokumen analisis SKPL. \- Mengembangkan pengujian fungsional modul presensi mandiri dan kalkulasi jam lembur PP 35/2021. \- Menyiapkan skenario integrasi timesheet dan ekspor laporan. \- Memperbaiki cacat fungsi backend/frontend dan melakukan re-test. |
| **Neyza Ratu Anastasya** | UI/UX Designer & Technical Writer | \- Bertindak sebagai **Lead QA Tester** antarmuka. \- Menyusun dan memandu eksekusi skenario *End-to-End (E2E) Testing* berbasis persona pengguna nyata. \- Menguji responsivitas layout *Mobile-First* pada ragam resolusi layar. \- Mengelola pencatatan *Defect Report* dan menyusun dokumentasi laporan akhir pengujian. |
| **Muhammad Yusuf** | Database Designer & System Analyst | \- Memverifikasi integritas referensial skema basis data 14 entitas. \- Menguji isolasi data multi-tenant company\_id agar terbebas dari kebocoran data. \- Menyiapkan data seeder uji realistis (JadwalinSeeder.php). \- Melakukan pengujian silang pada modul CRUD Master Data organisasi. |

> **Prinsip Pengujian Silang (*Cross-Testing*)**: Setiap anggota tim menguji modul yang tidak dikembangkannya sendiri secara langsung guna menjamin objektivitas hasil pengujian.

---

## 14\. Staffing And Training Needs

### 14.1 Kebutuhan Staf

Pengujian dikerjakan secara kolaboratif oleh 4 anggota tim pengembang. Unit testing dipimpin oleh Lead Backend Developer, sedangkan pengujian fungsional dan E2E dikoordinasikan oleh Technical Writer/QA Tester dengan dukungan seluruh anggota.

### 14.2 Keterampilan yang Dibutuhkan

1. Pemahaman konsep pengujian perangkat lunak (*Black-box testing, Equivalence Partitioning, Boundary Value Analysis*).  
2. Pemahaman sintaks PHP, framework Laravel, dan runner PHPUnit.  
3. Pemahaman aturan hukum ketenagakerjaan PP No. 35 Tahun 2021 terkait waktu kerja dan perhitungan jam lembur.  
4. Pemahaman alur bisnis penjadwalan shift dan konsep isolasi basis data multi-tenant.

### 14.3 Kebutuhan Pelatihan Singkat

| Materi Pelatihan | Peserta | Estimasi Durasi |
| :---- | :---- | :---: |
| *Walkthrough* Alur Bisnis Jadwalin, Aturan PP 35/2021, dan Use Case Sistem | Seluruh Anggota Tim | 2 Jam |
| Praktik Eksekusi PHPUnit Test Suite dan Analisis Assertion Failure | Developer & QA | 1/2 Hari |
| Prosedur Pencatatan Defect, Penentuan Severity, dan Alur Re-test | Seluruh Anggota Tim | 1 Jam |

---

## 15\. Schedule (Jadwal Pelaksanaan Pengujian)

| Aktivitas Pengujian | Periode Pelaksanaan | Penanggung Jawab (PIC) |
| :---- | :---: | :---: |
| Penyusunan dan Review Dokumen Test Plan | Minggu ke-7 | QA & Project Manager |
| Perancangan Skenario Uji (*Test Scenario*) & Kasus Uji (*Test Cases*) | Minggu ke-7 s.d. 8 | Seluruh Anggota Tim |
| Persiapan Lingkungan Uji, Konfigurasi Database, & Seeder Data Uji | Minggu ke-9 s.d. 10 | Lead Backend & DB Designer |
| Eksekusi Unit Testing per Fitur/Model pada PHPUnit secara berkala | Minggu ke-8 s.d. 14 | Backend Developer |
| **Siklus 1: Pengujian Interim (Uji Integrasi INT-01 s.d. INT-04)** | Minggu ke-12 | Seluruh Anggota Tim |
| Perbaikan Cacat Sistem (*Bug Fixing*) & Verifikasi Re-test Tahap 1 | Minggu ke-13 | Developer |
| **Siklus 2: Pengujian Menyeluruh (Integrasi Lanjutan & E2E-01 s.d. E2E-07)** | Minggu ke-14 s.d. 15 | Seluruh Anggota Tim |
| Pengujian Non-Fungsional (Keamanan RBAC, Waktu Respon, Responsivitas Mobile) | Minggu ke-15 | UI/UX & QA Tester |
| **Siklus 3: Pengujian Regresi Final & Penyusunan Test Summary Report** | Minggu ke-16 | Seluruh Anggota Tim |

---

## 16\. Risks And Contingencies (Manajemen Risiko & Mitigasi)

| No | Identifikasi Risiko | Potensi Dampak | Rencana Kontinjensi / Mitigasi |
| :---: | :---- | :---- | :---- |
| **1** | Algoritma *Smart Scheduling* mengalami *timeout* atau beban berat saat memproses banyak data staf. | Eksekusi penjadwalan gagal pada pengujian performa. | Menerapkan pagination/chunking data serta pembatasan pemrosesan per departemen spesifik. |
| **2** | Perbedaan zona waktu (*timezone mismatch*) antara server database cloud UTC dan waktu lokal Indonesia (WIB/WITA). | Kesalahan penentuan status terlambat (*Late*) dan kalkulasi jam kerja. | Mengunci konfigurasi zona waktu eksplisit pada aplikasi (config/app.php) dan tabel companies ke zona waktu operasional lokal. |
| **3** | Kondisi balapan (*Race Condition*) saat pengujian perebutan slot Open Shift secara bersamaan. | Terjadi *double-claim* (dua staf menempati satu kuota yang sama). | Mengimplementasikan pengujian transaksional dengan klausa lockForUpdate() di dalam blok DB::transaction pada controller terkait. |
| **4** | Koneksi internet tidak stabil saat menjalankan pengujian ke database cloud Supabase. | Pengujian otomatis terhenti dan terjadi kegagalan koneksi (*connection timeout*). | Menyediakan konfigurasi basis data lokal PostgreSQL / SQLite cadangan pada berkas phpunit.xml untuk keperluan uji lokal tanpa internet. |
| **5** | Terjadinya regresi fungsional (perbaikan satu fitur memicu kerusakan pada fitur lain yang telah stabil). | Kualitas sistem menurun menjelang masa evaluasi akhir. | Menjalankan seluruh rangkaian tes otomatis (php artisan test) setiap kali terdapat *pull request* atau perubahan kode baru. |
| **6** | Perbedaan tampilan layout kalender shift antar-peramban ponsel. | Pengguna mobile kesulitan mengklik slot shift pada tanggal tertentu. | Melakukan uji langsung pada browser utama (Google Chrome Mobile dan Safari Mobile) serta mengoptimalkan *touch-target* minimal 44x44px. |
| **7** | Kesalahan kalkulasi matematis jam lembur yang tidak sesuai formula PP No. 35 Tahun 2021\. | Laporan timesheet tidak valid secara hukum ketenagakerjaan. | Menyusun tabel data uji batas nilai (*Boundary Value Analysis*) khusus untuk jam kerja: tepat 8 jam, 8 jam 15 menit, dan di atas 12 jam. |
| **8** | Potensi kebocoran data antar-perusahaan (*multi-tenant data breach*). | Risiko keamanan fatal (staf perusahaan A melihat jadwal perusahaan B). | Menambahkan pengujian otomatis unit dan feature yang secara khusus menguji kegagalan kueri lintas company\_id. |
| **9** | Waktu pengujian terhambat akibat keterlambatan penyelesaian modul tertentu. | Waktu pengujian E2E menjadi sangat sempit. | Menerapkan pengujian modular bertahap; modul yang sudah rampung langsung diuji tanpa menunggu seluruh modul selesai dibuat. |
| **10** | Keterbatasan data uji riil operasional toko/kafe. | Skenario pengujian kurang mencerminkan dinamika nyata. | Membangun seeder data percontohan (JadwalinSeeder.php) yang mensimulasikan pola kerja riil industri F\&B (shift pagi, siang, dan libur). |

---

## 17\. Approvals (Lembar Persetujuan)

Dokumen *Software Test Plan* ini dinyatakan sah dan berlaku sebagai pedoman baku pengujian perangkat lunak setelah ditinjau dan disetujui oleh pihak-pihak terkait di bawah ini:

| Peran Persetujuan | Nama Lengkap | Tanda Tangan | Tanggal Persetujuan |
| :---- | :---- | :---: | :---: |
| **Lead Quality Assurance (QA)** | **Neyza Ratu Anastasya** | \_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_ | 08 Oktober 2026 |
| **Project Manager** | **Muhammad Farras A.A** | \_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_ | 08 Oktober 2026 |
| **Dosen Pengampu Penjaminan Mutu Perangkat Lunak** | **Usman Nurhasan, S.Kom., M.T.** | \_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_ | \_\_\_\_ Oktober 2026 |
