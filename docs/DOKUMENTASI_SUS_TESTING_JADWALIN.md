# PANDUAN & DOKUMENTASI PENGUJIAN USABILITY SYSTEM USABILITY SCALE (SUS)
## SISTEM MANAJEMEN PENJADWALAN KERJA FLEKSIBEL "JADWALIN"
### Mata Kuliah: Penjaminan Mutu Perangkat Lunak (PMPL) & Proyek Berbasis Masalah (PBL)

> **Dokumen Referensi:** `docs/PMPL - DOC SUS TESTING.pdf`  
> **Tautan Kuesioner Daring:** [Google Form Kuesioner SUS Testing Jadwalin](https://forms.gle/ziZHc6AHYmzCk2Fs9)  
> **Metode Pengujian:** System Usability Scale (SUS) Standar John Brooke (1986) + Kuesioner Terbuka Kualitatif  
> **Target Sampel Responden:** 18 – 22 Responden Riil (Multi-Stakeholder)

---

## 1. Pendahuluan & Tujuan Pengujian SUS

Dalam mata kuliah **Penjaminan Mutu Perangkat Lunak (PMPL)**, pengujian perangkat lunak tidak hanya terbatas pada verifikasi teknis fungsional (*Functional Verification* seperti Unit, Feature, dan E2E Testing), tetapi juga mencakup validasi pengalaman pengguna (*Usability Validation*). 

**System Usability Scale (SUS)** adalah instrumen evaluasi usability yang telah terstandarisasi secara global untuk mengukur persepsi kemudahan penggunaan, efisiensi antarmuka, dan tingkat kepuasan pengguna terhadap suatu sistem perangkat lunak secara kuantitatif dan objektif.

### Tujuan Utama:
1. Mengukur tingkat kebergunaan (*usability*) sistem Jadwalin dari kacamata pengguna akhir riil.
2. Mengetahui apakah sistem dapat diterima (*acceptable*) di industri F&B dan bisnis berbasis shift.
3. Menemukan titik friksi, kebingungan, kendala, atau potensi cacat desain (*UX pain points*) melalui pertanyaan kualitatif terbuka (*open-ended questions*).
4. Memperoleh skor SUS tolok ukur (*benchmark*) untuk syarat kelulusan pengujian mutu perangkat lunak pada Milestone PBL.

---

## 2. Pemetaan & Profil Target Responden

Berdasarkan dokumen `docs/PMPL - DOC SUS TESTING.pdf`, responden dipilih menggunakan teknik *Purposive Sampling* yang mewakili 3 kelompok peran (*roles*) dalam ekosistem aplikasi Jadwalin:

### A. Kriteria Umum Seluruh Responden:
1. Berusia **minimal 17 tahun ke atas**.
2. Mampu mengoperasikan smartphone (Android/iOS) atau laptop serta terhubung ke jaringan internet.
3. **Independen & Objektif:** Belum pernah terlibat dalam pengembangan, perancangan, maupun pengujian internal sistem Jadwalin agar hasil evaluasi bebas dari bias (*unbiased*).

---

### B. Segmentasi & Kriteria Khusus Per Peran:

| Peran (Role) | Profil Responden Ideal | Cara Mencari & Sumber Responden | Target Jumlah | Alasan & Rasionalisasi |
| :--- | :--- | :--- | :---: | :--- |
| **Employee (Karyawan)** | Karyawan part-time, full-time, pekerja kafe/resto/toko/wisata, atau freelancer yang bekerja dengan sistem shift. Idealnya pernah mengalami kerumitan jadwal via Excel, WhatsApp grup, atau kertas manual. | Teman/kenalan tim yang bekerja part-time di coffee shop/resto, mahasiswa pekerja sampingan, komunitas freelance, dan staf outlet mitra. | **10 – 12 orang**<br>*(Rincian:<br>• 6 Part-time<br>• 4 Full-time<br>• 2 Freelance)* | Merupakan basis pengguna terbanyak (*majority users*) yang paling sering berinteraksi dengan sistem harian (presensi, jadwal, tukar shift). |
| **Manager (Manajer Tim)** | Supervisor, kepala shift (*head store*), manajer operasional, atau staf HR bisnis kecil yang pernah/sedang bertanggung jawab menyusun jadwal kerja dan merekap absensi tim. | Supervisor atau kepala shift outlet/toko kenalan, HR UMKM lokal, atau manajer operasional mitra bisnis F&B. | **5 – 6 orang** | Merupakan peran dengan fitur paling kompleks (Smart Scheduling Builder, persetujuan tukar shift/halangan, monitoring realtime, dan rekap timesheet). |
| **Superadmin / Owner** | Pemilik usaha (*owner*), founder, atau manajer senior yang memiliki kewenangan kebijakan perusahaan dan mengelola staf shift. | Pemilik kedai/outlet F&B, pengusaha kenalan, dosen pembimbing yang memiliki unit bisnis, atau calon mitra industri. | **3 – 4 orang** | Pengambil keputusan tingkat kebijakan regulasi jam kerja dan struktur organisasi perusahaan. |
| **TOTAL KESELURUHAN** | | | **18 – 22 orang** | **Memenuhi standar sampel SUS (N $\ge$ 15 untuk signifikansi statistik)** |

---

### C. Strategi Cadangan (Contingency Plan):
Jika responden kategori Owner atau Manajer HR eksternal sulit ditemui atau memiliki keterbatasan waktu:
1. **Simulasi Role-Playing:** Mengundang alumni atau mahasiswa tingkat akhir yang pernah menjabat sebagai ketua kepanitiaan/organisasi besar dengan tanggung jawab mengelola jadwal dinas staf.
2. **Uji Asinkron Terpandu:** Mengirimkan rekaman video demo panduan alur singkat (2-3 menit) beserta kredensial akun uji coba sehingga responden dapat mencoba secara mandiri di waktu luang mereka.
3. **Wawancara Daring Singkat (15 Menit):** Melakukan sesi Google Meet terpandu (*moderated testing*) di mana tim pengembang memandu langkah per langkah tanpa mengintervensi penilaian.

---

## 3. Skenario Tugas Pengujian (Task Scenarios per Role)

Sebelum responden mengisi lembar kuesioner Google Form, **responden WAJIB mencoba dan menyelesaikan skenario tugas nyata** di aplikasi Jadwalin agar memiliki pengalaman interaksi langsung dengan sistem.

```
┌─────────────────────────────────────────────────────────────┐
│ LANGKAH 1: Persiapan Akun & Skenario Tugas Pengujian         │
│ (Responden diberikan kredensial demo & daftar tugas)        │
└──────────────────────────────┬──────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────┐
│ LANGKAH 2: Eksekusi Tugas di Sistem Jadwalin (Live Testing) │
│ (Responden mencoba fitur-fitur sesuai peran yang ditugaskan) │
└──────────────────────────────┬──────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────┐
│ LANGKAH 3: Pengisian Kuesioner SUS di Google Form            │
│ • 10 Pertanyaan Standar SUS (Skala Likert 1 - 5)            │
│ • 6 Pertanyaan Terbuka Kualitatif (Feedback & Pain Points)  │
└─────────────────────────────────────────────────────────────┘
```

Berikut rincian tugas per peran yang harus dijalankan responden:

### 1. Skenario Tugas: Employee (Karyawan)
1. **Login & Dashboard:** Masuk ke sistem menggunakan kredensial akun Karyawan, lalu amati informasi jadwal dinas shift minggu ini pada tampilan dashboard.
2. **Pengajuan Halangan Kerja (Availability):** Buka menu *Ketersediaan Waktu*, ajukan jadwal tidak bisa bekerja (contoh: Hari Senin pukul 10:00–12:00 dengan alasan "Kuliah"), lalu perhatikan status pengajuan (`Pending`).
3. **Pengajuan Tukar Shift (Shift Swap):** Buka menu *Tukar Shift*, pilih salah satu jadwal dinas milik Anda, lalu ajukan permohonan tukar shift kepada salah satu rekan kerja.
4. **Klaim Bursa Shift Terbuka (Open Shift):** Buka menu *Open Shift*, telusuri daftar shift kosong yang tersedia, lalu lakukan klaim (*ambil shift*).
5. **Presensi Mandiri GPS Geofencing:** Buka menu *Absensi*, lakukan simulasi *Clock-In* masuk kerja dan *Clock-Out* selesai kerja (perhatikan deteksi radius lokasi kantor).
6. **Pemeriksaan Lembar Waktu (Timesheet):** Buka menu *Riwayat Jam Kerja / Timesheet*, periksa transparansi akumulasi jam kerja reguler dan jam lembur Anda.

### 2. Skenario Tugas: Manager (Manajer Operasional)
1. **Login & Dashboard Monitoring:** Masuk ke sistem sebagai Manajer, periksa ringkasan kehadiran tim dan jadwal berjalan hari ini.
2. **Evaluasi Pengajuan Halangan:** Buka panel *Persetujuan Ketersediaan*, lakukan aksi setujui (*Approve*) atau tolak (*Reject*) pada salah satu pengajuan halangan staf tim.
3. **Penyusunan Jadwal Cerdas (Smart Scheduling Builder):** Buka menu *Jadwal Kerja / Roster*, atur kebutuhan kuota staf, lalu tekan tombol pembuatan jadwal otomatis mingguan (*Generate Roster*).
4. **Modifikasi Jadwal Manual & Deteksi Bentrok:** Pindahkan satu staf secara manual ke shift lain pada kisi kalender. Coba tempatkan staf pada jam di mana ia memiliki halangan kerja yang telah disetujui, lalu amati respons peringatan sistem.
5. **Penerbitan Jadwal Resmi:** Publikasikan draf jadwal menjadi jadwal resmi (*Publish Roster*).
6. **Manajemen Tukar Shift & Open Shift:** Setujui satu permohonan tukar shift staf, lalu siarkan satu shift kosong baru (*Broadcast Open Shift*) dan setujui klaim staf yang masuk.
7. **Rekapitulasi & Ekspor Laporan:** Buka menu *Laporan Timesheet*, tentukan filter periode bulan/tahun, lalu lakukan ekspor ke format file Excel (.xlsx) dan PDF (.pdf).

### 3. Skenario Tugas: Superadmin / Owner
1. **Login & Dashboard Analytics:** Masuk sebagai Superadmin/Owner, pantau grafik ringkasan distribusi jam kerja, kehadiran tim, dan ringkasan operasional bisnis.
2. **Manajemen Akun Pengguna:** Buka menu *Manajemen Pengguna*, tambahkan satu akun karyawan baru, tentukan departemen, posisi jabatan, dan status tipe kontrak (Full-Time/Part-Time).
3. **Pengaturan Master Organisasi:** Buka menu *Master Data*, buat satu divisi/departemen baru dan satu posisi jabatan baru.
4. **Unduh Laporan Konsolidasian:** Buka modul laporan rekapitulasi, unduh laporan gabungan bulanan seluruh cabang.
5. **Inspeksi Jejak Audit (Audit Trail):** Buka menu *Log Aktivitas Sistem*, telusuri rekam jejak aktivitas mutasi data dan login terakhir yang tercatat di sistem.

---

## 4. Struktur Kuesioner SUS Daring (Google Form)

Tautan Formulir: **https://forms.gle/ziZHc6AHYmzCk2Fs9**

Kuesioner dirancang terstruktur menjadi 3 bagian utama:

### Bagian 1: Identitas & Profil Responden
* Nama Lengkap (opsional/inisial diperbolehkan demi privasi).
* Peran saat pengujian: [ ] Employee / Karyawan  [ ] Manager / Supervisor  [ ] Superadmin / Owner.
* Status pekerjaan saat ini (Part-time / Full-time / Freelance / Pemilik Bisnis / Mahasiswa Pekerja).
* Pengalaman mengelola/menerima jadwal shift sebelumnya (Excel / WhatsApp Grup / Kertas / Aplikasi Khusus).

---

### Bagian 2: 10 Instrumen Baku System Usability Scale (SUS)
Setiap responden memberikan penilaian menggunakan **Skala Likert 5 Poin**:
* `1` = Sangat Tidak Setuju (STS)
* `2` = Tidak Setuju (TS)
* `3` = Netral / Ragu-ragu (N)
* `4` = Setuju (S)
* `5` = Sangat Setuju (SS)

#### Butir-Butir Pernyataan Standar:
| No | Pernyataan Instrumen SUS | Polaritas Sifat | Makna yang Diukur |
| :-: | :--- | :---: | :--- |
| **Q1** | *Saya pikir saya akan sering menggunakan sistem ini.* | **Positif (+)** | Minat adopsi sistem dan relevansi kebutuhan kerja harian. |
| **Q2** | *Saya merasa sistem ini terlalu rumit padahal seharusnya bisa dibuat lebih sederhana.* | **Negatif (-)** | Kompleksitas antarmuka dan beban kognitif pengguna. |
| **Q3** | *Saya merasa sistem ini mudah digunakan.* | **Positif (+)** | Kemudahan penggunaan (*ease of use*) dan navigasi umum. |
| **Q4** | *Saya pikir saya membutuhkan bantuan orang teknis untuk dapat menggunakan sistem ini.* | **Negatif (-)** | Ketergantungan terhadap bantuan teknis (*learnability friction*). |
| **Q5** | *Saya merasa berbagai fitur dalam sistem ini terintegrasi dengan baik.* | **Positif (+)** | Keharmonisan integrasi antar modul (Jadwal, Presensi, Tukar Shift). |
| **Q6** | *Saya merasa ada terlalu banyak ketidakkonsistenan dalam sistem ini.* | **Negatif (-)** | Konsistensi komponen antarmuka, istilah, tombol, dan warna. |
| **Q7** | *Saya membayangkan kebanyakan orang akan cepat belajar menggunakan sistem ini.* | **Positif (+)** | Kecepatan adaptasi dan kemudahan dipelajari oleh pengguna baru. |
| **Q8** | *Saya merasa sistem ini sangat membingungkan untuk digunakan.* | **Negatif (-)** | Kejelasan alur sistem dan kejelasan pesan error/konfirmasi. |
| **Q9** | *Saya merasa sangat percaya diri saat menggunakan sistem ini.* | **Positif (+)** | Rasa percaya diri (*confidence*) dan kepastian kontrol pengguna saat beraksi. |
| **Q10** | *Saya perlu belajar banyak hal terlebih dahulu sebelum bisa menggunakan sistem ini.* | **Negatif (-)** | Tingkat prasyarat pengetahuan sebelum mahir mengoperasikan sistem. |

---

### Bagian 3: Pertanyaan Terbuka Kualitatif (Open-Ended Feedback)
Bagian ini menggali masukan mendalam yang tidak tertangkap oleh angka skala Likert:
1. **Fitur Terfavorit:** *Fitur atau bagian apa dari sistem ini yang menurut Anda paling membantu atau paling Anda sukai? Mengapa?*
2. **Fitur Paling Sulit:** *Bagian mana dari sistem ini yang menurut Anda paling sulit atau membingungkan? Jelaskan kendala yang Anda alami.*
3. **Temuan Cacat/Error:** *Apakah ada kendala atau error yang Anda temui selama mencoba sistem? Jika ada, ceritakan pada langkah/halaman mana.*
4. **Saran UI/UX:** *Apa saran Anda untuk memperbaiki tampilan, alur, atau fitur pada sistem ini?*
5. **Kebutuhan Fitur Tambahan:** *Fitur apa yang menurut Anda masih kurang atau perlu ditambahkan?*
6. **Net Promoter Score (NPS):** *Apakah Anda akan merekomendasikan sistem ini kepada orang lain? Mengapa?*

---

## 5. Metodologi Kalkulasi & Interpretasi Skor SUS

Kalkulasi skor SUS mengikuti formula matematis standar dari **John Brooke (1986)**:

### Rumus Perhitungan Skor Per Responden:
Misalkan $R_i$ adalah nilai jawaban responden untuk pertanyaan ke-$i$ (rentang 1 sampai 5):

1. **Untuk Pertanyaan Bernomor Ganjil (Item Positif: Q1, Q3, Q5, Q7, Q9):**
   $$\text{Skor Kontribusi } S_i = R_i - 1$$
   *(Nilai kontribusi bergerak dari 0 hingga 4)*

2. **Untuk Pertanyaan Bernomor Genap (Item Negatif: Q2, Q4, Q6, Q8, Q10):**
   $$\text{Skor Kontribusi } S_i = 5 - R_i$$
   *(Nilai kontribusi bergerak dari 0 hingga 4)*

3. **Total Skor Responden ($SUS_k$):**
   $$SUS_k = \left( \sum_{i=1}^{10} S_i \right) \times 2.5$$
   *(Faktor pengali $2.5$ menstandardisasi total skor mentah $0 - 40$ menjadi skala $0 - 100$)*

4. **Skor Rata-Rata Sistem ($\overline{SUS}$):**
   $$\overline{SUS} = \frac{1}{N} \sum_{k=1}^{N} SUS_k$$
   *(Di mana $N$ adalah jumlah total responden, target $N = 18 - 22$)*

---

### Matriks Tolok Ukur & Interpretasi Skor SUS (Bangor et al., 2008 & Sauro, 2011):

```
Skor SUS :   0 -------- 50 --------- 68 --------- 80.3 -------- 100
Grade    :   [    F    ] [    D    ] [   C   ] [   B   ] [   A   ]
Adjective:   [  Poor   ] [   OK    ] [ Good  ] [Excellent] [Best Imaginable]
Accept   :   [  Not Acceptable ] [Marginal] [     Acceptable     ]
NPS      :   [-------- Detractor ---------] [ Passive ] [ Promoter ]
```

| Rentang Skor SUS | Grade Kategori | Adjective Rating | Tingkat Penerimaan (*Acceptability*) | Kategori Net Promoter Score (NPS) |
| :---: | :---: | :---: | :---: | :---: |
| **> 80.3** | **Grade A** | **Excellent / Best Imaginable** | **Acceptable (Sangat Layak)** | **Promoter (Rekomendasi Tinggi)** |
| **68.0 – 80.3** | **Grade B / C** | **Good** *(Batas Rata-Rata Industri = 68)* | **Acceptable (Dapat Diterima)** | **Passive (Cukup Puas)** |
| **51.0 – 67.9** | **Grade D** | **OK / Fair** | **Marginal (Perlu Perbaikan)** | **Detractor (Kurang Puas)** |
| **< 51.0** | **Grade F** | **Poor / Awful** | **Not Acceptable (Gagal Usability)** | **Detractor (Tidak Direkomendasikan)** |

> [!IMPORTANT]
> **Target Ambang Batas Minimal Proyek Jadwalin:**  
> Skor rata-rata SUS Jadwalin ditargetkan minimal **$\ge 70.0$ (Grade B - Acceptable/Good)** untuk membuktikan bahwa sistem siap digunakan oleh pelaku industri F&B dan retail tanpa hambatan usability yang berarti.

---

## 6. Prosedur Operasional Pelaksanaan Pengujian (SOP Pengujian)

Agar pengumpulan data berjalan tertib dan valid, tim pengembang mengikuti protokol berikut:

1. **Tahap Pra-Pengujian:**
   * Memastikan aplikasi Jadwalin aktif dan database memiliki seeder data uji yang representatif.
   * Menyiapkan akun demo per peran:
     * Akun Employee: `employee@senja.com`
     * Akun Manager: `manager@senja.com`
     * Akun Admin/Owner: `admin@senja.com`
2. **Tahap Pengujian (Task Execution):**
   * Berikan lembar skenario tugas kepada responden sesuai peran yang ditargetkan.
   * Biarkan responden mengeksplorasi antarmuka secara natural (*think aloud protocol*).
   * Catat durasi pengerjaan tugas dan bagian di mana responden ragu-ragu atau bertanya.
3. **Tahap Pasca-Pengujian (Form Filling):**
   * Berikan tautan Google Form: `https://forms.gle/ziZHc6AHYmzCk2Fs9`.
   * Ingatkan responden untuk menjawab 10 butir pertanyaan secara spontan tanpa berdiskusi.
   * Pastikan responden mengisi bagian pertanyaan terbuka kualitatif untuk masukan perbaikan.
4. **Tahap Agregasi Data:**
   * Ekspor respon spreadsheet dari Google Form ke berkas Excel.
   * Terapkan rumus perhitungan kontribusi $(R_i - 1)$ dan $(5 - R_i)$.
   * Buat visualisasi grafik distribusi skor per peran (Employee vs Manager vs Owner).
   * Cantumkan hasil analisis dan kutipan feedback responden ke dalam Laporan Akhir PBL / Dokumen PMPL.

---
*Dokumen panduan ini terpisah dan didedikasikan secara khusus untuk keperluan Penjaminan Mutu Perangkat Lunak (PMPL) - Pengujian Kebergunaan Sistem Usability Scale (SUS) Jadwalin.*

