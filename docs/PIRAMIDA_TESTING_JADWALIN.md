# DOKUMENTASI LENGKAP PIRAMIDA TESTING BERDASARKAN PENUGASAN TASK JIRA
## SISTEM MANAJEMEN PENJADWALAN KERJA FLEKSIBEL "JADWALIN"

> **Prinsip Utama:** Seluruh atribusi penugasan author murni dan eksklusif merujuk pada **Assignee Task resmi di Jira Cloud** (`muhammadfarrasawaludinalwi.atlassian.net`).  
> **Status Seluruh Pengujian:** 100% Lulus (Green)  
> **Total Cakupan Pengujian Otomatis:** 354 Kasus Uji (54 Unit Test, 226 Feature Test, 74 Playwright E2E Test)

---

## 1. Filosofi & Distribusi Piramida Testing Jadwalin

Sistem Jadwalin mengadopsi model **Testing Trophy / Testing Diamond**, di mana integrasi backend transaksional (Feature Testing) menjadi fondasi terkuat yang menjembatani logika algoritma murni (Unit Testing) dengan validasi antarmuka peramban nyata (End-to-End Testing):

```
                      ▲
                     / \
                    /   \     E2E / Browser Testing (Playwright)
                   / E2E \    74 Skenario Uji (21 Berkas .spec.js)
                  /-------\   Pengujian Antarmuka, Interaksi Pengguna & DOM
                 /         \
                /  FEATURE  \  Feature / Integration Testing (PHPUnit)
               /  INTEGRATION\ 226 Kasus Uji (44 Berkas Test)
              /---------------\ HTTP Requests, Middleware, RBAC, DB Transactions
             /      UNIT       \
            /-------------------\ Unit Testing (PHPUnit)
                                  54 Kasus Uji (13 Berkas Test)
                                  Algoritma Murni, Formula Matematika, Aturan Regulasi
```

### Ringkasan Penugasan Author Berdasarkan Task Jira:
* **Muhammad Farras Awaludin Alwi** (8 Task Jira): Core Engine, Security RBAC, Penjadwalan Otomatis Bebas Konflik, Concurrency Locking, Pertukaran Shift, Open Shift Marketplace, dan Arsitektur Test Suite.
* **nesya ratu anastasya** (6 Task Jira): Form Ketersediaan/Halangan Kerja Karyawan, Panel Persetujuan Ketersediaan Manajer, Kalender Roster Karyawan, Pusat Notifikasi In-App, Landing Page Publik & Profil Pengguna.
* **Umi Maharani** (7 Task Jira): Presensi Mandiri GPS Geofencing, Monitoring Kehadiran Realtime Tim, Otomasi Auto Clock-Out & Koreksi Presensi, Rekapitulasi Timesheet & Ekspor Laporan (Excel/PDF), dan Audit Trail Log.
* **muhammadyusuffff0812** (7 Task Jira): Master Departemen & Posisi Jabatan, Master Template Shift, Pengaturan Regulasi Ketenagakerjaan (PP 35/2021), Manajemen Tim Staf, Manajemen Akun Pengguna Multi-Role, Profil Tenant Perusahaan, dan Dashboard Operasional.

---

## 2. Rincian Lengkap Pengujian Per Task Jira (File & Seluruh Isinya)

Berikut adalah bedah lengkap setiap task Jira: siapa author-nya, file pengujian apa saja yang dibuat, seluruh nama fungsi uji (*test methods*), serta penjelasan mendalam mengenai skenario, input data, logika, dan asersi yang diuji.

---

# BAGIAN A: AUTHOR — Muhammad Farras Awaludin Alwi

---

### TIKET: PBL-8
**Judul Task:** Sprint 1 - [Core Engine] Implementasi Model Eloquent & Relasi Database (14 Entitas)  
**Author (Assignee di Jira):** Muhammad Farras Awaludin Alwi  
**Tujuan Task:** Membangun fondasi 14 model Eloquent utama beserta seluruh relasi kunci asing (*Foreign Keys*) dan fungsi pembantu bisnis.

#### Lapisan Unit Test:
* Berkas: `tests/Unit/ModelsTest.php` (18 Test Methods)
  1. `test_core_model_eloquent_relationships_configuration`: Memverifikasi instansiasi dan konfigurasi dasar relasi Eloquent pada model inti.
  2. `test_user_employment_type_and_role_helpers`: Memverifikasi fungsi helper status kerja pada model User (`isFullTime()`, `isPartTime()`, `isFreelance()`, `isSuperAdmin()`, `isManager()`, `isEmployee()`).
  3. `test_shift_swap_dual_mode_helpers`: Memvalidasi logika model `ShiftSwap` dalam mengenali mode One-Way Handover vs Two-Way Mutual Barter.
  4. `test_shift_supports_gap_flags`: Menguji penanda slot shift yang belum terpenuhi kuotanya (*understaffed / gap flag*).
  5. `test_company_relationships`: Memvalidasi relasi `Company` hasMany ke Departments, Users, ShiftTemplates, dan CompanySettings.
  6. `test_company_setting_relationships`: Memvalidasi relasi `CompanySetting` belongsTo ke Company.
  7. `test_department_relationships`: Memvalidasi relasi `Department` belongsTo ke Company, hasMany ke Positions, Users, dan Shifts.
  8. `test_position_relationships`: Memvalidasi relasi `Position` belongsTo ke Department dan hasMany ke Users.
  9. `test_user_relationships`: Memvalidasi relasi `User` belongsTo ke Company, Department, Position, serta hasMany ke ShiftAssignments, Availabilities, dan Notifications.
  10. `test_availability_relationships`: Memvalidasi relasi `Availability` belongsTo ke User dan Company.
  11. `test_shift_template_relationships`: Memvalidasi relasi `ShiftTemplate` belongsTo ke Company dan Department.
  12. `test_shift_relationships`: Memvalidasi relasi `Shift` belongsTo ke Department, ShiftTemplate, dan hasMany ke ShiftAssignments.
  13. `test_shift_assignment_relationships`: Memvalidasi relasi `ShiftAssignment` belongsTo ke Shift dan User, serta hasOne ke Attendance.
  14. `test_shift_swap_relationships`: Memvalidasi relasi `ShiftSwap` belongsTo ke ShiftAssignment asal, User pengaju, User target, dan Assignment pengganti.
  15. `test_open_shift_claim_relationships`: Memvalidasi relasi `OpenShiftClaim` belongsTo ke Shift dan User pengklaim.
  16. `test_timesheet_report_relationships`: Memvalidasi relasi `TimesheetReport` belongsTo ke User, Department, dan Company.
  17. `test_notification_relationships`: Memvalidasi relasi `Notification` belongsTo ke User dan Company.
  18. `test_audit_log_relationships`: Memvalidasi relasi polimorfik / belongsTo `AuditLog` ke User aktor dan Company.

---

### TIKET: PBL-12
**Judul Task:** [Setup] Multi-Role Authentication & RBAC Middleware  
**Author (Assignee di Jira):** Muhammad Farras Awaludin Alwi  
**Tujuan Task:** Membangun infrastruktur autentikasi multi-peran (Superadmin, Manager, Employee), perutean aman, proteksi otorisasi Gate/Policy, dan isolasi tenant.

#### Lapisan Feature Test:
* Berkas: `tests/Feature/Auth/AuthenticationTest.php` (4 Test Methods)
  1. `test_login_screen_can_be_rendered`: Memastikan form login dapat dimuat via HTTP GET 200.
  2. `test_users_can_authenticate_using_the_login_screen`: Memverifikasi submit kredensial email & password valid berhasil autentikasi dan meregenerasi sesi.
  3. `test_users_can_not_authenticate_with_invalid_password`: Menolak login kata sandi salah dan memberikan error validasi sesi.
  4. `test_users_can_logout`: Memverifikasi pengguna dapat logout, sesi dihapus, dan token CSRF di-refresh.
* Berkas: `tests/Feature/Auth/PasswordConfirmationTest.php` (3 Test Methods)
  1. `test_confirm_password_screen_can_be_rendered`: Memuat layar konfirmasi password untuk aksi sensitif.
  2. `test_password_can_be_confirmed`: Mengonfirmasi password pengguna yang sedang login.
  3. `test_password_is_not_confirmed_with_invalid_password`: Menolak konfirmasi jika password salah.
* Berkas: `tests/Feature/Auth/PasswordResetTest.php` (4 Test Methods)
  1. `test_reset_password_link_screen_can_be_rendered`: Memuat form permohonan link reset password.
  2. `test_reset_password_link_can_be_requested`: Mengirim token reset password ke email.
  3. `test_reset_password_screen_can_be_rendered`: Membuka form ganti password dengan token valid.
  4. `test_password_can_be_reset_with_valid_token`: Memperbarui password di database dengan enkripsi Bcrypt/Argon2.
* Berkas: `tests/Feature/Auth/PasswordUpdateTest.php` (2 Test Methods)
  1. `test_password_can_be_updated`: Mengubah password akun dari halaman setelan profil.
  2. `test_correct_password_must_be_provided_to_update_password`: Menolak ubah password jika current password salah.
* Berkas: `tests/Feature/Routing/RoleAccessControlTest.php` (5 Test Methods)
  1. `test_guest_is_redirected_to_login_for_dashboard`: Tamu tanpa sesi dicegat dan diredireksi ke `/login`.
  2. `test_employee_is_forbidden_from_admin_dashboard_and_settings`: Role employee dicegat (HTTP 403) saat mengakses area admin.
  3. `test_employee_is_forbidden_from_admin_placeholder_routes`: Mencegah akses employee ke rute admin tingkat dalam.
  4. `test_manager_is_forbidden_from_admin_routes`: Role manager diblokir dari pengaturan tingkat perusahaan.
  5. `test_superadmin_can_access_admin_dashboard_and_placeholders`: Role superadmin memiliki hak penuh ke dashboard admin.
* Berkas: `tests/Feature/NavigationAndRoleMenuTest.php` (3 Test Methods)
  1. `test_superadmin_can_access_admin_routes_and_not_manager_routes`: Memastikan menu superadmin bersih dari menu manajer.
  2. `test_manager_can_access_manager_routes_and_not_admin_routes`: Memastikan menu manajer terisolasi pada lingkup tim.
  3. `test_employee_can_access_employee_routes_and_not_admin_routes`: Memastikan menu karyawan hanya modul mandiri.
* Berkas: `tests/Feature/Security/MultiTenantIsolationTest.php` (5 Test Methods)
  1. `test_superadmin_cannot_access_or_edit_departments_from_another_company`: Mencegah manipulasi departemen lintas tenant.
  2. `test_superadmin_cannot_delete_shift_template_from_another_company`: Mencegah penghapusan template tenant lain.
  3. `test_manager_cannot_view_employee_details_from_another_company`: Mencegah manajer melihat staf tenant lain.
  4. `test_manager_cannot_approve_shift_swap_from_another_company`: Mencegah approval pertukaran shift lintas perusahaan.
  5. `test_employee_cannot_view_schedule_details_from_another_company`: Menjamin isolasi kalender kerja per tenant.

#### Lapisan E2E Playwright Test:
* Berkas: `e2e/login.spec.js` (9 Skenario: Form render, toggle password, login 3 peran, validasi error, logout).
* Berkas: `e2e/auth_and_profile.spec.js` (9 Skenario: Form login, negative test, confirm/reset password, edit profil).
* Berkas: `e2e/navigation_role_menu.spec.js` (3 Skenario: Verifikasi navigasi sidebar untuk Superadmin, Manager, Employee).

---

### TIKET: PBL-16
**Judul Task:** [Setup] Automated Test Suite Setup (23 Tests Passing)  
**Author (Assignee di Jira):** Muhammad Farras Awaludin Alwi  
**Tujuan Task:** Membangun infrastruktur pengujian otomatis PHPUnit, TestCase dasar, helper data factory, dan koneksi in-memory SQLite.

#### Lapisan Unit & Feature Test:
* Berkas: `tests/TestCase.php`: Base class yang menginisialisasi database SQLite transaksi cepat dan helper autentikasi.
* Berkas: `tests/Unit/ExampleTest.php`: Memastikan assertion unit runner berfungsi dengan baik (`test_that_true_is_true`).
* Berkas: `tests/Feature/ExampleTest.php`: Memastikan HTTP response kernel berfungsi dengan baik (`test_the_application_returns_a_successful_response`).

---

### TIKET: PBL-17
**Judul Task:** Sprint 2 - [Core] Algoritma Smart Scheduling Builder (Manager)  
**Author (Assignee di Jira):** Muhammad Farras Awaludin Alwi  
**Tujuan Task:** Mengembangkan mesin penyusun jadwal otomatis bebas konflik (*Smart Scheduling Engine*), penegakan jatah libur bergilir (Rolling Day Off) bagi Full-Time, pembatasan akumulasi jam platform (maksimal 48 jam/minggu), pencegahan shift hanya diisi Part-Time/Freelance, dan penanganan shift lintas malam (*overnight*).

#### Lapisan Unit Test:
* Berkas: `tests/Unit/Services/ScheduleBuilderServiceTest.php` (5 Test Methods)
  1. `test_generate_weekly_roster_returns_empty_when_no_templates_exist`: Validasi penanganan aman saat template shift kosong.
  2. `test_generate_weekly_roster_creates_shifts_and_assignments`: Menghasilkan instansiasi Shift dan ShiftAssignment mingguan secara otomatis.
  3. `test_builder_respects_employee_blackout_unavailability`: Memastikan karyawan yang memiliki izin/halangan disetujui **100% tidak dijadwalkan** pada slot tersebut.
  4. `test_builder_respects_recurring_weekly_availability_and_prevents_conflict`: Menghormati pola halangan berulang mingguan karyawan.
  5. `test_generate_weekly_roster_rotates_full_time_off_days_and_fills_all_shifts`: **Menguji algoritma mutakhir:** memastikan setiap karyawan Full-Time mendapat 1 hari libur wajib (*Rolling Day Off*), penyeimbangan distribusi libur antar staf, kepatuhan batas 48 jam per minggu, dan penugasan minimal 1 karyawan Full-Time di setiap shift (tidak membiarkan Part-Time/Freelance bertugas sendirian).
* Berkas: `tests/Unit/Services/OvernightShiftResolutionTest.php` (3 Test Methods)
  1. `test_resolves_same_day_shift_end_datetime`: Resolusi waktu shift normal yang berakhir pada hari yang sama.
  2. `test_resolves_overnight_shift_where_end_time_is_next_day`: Menghitung tanggal akhir shift lintas malam (contoh: 22:00 - 06:00 keesokan harinya).
  3. `test_resolves_overnight_shift_across_month_boundary`: Menghitung pergantian tanggal shift lintas malam pada batas akhir bulan kalender.

#### Lapisan Feature Test:
* Berkas: `tests/Feature/Manager/SmartSchedulingBuilderTest.php` (15 Test Methods)
  1. `test_manager_can_view_weekly_schedule_grid`: Membuka matriks tampilan kalender grid jadwal mingguan.
  2. `test_auto_scheduling_excludes_employees_with_approved_unavailability`: Integrasi builder mengecualikan staf yang cuti/izin.
  3. `test_auto_scheduling_enforces_regular_hour_cap_from_settings`: Kepatuhan terhadap batas jam kerja reguler mingguan perusahaan.
  4. `test_auto_scheduling_respects_freelance_and_part_time_max_weekly_hours`: Membatasi jam kerja staf Part-Time & Freelance sesuai kuota kontrak.
  5. `test_auto_scheduling_enforces_weekly_rostered_day_off`: Memastikan hak 1 hari libur per pekan terpenuhi pada jadwal resmi.
  6. `test_manager_can_publish_draft_shifts`: Menerbitkan (*publish*) draf jadwal menjadi jadwal resmi yang dapat dilihat karyawan.
  7. `test_manager_can_publish_and_overwrite_existing_published_shifts`: Fitur menimpa (*overwrite*) jadwal yang sudah dipublikasikan sebelumnya.
  8. `test_manager_can_publish_and_keep_both_published_shifts_when_not_overwritten`: Opsi mempertahankan jadwal lama jika opsi penimpaan dinonaktifkan.
  9. `test_global_manager_and_regular_manager_view_differences`: Membedakan cakupan visibilitas antara Manajer Global dan Manajer Departemen.
  10. `test_auto_scheduling_with_custom_quota_requirements_and_dynamic_dates`: Mendukung penyesuaian kuota staf dinamis pada tanggal tertentu.
  11. `test_auto_scheduling_with_dynamic_number_of_shifts_and_custom_shifts`: Fleksibilitas jumlah shift harian.
  12. `test_manager_can_update_existing_shift_and_reassign_staff`: Mengedit detail shift dan menukar staf secara manual di panel grid.
  13. `test_manager_can_delete_existing_shift`: Menghapus entitas shift dari grid kalender.
  14. `test_regular_manager_cannot_edit_shift_of_another_department`: Menolak akses (403) jika manajer mengedit jadwal departemen lain.
  15. `test_manager_can_update_master_shift_template`: Mengubah preferensi template shift langsung dari panel builder.

#### Lapisan E2E Playwright Test:
* Berkas: `e2e/manager_smart_scheduling.spec.js` (5 Skenario)
  * `TC-SCH-01`: Menampilkan grid roster jadwal mingguan tim secara interaktif.
  * `TC-SCH-02`: Filter pemilihan departemen sesuai peran Manajer Global vs Departemen.
  * `TC-SCH-03`: Aksi penerbitan jadwal (Publish Roster) mengubah badge status menjadi resmi.
  * `TC-SCH-04`: Interaksi modal edit shift, modifikasi jam, dan hapus shift dari roster.
  * `TC-SCH-05`: Pencegahan akses (403) saat manajer memanipulasi jadwal di luar kewenangannya.

---

### TIKET: PBL-22
**Judul Task:** Sprint 3 - [Core] Dual-Mode Shift Swap & Transfer (Handover & Mutual Barter) via Atomic Transaction  
**Author (Assignee di Jira):** Muhammad Farras Awaludin Alwi  
**Tujuan Task:** Mengembangkan modul pertukaran shift dua mode: *One-Way Transfer* (hibah satu arah) dan *Two-Way Mutual Exchange* (barter silang) dengan mutasi transaksi database atomik (*DB Transaction*) dan pencegahan race condition.

#### Lapisan Feature Test:
* Berkas: `tests/Feature/Employee/ShiftSwapTest.php` (11 Test Methods)
  1. `test_employee_can_request_one_way_shift_transfer`: Pengajuan hibah shift satu arah ke rekan kerja tanpa barter.
  2. `test_employee_can_request_two_way_mutual_exchange`: Pengajuan barter tukar shift timbal balik dengan rekan kerja.
  3. `test_target_employee_can_accept_and_transition_to_pending_manager`: Penerimaan oleh rekan kerja memindahkan status ke `pending_manager`.
  4. `test_manager_approval_atomically_swaps_or_reassigns_shift_ownership`: Persetujuan manajer memutasi pemilik shift secara atomik di database.
  5. `test_prevent_concurrent_duplicate_swap_requests_on_same_assignment`: Mencegah dobel pengajuan tukar pada assignment shift yang sama.
  6. `test_target_employee_can_reject_swap_request`: Rekan kerja dapat menolak ajakan pertukaran shift.
  7. `test_manager_can_reject_swap_request_with_notes`: Manajer dapat menolak permohonan disertai alasan catatan evaluasi.
  8. `test_employee_cannot_swap_shift_they_do_not_own`: Menolak pengajuan tukar jika assignment shift bukan milik pemohon.
  9. `test_employee_cannot_swap_with_self`: Menolak pengajuan tukar dengan diri sendiri.
  10. `test_employee_and_manager_can_access_swap_views`: Menampilkan halaman bursa pertukaran sesuai peran.
  11. `test_employee_cannot_swap_shift_that_has_already_ended`: Menolak pertukaran untuk shift yang tanggalnya sudah lampau.

#### Lapisan E2E Playwright Test:
* Berkas: `e2e/shift_swap.spec.js` (3 Skenario: Buka modal pengajuan tukar, antrean persetujuan manajer, proteksi akses 403).
* Berkas: `e2e/shift_swap_lifecycle.spec.js` (2 Skenario: Alur penuh multi-aktor Karyawan A ajukan $	o$ Karyawan B setujui $	o$ Manajer approve $	o$ Verifikasi kalender).

---

### TIKET: PBL-23
**Judul Task:** Sprint 3 - [Core] Open Shift Broadcast & Pessimistic Locking  
**Author (Assignee di Jira):** Muhammad Farras Awaludin Alwi  
**Tujuan Task:** Membangun bursa shift terbuka (*Open Shift Marketplace*), penyiaran slot shift kosong oleh manajer, pengambilan mandiri oleh karyawan, serta perlindungan konkurensi klaim serentak via *pessimistic locking* database (`lockForUpdate`).

#### Lapisan Feature Test:
* Berkas: `tests/Feature/Concurrency/OpenShiftClaimConcurrencyTest.php` (14 Test Methods)
  1. `test_employee_can_claim_available_open_shift_successfully`: Staf berhasil mengambil shift terbuka yang tersedia.
  2. `test_open_shift_instant_fcfs_creates_assignment_immediately`: Mode instan FCFS langsung membuat assignment saat diklaim.
  3. `test_open_shift_requires_approval_creates_pending_claim`: Mode butuh persetujuan membuat record berstatus pending.
  4. `test_prevent_duplicate_claims_by_same_employee`: Mencegah klaim ganda dari karyawan yang sama pada slot yang sama.
  5. `test_simultaneous_claims_prevent_race_condition_under_pessimistic_lock`: **Uji Kritis Concurrency:** menyimulasikan klaim serentak multi-staf dengan kunci pesimistik database, memastikan kuota slot tidak pernah terambil melebihi batas (*zero overbooking*).
  6. `test_manager_can_broadcast_open_shift`: Manajer dapat menyiarkan shift terbuka baru.
  7. `test_manager_can_approve_pending_claim`: Manajer menyetujui klaim terbuka dari staf.
  8. `test_manager_can_reject_pending_claim`: Manajer menolak klaim terbuka dari staf.
  9. `test_manager_can_close_open_shift_broadcast`: Menutup penyiaran open shift secara manual sebelum kuota habis.
  10. `test_manager_can_broadcast_existing_understaffed_shift`: Menyiarkan shift berjalan yang kekurangan personel.
  11. `test_broadcasting_existing_shift_with_custom_open_slots_adjusts_quota_correctly`: Menyesuaikan kuota slot tambahan secara dinamis.
  12. `test_global_manager_can_broadcast_global_shift_and_cross_department_employee_can_claim`: Penyiaran terbuka lintas departemen oleh Manajer Global.
  13. `test_global_manager_can_broadcast_existing_shift_as_global`: Mengubah shift reguler menjadi shift global lintas cabang.
  14. `test_department_manager_cannot_broadcast_for_other_departments`: Manajer reguler dibatasi hanya pada departemen miliknya.
* Berkas: `tests/Feature/Employee/OpenShiftClaimTest.php` (4 Test Methods)
  1. `test_employee_can_view_available_open_shifts`: Membuka katalog penawaran open shift aktif.
  2. `test_employee_can_claim_available_open_shift`: Mengirim permohonan klaim shift terbuka.
  3. `test_employee_cannot_claim_open_shift_if_schedule_conflicts`: Mencegah klaim jika jadwal bertabrakan dengan shift yang sudah dimiliki.
  4. `test_employee_cannot_claim_same_open_shift_twice`: Mencegah pengambilan ulang pada shift yang sama.
* Berkas: `tests/Feature/Manager/OpenShiftManagementTest.php` (6 Test Methods)
  1. `test_manager_can_view_open_shifts_index`: Memuat panel manajemen penyiaran open shift.
  2. `test_manager_can_broadcast_shift_to_open_shift`: Aksi broadcast shift kosong.
  3. `test_manager_can_close_open_shift_broadcast`: Aksi penutupan broadcast shift.
  4. `test_manager_can_approve_open_shift_claim`: Aksi approval klaim staf.
  5. `test_manager_can_reject_open_shift_claim_with_reason`: Aksi penolakan klaim staf disertai alasan.
  6. `test_manager_cannot_reject_claim_with_reason_exceeding_max_length`: Validasi panjang maksimal teks alasan penolakan.

#### Lapisan E2E Playwright Test:
* Berkas: `e2e/open_shift.spec.js` (3 Skenario: Katalog bursa karyawan, kelola bursa manajer, proteksi akses 403).
* Berkas: `e2e/open_shift_lifecycle.spec.js` (2 Skenario: Siklus hidup penuh: Broadcast oleh manajer $	o$ Klaim cepat oleh staf $	o$ Penugasan resmi terverifikasi).

---

### TIKET: PBL-30
**Judul Task:** Sprint 5 - [SQA] Uji Beban & Penyusunan Dokumen Pengujian Perangkat Lunak (Test Plan & Report)  
**Author (Assignee di Jira):** Muhammad Farras Awaludin Alwi  
**Tujuan Task:** Menguji performa kueri database (eliminasi N+1 problem dengan Eager Loading) dan menyusun suite pengujian komprehensif.

#### Lapisan Feature Test:
* Berkas: `tests/Feature/Performance/NPlusOneQueryTest.php` (3 Test Methods)
  1. `test_superadmin_views_have_no_n_plus_one_violations`: Memastikan seluruh halaman admin bebas dari N+1 query menggunakan eager loading (`with(...)`).
  2. `test_manager_views_have_no_n_plus_one_violations`: Memastikan halaman grid roster dan monitoring tim manajer terbebas dari query berulang.
  3. `test_employee_views_have_no_n_plus_one_violations`: Memastikan halaman dashboard presensi dan kalender staf sangat efisien dan ringan.

---

# BAGIAN B: AUTHOR — nesya ratu anastasya

---

### TIKET: PBL-10
**Judul Task:** Sprint 2 - [Availability] Form Input Jadwal Halangan Karyawan & Auto-Buffer  
**Author (Assignee di Jira):** nesya ratu anastasya  
**Tujuan Task:** Mengembangkan modul pengajuan ketersediaan / jadwal halangan kerja karyawan (izin/kuliah) dengan penerapan auto-buffer otomatis.

#### Lapisan Feature Test:
* Berkas: `tests/Feature/Employee/AvailabilityTest.php` (7 Test Methods)
  1. `test_employee_can_view_own_availability_list`: Karyawan melihat riwayat halangan kerja pribadi.
  2. `test_employee_can_submit_unavailability_with_auto_buffer_applied`: Mengajukan tanggal tidak bersedia kerja dengan buffer jam otomatis.
  3. `test_recurring_submission_stores_day_of_week`: Mengajukan halangan berulang mingguan (misal: setiap Senin jam kuliah).
  4. `test_submission_with_past_date_or_inverted_time_fails`: Menolak pengajuan tanggal mundur (masa lalu) atau rentang jam terbalik.
  5. `test_employee_can_delete_pending_availability_only`: Karyawan dapat membatalkan pengajuan yang masih berstatus pending.
  6. `test_employee_cannot_delete_already_approved_availability`: Mencegah pembatalan mandiri jika halangan telah disetujui manajer.
  7. `test_employee_cannot_delete_another_users_availability`: Mencegah manipulasi data halangan milik rekan kerja.

#### Lapisan E2E Playwright Test:
* Berkas: `e2e/employee_availability.spec.js` (5 Skenario)
  * `TC-AVL-01`: Menampilkan daftar riwayat halangan pribadi.
  * `TC-AVL-02`: Pengajuan halangan tanggal tunggal (*Single Date*).
  * `TC-AVL-03`: Pengajuan halangan berulang mingguan (*Recurring*).
  * `TC-AVL-04`: Validasi form penolakan jika tanggal lampau atau format waktu tidak valid.
  * `TC-AVL-05`: Karyawan membatalkan pengajuan status pending dan dicegat menghapus milik rekan kerja.

---

### TIKET: PBL-19
**Judul Task:** Sprint 2 - [Availability] Panel Persetujuan Ketersediaan (Manager)  
**Author (Assignee di Jira):** nesya ratu anastasya  
**Tujuan Task:** Membangun antarmuka dan endpoint bagi manajer untuk meninjau dan mengevaluasi pengajuan halangan staf departemennya.

#### Lapisan Feature Test:
* Berkas: `tests/Feature/Manager/AvailabilityApprovalTest.php` (5 Test Methods)
  1. `test_manager_can_view_pending_availabilities_in_own_department`: Manajer membuka daftar permohonan halangan staf departemennya.
  2. `test_manager_can_approve_availability_request`: Menyetujui pengajuan halangan kerja.
  3. `test_manager_can_reject_availability_request_with_reason`: Menolak pengajuan disertai alasan penolakan.
  4. `test_manager_cannot_approve_request_from_different_department`: Membatasi wewenang manajer hanya untuk departemennya sendiri.
  5. `test_employee_cannot_access_manager_approval_panel`: Mencegah karyawan biasa membuka panel persetujuan manajer.

#### Lapisan E2E Playwright Test:
* Berkas: `e2e/manager_availability_approval.spec.js` (3 Skenario)
  * `TC-APP-01`: Manajer melihat tabel daftar antrean persetujuan halangan staf.
  * `TC-APP-02`: Filter data berdasarkan status (*Pending*, *Approved*, *Rejected*).
  * `TC-APP-03`: Penolakan akses (403 Forbidden) saat akun Karyawan mencoba membuka panel manajer.

---

### TIKET: PBL-20
**Judul Task:** Sprint 3 - [Notif] Pusat Notifikasi In-App Karyawan & Manager  
**Author (Assignee di Jira):** nesya ratu anastasya  
**Tujuan Task:** Membangun pusat notifikasi in-app untuk mengabarkan perubahan jadwal, persetujuan tukar shift, dan broadcast open shift.

#### Lapisan Unit Test:
* Berkas: `tests/Unit/Services/NotificationServiceTest.php` (2 Test Methods)
  1. `test_send_creates_notification_using_user_instance`: Menguji fungsi kirim notifikasi menggunakan objek model User.
  2. `test_send_creates_notification_using_user_id_and_explicit_company_id`: Menguji kirim notifikasi dengan ID pengguna dan perutean tenant yang benar.

#### Lapisan Feature Test:
* Berkas: `tests/Feature/NotificationTest.php` (4 Test Methods)
  1. `test_user_can_view_notifications_list_and_unread_count`: Memuat daftar notifikasi dan jumlah pesan belum dibaca.
  2. `test_user_can_mark_single_notification_as_read`: Menandai satu notifikasi telah dibaca.
  3. `test_user_can_mark_all_notifications_as_read`: Menandai seluruh notifikasi telah dibaca.
  4. `test_notification_service_creates_records_correctly`: Memverifikasi rekaman pesan tersimpan akurat di tabel database.
* Berkas: `tests/Feature/Security/NotificationSecurityTest.php` (2 Test Methods)
  1. `test_user_cannot_mark_as_read_another_users_notification`: Mencegah pengguna membaca notifikasi milik pengguna lain.
  2. `test_mark_all_as_read_only_affects_authenticated_users_notifications`: Memastikan aksi bulk update hanya menyasar pesan milik pengguna yang sedang login.

#### Lapisan E2E Playwright Test:
* Berkas: `e2e/notifications.spec.js` (2 Skenario: Membuka halaman pusat notifikasi, mengklik lonceng notifikasi di navbar).

---

### TIKET: PBL-26
**Judul Task:** Sprint 1 - [UI/UX] Desain Landing Page Publik & Profil Akun  
**Author (Assignee di Jira):** nesya ratu anastasya  
**Tujuan Task:** Membangun antarmuka landing page publik dan modul manajemen profil akun pengguna.

#### Lapisan Feature Test:
* Berkas: `tests/Feature/LandingPageAndProfileTest.php` (7 Test Methods)
  1. `test_landing_page_can_be_rendered_successfully`: Halaman landing page publik berhasil dimuat (HTTP 200).
  2. `test_authenticated_user_can_view_profile_page`: Pengguna dapat membuka form profil akun.
  3. `test_user_can_update_profile_information_and_phone`: Memperbarui data nama dan nomor telepon.
  4. `test_user_can_upload_valid_profile_avatar`: Mengunggah foto avatar profil ke disk storage.
  5. `test_invalid_avatar_format_or_oversize_is_rejected`: Menolak file non-gambar atau ukuran melebihi 2MB.
  6. `test_invalid_phone_number_format_is_rejected`: Menolak format nomor telepon yang tidak sesuai.
  7. `test_user_can_delete_their_account`: Menghapus akun pengguna secara mandiri.
* Berkas: `tests/Feature/ProfileTest.php` (8 Test Methods)
  1. `test_authenticated_user_can_view_profile_page`: Verifikasi tampilan profil.
  2. `test_user_can_update_profile_information_and_phone`: Verifikasi mutasi profil.
  3. `test_user_can_upload_valid_profile_avatar`: Verifikasi unggah avatar.
  4. `test_invalid_avatar_format_or_oversize_is_rejected`: Penolakan format file tidak valid.
  5. `test_invalid_phone_number_format_is_rejected`: Penolakan nomor telepon salah.
  6. `test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged`: Status verifikasi email tetap aman saat email tidak diubah.
  7. `test_user_can_delete_their_account`: Verifikasi penghapusan akun.
  8. `test_correct_password_must_be_provided_to_delete_account`: Konfirmasi password wajib disediakan untuk hapus akun.

#### Lapisan E2E Playwright Test:
* Berkas: `e2e/landing_page.spec.js` (3 Skenario: Akses publik, tombol Masuk Sistem, verifikasi pilar fitur 0 Konflik).

---

### TIKET: PBL-41
**Judul Task:** Sprint 3 - [Employee] Modul Kalender & Roster Jadwal Kerja Karyawan  
**Author (Assignee di Jira):** nesya ratu anastasya  
**Tujuan Task:** Membangun antarmuka kalender dinas kerja mandiri karyawan dengan navigasi pekan.

#### Lapisan Feature Test:
* Berkas: `tests/Feature/Employee/EmployeeScheduleTest.php` (4 Test Methods)
  1. `test_employee_can_view_own_published_schedule_calendar`: Karyawan melihat kalender jadwal resmi miliknya.
  2. `test_employee_cannot_see_unpublished_draft_shifts`: Menjamin draf jadwal yang belum dipublikasikan tidak bocor ke karyawan.
  3. `test_employee_cannot_access_other_employee_schedule_details`: Mencegah karyawan melihat detail privat jadwal rekan kerja.
  4. `test_schedule_view_filters_correctly_by_week_range`: Filter navigasi rentang tanggal mingguan bekerja akurat.

#### Lapisan E2E Playwright Test:
* Berkas: `e2e/employee_schedule.spec.js` (1 Skenario: Karyawan melihat kalender kerja dan menavigasi antar minggu).

---

# BAGIAN C: AUTHOR — Umi Maharani

---

### TIKET: PBL-11
**Judul Task:** Sprint 1 - [Presensi] Modul Pencatatan Kehadiran Mandiri (Clock-In/Clock-Out)  
**Author (Assignee di Jira):** Umi Maharani  
**Tujuan Task:** Membangun modul absensi mandiri karyawan dengan verifikasi koordinat GPS Geofencing, validasi radius cabang, toleransi keterlambatan, dan kalkulasi jam kerja bersih.

#### Lapisan Unit Test:
* Berkas: `tests/Unit/Services/GeofencingHaversineTest.php` (4 Test Methods)
  1. `test_identical_coordinates_returns_zero_meters`: Memvalidasi formula Haversine menghasilkan 0 meter jika koordinat identik.
  2. `test_known_landmarks_distance`: Menghitung akurasi jarak antar landmark riil yang terkalibrasi.
  3. `test_short_distance_radius_accuracy`: Memverifikasi akurasi jarak dekat radius 10 s.d. 100 meter.
  4. `test_distance_is_symmetric`: Memastikan kalkulasi simetris bolak-balik bernilai sama.
* Berkas: `tests/Unit/Services/AttendanceStatusClassifierTest.php` (4 Test Methods)
  1. `test_classifies_on_time_when_clock_in_is_within_grace_period`: Klasifikasi Tepat Waktu saat clock-in dalam toleransi.
  2. `test_classifies_late_when_clock_in_exceeds_grace_period`: Klasifikasi Terlambat saat clock-in melebihi masa tenggang.
  3. `test_classifies_not_started_when_shift_has_not_started`: Status sebelum waktu shift dimulai.
  4. `test_classifies_absent_when_time_passes_threshold_without_clock_in`: Klasifikasi Mangkir saat melewati batas waktu tanpa absensi.

#### Lapisan Feature Test:
* Berkas: `tests/Feature/Employee/AttendanceTest.php` (14 Test Methods)
  1. `test_employee_can_view_attendance_dashboard`: Membuka dashboard absensi dan info shift hari ini.
  2. `test_attendance_page_uses_coordinates_from_user_branch`: Mengambil koordinat GPS cabang penempatan karyawan.
  3. `test_employee_can_clock_in_on_time_marked_as_present`: Melakukan Clock-In tepat waktu dan menyimpan jam/koordinat.
  4. `test_employee_clocking_in_after_grace_period_marked_as_late`: Clock-In melewati masa tenggang tercatat Late.
  5. `test_employee_cannot_double_clock_in`: Mencegah manipulasi aksi Clock-In ganda pada shift yang sama.
  6. `test_employee_can_clock_out_and_work_hours_calculated`: Clock-Out dan otomatis menghitung durasi jam kerja bersih.
  7. `test_employee_cannot_clock_out_without_clocking_in`: Mencegah Clock-Out jika belum Clock-In.
  8. `test_employee_location_api_endpoint_returns_json`: Endpoint koordinat mengembalikan JSON dengan header anti-cache.
  9. `test_clock_in_rejected_when_location_is_unconfigured`: Menolak presensi jika kantor belum disetel koordinatnya.
  10. `test_clock_in_allowed_when_branch_location_is_configured`: Mengizinkan presensi jika koordinat cabang valid.
  11. `test_clock_in_rejected_when_shift_has_ended`: Mencegah Clock-In pada shift yang sudah kedaluwarsa.
  12. `test_part_time_shift_calculates_overtime_when_exceeding_scheduled_hours`: Menghitung lembur otomatis saat part-time melebihi kuota jam.
  13. `test_employee_cannot_clock_in_too_early_before_shift`: Mencegah Clock-In terlalu dini sebelum batas 60 menit.
  14. `test_employee_can_clock_out_of_overnight_shift_the_next_morning`: Mendukung Clock-Out shift lintas malam pada pagi hari berikutnya.
* Berkas: `tests/Feature/Employee/AttendanceGeofencingTest.php` (3 Test Methods)
  1. `test_clock_in_allowed_when_within_office_radius`: Presensi diterima ketika berada dalam radius kantor.
  2. `test_clock_in_rejected_when_outside_office_radius`: Presensi ditolak jika berada di luar radius kantor.
  3. `test_clock_in_rejected_when_gps_coordinates_missing`: Menolak presensi jika koordinat GPS kosong.
* Berkas: `tests/Feature/Employee/AttendanceBoundaryTest.php` (5 Test Methods)
  1. `test_clock_in_at_exact_60_minutes_before_shift_is_allowed`: Tepat 60 menit sebelum shift: diizinkan.
  2. `test_clock_in_at_61_minutes_before_shift_is_rejected`: 61 menit sebelum shift: ditolak (terlalu dini).
  3. `test_clock_in_at_exact_15_minutes_grace_period_is_marked_present`: Tepat menit ke-15 toleransi: status hadir tepat waktu.
  4. `test_clock_in_at_16_minutes_is_marked_late`: Menit ke-16: status berubah terlambat.
  5. `test_clock_in_after_shift_ended_is_rejected`: Melewati jam selesai shift: ditolak.
* Berkas: `tests/Feature/Employee/AttendanceLocationApiTest.php` (2 Test Methods)
  1. `test_location_api_requires_authentication`: Endpoint lokasi wajib terautentikasi.
  2. `test_location_api_returns_json_with_outlet_details_and_no_cache`: Endpoint mengembalikan data JSON detail outlet tanpa cache.

#### Lapisan E2E Playwright Test:
* Berkas: `e2e/employee_attendance.spec.js` (4 Skenario: Lihat dashboard presensi, clock-in sukses & cegah double, clock-out sukses, cegah clock-out sebelum clock-in).

---

### TIKET: PBL-21
**Judul Task:** Sprint 2 - [Report] Rekapitulasi Timesheet & Ekspor Excel/PDF  
**Author (Assignee di Jira):** Umi Maharani  
**Tujuan Task:** Membangun modul agregasi jam kerja dan lembur serta ekspor rekapitulasi ke format Excel (.xlsx) dan PDF (.pdf).

#### Lapisan Unit Test:
* Berkas: `tests/Unit/TimesheetExportFilenameTest.php` (1 Test Method)
  1. `test_build_export_filename_for_various_roles`: Menguji konsistensi generator nama file ekspor unik berdasarkan peran, bulan, tahun, dan kode perusahaan.

#### Lapisan Feature Test:
* Berkas: `tests/Feature/Admin/TimesheetReportTest.php` (5 Test Methods)
  1. `test_admin_can_view_timesheet_report_page`: Membuka halaman rekapitulasi lembar waktu seluruh perusahaan.
  2. `test_timesheet_correctly_aggregates_regular_and_overtime_hours`: Perhitungan akurat agregasi jam kerja reguler dan lembur.
  3. `test_admin_can_export_timesheet_to_csv_excel`: Mengunduh laporan timesheet ke format Excel (.xlsx).
  4. `test_admin_can_export_timesheet_to_pdf`: Mengunduh laporan timesheet ke format dokumen cetak PDF (.pdf).
  5. `test_employee_cannot_access_timesheet_admin_report`: Karyawan dicegat (403) dari halaman laporan admin.
* Berkas: `tests/Feature/Manager/DepartmentTimesheetTest.php` (5 Test Methods)
  1. `test_manager_can_view_timesheet_of_own_department_staf_only`: Manajer hanya melihat data staf departemennya.
  2. `test_manager_cannot_export_timesheet_of_other_departments`: Mencegah ekspor data staf departemen lain.
  3. `test_manager_can_export_excel_and_pdf_of_own_department`: Menguji unduhan Excel & PDF khusus departemen manajer.
  4. `test_manager_export_pdf_limits_rows_to_fifty_for_memory_safety`: Paginasi aman 50 baris untuk mencegah memory exhaustion saat cetak PDF.
  5. `test_manager_timesheet_status_is_automatically_final_when_period_is_past`: Status lembar waktu otomatis terkunci jika periode bulan telah usai.
* Berkas: `tests/Feature/TimesheetExportUnifiedFormatTest.php` (3 Test Methods)
  1. `test_excel_export_structure_and_headers_match_specification`: Memvalidasi susunan header kolom Excel sesuai spesifikasi SRS.
  2. `test_export_filename_is_unique_per_role`: Format nama file seragam dan informatif.
  3. `test_data_access_isolation_per_role`: Menjamin isolasi hak akses data saat melakukan ekspor.

#### Lapisan E2E Playwright Test:
* Berkas: `e2e/admin_timesheet_report.spec.js` (5 Skenario: Lihat halaman rekapitulasi, filter dinamis, unduh Excel, unduh PDF, penolakan akses karyawan).

---

### TIKET: PBL-27
**Judul Task:** Sprint 3 - [Presensi] Monitoring Kehadiran Realtime Tim  
**Author (Assignee di Jira):** Umi Maharani  
**Tujuan Task:** Membangun dashboard pemantauan kehadiran realtime bagi manajer untuk memantau kehadiran staf pada hari berjalan.

#### Lapisan Unit Test:
* Berkas: `tests/Unit/Services/AttendanceMonitorServiceTest.php` (2 Test Methods)
  1. `test_classifies_on_time_attendance_correctly`: Klasifikasi akurat status tepat waktu pada agregasi monitoring.
  2. `test_classifies_approved_leave_as_on_leave_instead_of_absent`: Memastikan staf yang izin/cuti resmi diklasifikasikan sebagai On Leave dan bukan mangkir.

#### Lapisan Feature Test:
* Berkas: `tests/Feature/Manager/RealtimeAttendanceMonitoringTest.php` (8 Test Methods)
  1. `test_manager_can_view_realtime_attendance_monitoring_dashboard`: Memuat dashboard monitoring visual kehadiran staf.
  2. `test_realtime_feed_returns_accurate_counts_for_manager_department`: Payload JSON mengembalikan metrik akurat (hadir, terlambat, belum hadir, cuti).
  3. `test_manager_cannot_see_attendance_of_other_departments`: Isolasi departemen pada monitoring tim.
  4. `test_employee_cannot_access_realtime_monitoring`: Akses terproteksi dari intipan karyawan biasa.
  5. `test_guest_is_redirected_to_login`: Tamu tanpa login diredireksi ke login.
  6. `test_feed_only_contains_today_shift_assignments`: Memastikan feed hanya mencakup shift hari ini.
  7. `test_employee_on_leave_is_classified_as_on_leave_and_not_counted_as_absent`: Staf cuti tidak dihitung mangkir pada agregasi metrik.
  8. `test_feed_has_no_n_plus_one_queries`: Menguji performa kueri database bebas dari masalah N+1.

#### Lapisan E2E Playwright Test:
* Berkas: `e2e/manager_attendance_monitor.spec.js` (2 Skenario: Menampilkan dashboard monitoring kehadiran hari ini, memverifikasi feed realtime).

---

### TIKET: PBL-28
**Judul Task:** Sprint 3 - [Presensi] Otomatisasi Penutupan Absensi (Auto Clock-Out)  
**Author (Assignee di Jira):** Umi Maharani  
**Tujuan Task:** Mengembangkan cron command `php artisan attendance:auto-clock-out` untuk menutup shift terlewat secara otomatis dan menyediakan alur koreksi absensi (*regularization*).

#### Lapisan Unit Test:
* Berkas: `tests/Unit/Requests/AttendanceRegularizationRequestTest.php` (3 Test Methods)
  1. `test_rules_contains_required_fields`: Memastikan form koreksi mewajibkan alasan, jam clock-in usulan, dan jam clock-out usulan.
  2. `test_validation_fails_when_reason_is_too_short`: Menolak alasan pengajuan yang terlalu singkat.
  3. `test_authorize_allows_only_employee_role`: Hak otorisasi form hanya untuk role employee.

#### Lapisan Feature Test:
* Berkas: `tests/Feature/Console/AutoClockOutCommandTest.php` (9 Test Methods)
  1. `test_auto_clockout_command_closes_overdue_shifts_at_shift_end_time`: Cron otomatis menutup shift terlewat setelah buffer waktu habis.
  2. `test_auto_clockout_does_not_affect_active_shifts_within_buffer`: Tidak menutup shift yang masih berlangsung normal.
  3. `test_auto_clockout_handles_overnight_shifts_correctly`: Penanganan tepat untuk auto clock-out shift lintas malam.
  4. `test_auto_clockout_is_idempotent`: Menjalankan cron berulang kali tidak merusak data yang telah diproses.
  5. `test_employee_can_submit_attendance_regularization_for_auto_clocked_out_shift`: Karyawan yang terkena auto clock-out dapat mengajukan koreksi presensi.
  6. `test_employee_cannot_submit_regularization_for_normal_shift_or_duplicate`: Mencegah pengajuan koreksi pada presensi normal atau duplikat.
  7. `test_manager_can_approve_attendance_regularization_and_update_hours`: Persetujuan manajer otomatis memperbarui jam presensi dan durasi kerja di database.
  8. `test_manager_can_reject_attendance_regularization`: Manajer dapat menolak klaim koreksi jam kerja staf.
  9. `test_manager_cannot_approve_out_of_department_or_self_regularization`: Mencegah approval di luar departemen atau untuk diri sendiri.
* Berkas: `tests/Feature/Console/AutoClockOutAuditTest.php` (2 Test Methods)
  1. `test_auto_clock_out_creates_audit_log_entry`: Eksekusi auto clock-out dicatat ke audit log.
  2. `test_auto_clock_out_skips_when_disabled_in_settings`: Melewati penutupan jika dinonaktifkan di setelan perusahaan.
* Berkas: `tests/Feature/Employee/AttendanceRegularizationTest.php` (5 Test Methods)
  1. `test_employee_can_view_regularization_index_page`: Membuka halaman riwayat koreksi karyawan.
  2. `test_employee_can_request_regularization_index_as_json`: Mengambil payload JSON daftar koreksi.
  3. `test_employee_can_submit_regularization_for_auto_clocked_out_shift`: Mengirim permohonan koreksi.
  4. `test_employee_cannot_submit_regularization_for_another_employees_shift`: Menolak permohonan atas shift rekan kerja.
  5. `test_employee_cannot_submit_regularization_for_normal_shift`: Menolak pengajuan pada shift normal.
* Berkas: `tests/Feature/Manager/AttendanceRegularizationTest.php` (4 Test Methods)
  1. `test_manager_can_view_department_regularization_list`: Membuka daftar antrean koreksi departemen.
  2. `test_manager_can_approve_regularization_and_update_hours`: Menyetujui pengajuan dan memperbarui jam kerja.
  3. `test_manager_can_reject_regularization`: Menolak permohonan koreksi.
  4. `test_manager_cannot_approve_out_of_department_regularization`: Menolak approval untuk staf di luar departemen manajer.

#### Lapisan E2E Playwright Test:
* Berkas: `e2e/attendance_regularization.spec.js` (3 Skenario: Riwayat koreksi karyawan, antrean persetujuan manajer, pencegahan akses karyawan ke panel approval).

---

### TIKET: PBL-29
**Judul Task:** Sprint 5 - [SQA] Implementasi Audit Trail Log Otomatis  
**Author (Assignee di Jira):** Umi Maharani  
**Tujuan Task:** Membangun pencatatan rekam jejak aktivitas sistem (*Audit Trail*) secara otomatis via Eloquent Observer.

#### Lapisan Feature Test:
* Berkas: `tests/Feature/Admin/AuditLogTest.php` (8 Test Methods)
  1. `test_admin_can_view_audit_logs_table`: Superadmin dapat melihat tabel log aktivitas sistem.
  2. `test_audit_log_renders_friendly_labels_for_non_crud_actions_and_models`: Label deskriptif yang mudah dibaca untuk aksi non-CRUD (misal broadcast shift, publish roster).
  3. `test_creating_or_updating_model_automatically_writes_audit_log`: Observer model otomatis mencatat setiap mutasi data ke tabel audit trail.
  4. `test_audit_log_captures_accurate_json_diff_and_ip_address`: Menyimpan rekaman perubahan data (old vs new JSON) beserta alamat IP aktor.
  5. `test_non_admin_cannot_access_audit_logs`: Menolak akses log audit untuk role non-admin.
  6. `test_audit_logs_are_strictly_isolated_by_company`: Log audit terisolasi ketat per tenant perusahaan.
  7. `test_audit_log_creation_automatically_assigns_correct_company_id`: Penautan ID perusahaan secara otomatis pada setiap rekaman log.
  8. `test_audit_log_timestamp_follows_company_timezone_settings`: Waktu audit mengikuti zona waktu operasional perusahaan.

---

### TIKET: PBL-44
**Judul Task:** Sprint 4 - [Timesheet] Modul Timesheet Personal Karyawan & Rekap Tim Manager  
**Author (Assignee di Jira):** Umi Maharani  
**Tujuan Task:** Membangun modul transparansi lembar waktu kerja mandiri staf dan slip rekap kehadiran.

#### Lapisan Feature Test:
* Berkas: `tests/Feature/Employee/PersonalTimesheetTest.php` (6 Test Methods)
  1. `test_employee_can_view_own_timesheet_history`: Karyawan melihat riwayat jam kerja mandiri.
  2. `test_employee_cannot_see_other_employee_timesheet`: Mencegah akses ke timesheet rekan kerja.
  3. `test_employee_can_export_own_timesheet_to_excel_and_pdf`: Karyawan mengunduh slip timesheet mandiri ke Excel dan PDF.
  4. `test_employee_cannot_export_other_employee_timesheet`: Menolak manipulasi ekspor data milik orang lain.
  5. `test_employee_timesheet_page_contains_month_filter_and_export_buttons`: Verifikasi komponen filter bulan dan tombol aksi.
  6. `test_employee_export_contains_combined_timesheet_and_attendance_logs_filtered_by_month`: Memverifikasi isi berkas ekspor memuat log tanggal, jam masuk, jam keluar, dan total jam kerja.

---

# BAGIAN D: AUTHOR — muhammadyusuffff0812

---

### TIKET: PBL-9
**Judul Task:** Sprint 1 - [Master Data] Modul CRUD Departemen & Posisi Jabatan  
**Author (Assignee di Jira):** muhammadyusuffff0812  
**Tujuan Task:** Membangun modul master struktur organisasi perusahaan (Departemen dan Posisi Jabatan) lengkap dengan validasi koordinat geofencing cabang.

#### Lapisan Unit Test:
* Berkas: `tests/Unit/Models/DepartmentTest.php` (4 Test Methods)
  1. `test_is_location_configured_returns_true_when_both_coordinates_present`: Helper koordinat bernilai true jika latitude dan longitude terisi.
  2. `test_is_location_configured_returns_false_when_coordinates_are_null`: Helper koordinat bernilai false jika koordinat kosong.
  3. `test_radius_and_radius_meters_are_synchronized`: Sinkronisasi atribut radius kantor.
  4. `test_department_falls_back_to_company_coordinates`: Mekanisme fallback ke koordinat kantor pusat perusahaan jika cabang belum memiliki koordinat mandiri.

#### Lapisan Feature Test:
* Berkas: `tests/Feature/Admin/DepartmentPositionTest.php` (6 Test Methods)
  1. `test_superadmin_can_view_department_and_position_list`: Membuka halaman indeks departemen dan jabatan.
  2. `test_superadmin_can_create_department_with_valid_data`: Membuat departemen baru dengan data valid.
  3. `test_duplicate_department_name_in_same_company_is_rejected`: Mencegah nama departemen kembar dalam perusahaan yang sama.
  4. `test_user_cannot_access_departments_from_another_company`: Menjamin isolasi data departemen antar tenant.
  5. `test_non_admin_cannot_access_department_crud`: Membatasi aksi CRUD hanya untuk Superadmin.
  6. `test_position_cannot_be_deleted_if_has_active_employees`: Proteksi jabatan dari penghapusan jika masih menaungi staf aktif.
* Berkas: `tests/Feature/Admin/DepartmentLifecycleTest.php` (3 Test Methods)
  1. `test_superadmin_can_update_department_details_and_geofence_coordinates`: Memperbarui detail nama departemen dan koordinat geofence cabang.
  2. `test_department_cannot_be_deleted_if_has_active_employees`: Proteksi departemen dari penghapusan jika masih memiliki karyawan aktif.
  3. `test_department_can_be_deleted_if_no_active_employees`: Departemen tanpa karyawan aman dihapus dari sistem.

#### Lapisan E2E Playwright Test:
* Berkas: `e2e/admin_department_position.spec.js` (5 Skenario: Lihat daftar departemen & posisi, tambah departemen, penolakan nama duplikat, isolasi antar perusahaan, pencegahan akses manajer 403).

---

### TIKET: PBL-18
**Judul Task:** Sprint 2 - [Master] CRUD Template Shift Rutin  
**Author (Assignee di Jira):** muhammadyusuffff0812  
**Tujuan Task:** Membangun modul master template shift harian (Pagi, Siang, Malam) lengkap dengan label waktu, kuota posisi, dan kode warna kalender.

#### Lapisan Feature Test:
* Berkas: `tests/Feature/Admin/ShiftTemplateTest.php` (6 Test Methods)
  1. `test_admin_can_view_shift_templates_list`: Membuka daftar master template shift perusahaan.
  2. `test_admin_can_create_shift_template_with_color_and_quota`: Membuat template baru dengan kode warna dan kuota staf.
  3. `test_admin_can_update_and_delete_shift_template`: Mengedit jam kerja template dan menghapusnya.
  4. `test_templates_are_scoped_to_user_company`: Isolasi data master template shift per perusahaan.
  5. `test_invalid_time_range_or_missing_department_fails`: Menolak input jika jam terbalik atau departemen tidak dipilih.
  6. `test_admin_cannot_delete_shift_template_in_use_by_shifts`: Mencegah penghapusan template yang sedang aktif digunakan dalam jadwal berjalan.

#### Lapisan E2E Playwright Test:
* Berkas: `e2e/shift_template.spec.js` (5 Skenario: Lihat tabel template, tambah template baru dengan kuota & warna, edit & hapus template, isolasi multi-tenant, validasi error form).

---

### TIKET: PBL-24
**Judul Task:** Sprint 2 - [Master] Pengaturan Dinamis Ketenagakerjaan (PP 35/2021)  
**Author (Assignee di Jira):** muhammadyusuffff0812  
**Tujuan Task:** Membangun modul konfigurasi regulasi ketenagakerjaan, batasan jam reguler, jam lembur, dan lokasi geofencing kantor.

#### Lapisan Unit Test:
* Berkas: `tests/Unit/Services/LaborLawComplianceTest.php` (4 Test Methods)
  1. `test_standard_weekly_regular_hours_capped_at_40_hours`: Batas standar reguler mingguan dipatok 40 jam sesuai regulasi ketenagakerjaan.
  2. `test_overtime_limits_enforce_daily_and_weekly_maximums`: Batas toleransi lembur harian (maks 4 jam/hari) dan mingguan (maks 18 jam/minggu).
  3. `test_inter_shift_rest_period_requires_at_least_11_consecutive_hours`: Wajib jeda istirahat minimal 11 jam antar giliran shift.
  4. `test_weekly_rest_day_enforces_maximum_consecutive_work_days`: Batas maksimal 6 hari kerja berturut-turut sebelum wajib 1 hari libur.

#### Lapisan Feature Test:
* Berkas: `tests/Feature/Admin/CompanySettingTest.php` (11 Test Methods)
  1. `test_superadmin_can_view_and_edit_company_settings`: Memuat form konfigurasi regulasi perusahaan.
  2. `test_settings_update_persists_properly_with_validation_rules`: Menyimpan batas jam kerja reguler mingguan dan toleransi keterlambatan.
  3. `test_non_admin_cannot_access_or_modify_settings`: Memblokir non-admin dari perubahan konfigurasi kebijakan.
  4. `test_invalid_hours_or_negative_buffer_is_rejected`: Mencegah input angka negatif atau jam tidak rasional.
  5. `test_superadmin_can_update_office_location_and_geofencing_settings`: Menyimpan titik koordinat kantor pusat (lat, long, radius).
  6. `test_settings_update_handles_null_or_empty_attendance_radius_meters_safely`: Fallback radius default saat input kosong.
  7. `test_superadmin_can_update_specific_branch_location_and_radius`: Mengonfigurasi lokasi geofence per cabang/outlet.
  8. `test_employee_attendance_page_synchronizes_with_updated_branch_location_from_superadmin`: Memverifikasi halaman presensi tersinkronisasi otomatis dengan perubahan koordinat admin.
  9. `test_branch_location_supports_decimal_comma_normalization`: Normalisasi desimal koma (,) menjadi titik (.) pada koordinat GPS.
  10. `test_settings_update_supports_unlimited_hours_toggle`: Mendukung toggle jam tanpa batas untuk usaha yang fleksibel.
  11. `test_setting_hours_above_pp_standard_persists_successfully`: Memastikan fleksibilitas kustomisasi jam kerja sesuai kebijakan internal.

#### Lapisan E2E Playwright Test:
* Berkas: `e2e/admin_company_setting.spec.js` (4 Skenario: Lihat pengaturan regulasi, perbarui parameter jam kerja, penolakan input jam tidak valid, penolakan akses manajer 403).

---

### TIKET: PBL-25
**Judul Task:** Sprint 3 - [Master] Manajemen Data Karyawan per Tim (Manager)  
**Author (Assignee di Jira):** muhammadyusuffff0812  
**Tujuan Task:** Membangun direktori staf bawahan manajer untuk memeriksa komposisi tipe kontrak kerja (Full-Time, Part-Time, Freelance) dan jabatan.

#### Lapisan Unit Test:
* Berkas: `tests/Unit/Models/UserTest.php` (3 Test Methods)
  1. `test_role_helper_methods`: Menguji fungsi pembantu status peran (`isSuperAdmin()`, `isManager()`, `isEmployee()`).
  2. `test_employment_type_helper_methods`: Menguji fungsi status ikatan kerja (`isFullTime()`, `isPartTime()`, `isFreelance()`).
  3. `test_branch_id_accessor_and_mutator_maps_to_department_id`: Memastikan mutator relasi cabang memetakan ke field departemen secara konsisten.

#### Lapisan Feature Test:
* Berkas: `tests/Feature/Manager/TeamEmployeeTest.php` (4 Test Methods)
  1. `test_manager_can_view_employee_list_in_own_department`: Manajer dapat memantau seluruh bawahan di departemennya.
  2. `test_manager_cannot_view_employee_details_from_different_department`: Manajer dibatasi hanya untuk departemen yang dipimpinnya.
  3. `test_search_and_filter_by_employment_type_and_position`: Filter pencarian staf berdasarkan jenis kontrak kerja (FT/PT/FL).
  4. `test_employee_cannot_access_manager_employee_directory`: Karyawan biasa diblokir dari direktori staf manajer.

#### Lapisan E2E Playwright Test:
* Berkas: `e2e/manager_team_employees.spec.js` (1 Skenario: Manajer membuka direktori staf departemen dan memeriksa informasi kontrak kerja).

---

### TIKET: PBL-38
**Judul Task:** Sprint 3 - [UI/UX] Implementasi Role-Based Dashboard Operasional  
**Author (Assignee di Jira):** muhammadyusuffff0812  
**Tujuan Task:** Membangun dashboard operasional yang menampilkan kartu metrik dinamis sesuai peran login (Super Admin, Manager, Employee).

#### Lapisan Feature Test:
* Berkas: `tests/Feature/DashboardTest.php` (6 Test Methods)
  1. `test_superadmin_sees_admin_specific_dashboard_metrics`: Superadmin melihat metrik agregat seluruh perusahaan (total staf, total departemen, status langganan).
  2. `test_manager_without_company_is_forbidden`: Manajer tanpa perusahaan dicegat (403).
  3. `test_manager_with_valid_company_sees_department_schedule_and_pending_approvals`: Manajer melihat ringkasan roster jadwal departemen dan antrean persetujuan.
  4. `test_employee_without_company_is_forbidden`: Karyawan tanpa perusahaan dicegat (403).
  5. `test_employee_with_valid_company_sees_own_assigned_shifts_and_hours`: Karyawan melihat ringkasan shift terdekat dan akumulasi jam kerja.
  6. `test_unauthenticated_user_is_redirected_to_login`: Tamu tanpa login diarahkan ke login.

---

### TIKET: PBL-42
**Judul Task:** Sprint 4 - [Master] Modul Manajemen Akun Pengguna & Pendaftaran Multi-Role (Super Admin)  
**Author (Assignee di Jira):** muhammadyusuffff0812  
**Tujuan Task:** Membangun modul administrasi akun pengguna bagi Super Admin (CRUD pengguna, penugasan departemen & jabatan, soft delete, dan pemblokiran akun resigned).

#### Lapisan Feature Test:
* Berkas: `tests/Feature/Admin/UserManagementTest.php` (16 Test Methods)
  1. `test_superadmin_can_view_user_list_in_own_company`: Menampilkan daftar pengguna khusus perusahaan yang login.
  2. `test_superadmin_can_register_new_manager_and_employee`: Registrasi akun baru dengan penugasan departemen, jabatan, dan tipe kontrak.
  3. `test_email_uniqueness_is_enforced_upon_user_creation`: Mencegah pendaftaran email duplikat.
  4. `test_superadmin_cannot_access_or_edit_users_from_another_company`: Proteksi pengeditan akun milik tenant lain.
  5. `test_non_admin_cannot_access_user_management_module`: Memblokir non-admin dari modul user management.
  6. `test_cannot_assign_department_or_position_from_another_company`: Validasi keterikatan referensi departemen dengan tenant.
  7. `test_cannot_assign_position_not_belonging_to_selected_department`: Validasi relasi jabatan harus sinkron dengan departemen yang dipilih.
  8. `test_superadmin_cannot_delete_own_account`: Mencegah admin menghapus akun miliknya sendiri (*self-lockout prevention*).
  9. `test_cannot_delete_user_with_active_shift_assignment`: Mencegah penghapusan karyawan yang masih memiliki jadwal dinas aktif.
  10. `test_password_is_not_changed_when_empty_during_update`: Kata sandi tidak berubah jika form input password dikosongkan saat update.
  11. `test_user_is_soft_deleted_upon_deletion`: Memastikan mekanisme Soft Deletes agar riwayat audit presensi tidak hilang.
  12. `test_superadmin_cannot_create_or_promote_user_to_superadmin`: Mencegah eskalasi hak istimewa (*privilege escalation*).
  13. `test_superadmin_can_search_users_by_phone_number_without_query_error`: Fitur pencarian staf berdasarkan nomor telepon.
  14. `test_phone_number_is_properly_saved_and_displayed`: Nomor telepon tersimpan dan tampil presisi di tabel data.
  15. `test_user_with_inactive_status_is_blocked_from_logging_in`: Memblokir login bagi staf berstatus nonaktif.
  16. `test_superadmin_can_update_own_account_without_role_validation_error`: Superadmin dapat mengupdate profil akunnya sendiri tanpa konflik validasi role.
* Berkas: `tests/Feature/Auth/AccountStatusAuthenticationTest.php` (2 Test Methods)
  1. `test_active_user_can_authenticate_successfully`: Pengguna aktif diizinkan masuk.
  2. `test_resigned_user_cannot_authenticate`: Pengguna berstatus resigned dicegat saat login.

---

### TIKET: PBL-43
**Judul Task:** Sprint 4 - [Master] Modul Profil & Master Data Perusahaan Tenant (Super Admin)  
**Author (Assignee di Jira):** muhammadyusuffff0812  
**Tujuan Task:** Membangun modul kelola profil perusahaan tenant bagi Super Admin.

#### Lapisan Feature Test:
* Berkas: `tests/Feature/Admin/CompanyProfileTest.php` (5 Test Methods)
  1. `test_superadmin_can_view_own_company_profile`: Superadmin melihat detail profil perusahaannya.
  2. `test_superadmin_can_update_company_details_with_valid_data`: Memperbarui nama perusahaan, alamat, dan kontak.
  3. `test_invalid_timezone_or_email_format_is_rejected`: Validasi penolakan zona waktu atau format email salah.
  4. `test_user_cannot_update_other_company_profile`: Mencegah manipulasi profil perusahaan tenant lain.
  5. `test_non_superadmin_roles_cannot_access_or_update_company_profile`: Penolakan HTTP 403 bagi role non-superadmin.

---

## 3. Matriks Traceability Dokumen vs Pengujian Riil

| Sumber Data | Jumlah Pengujian | Keterangan & Status Sinkronisasi |
| :--- | :---: | :--- |
| **Manual Test Cases (`docs/test_case_jadwalin.xlsx`)** | 66 Kasus Uji | Mencakup 10 modul fungsional. 100% terakomodasi dan divalidasi oleh suite Feature Test dan E2E Test. |
| **Dokumen Automated Testing (`docs/AUTOMATED TESTING...xlsx`)** | 60 Kasus Uji | 60 skenario pengujian Playwright E2E yang terdokumentasi formal pada tabel laporan. |
| **Kode Pengujian Playwright Nyata (`e2e/*.spec.js`)** | **74 Kasus Uji** | Terdapat **14 skenario ekstra** di kode riil (pengujian siklus hidup *shift swap lifecycle*, *open shift lifecycle*, *regularization*, *navigation role menu*, *landing page*, dan *notifications*). |
| **Kode Pengujian PHPUnit Nyata (`tests/Unit` & `tests/Feature`)** | **280 Kasus Uji** | 54 Unit Test + 226 Feature Test, berjalan di in-memory SQLite (~13 detik), mencakup kalkulasi matematis, concurrency lock, dan proteksi multi-tenant. |
| **TOTAL SELURUH PENGUJIAN OTOMATIS KODE** | **354 Kasus Uji** | **100% PASS (Green)** |

---

## 4. Perintah Eksekusi Pengujian

```bash
# Menjalankan seluruh Unit & Feature Test (280 pengujian)
php artisan test

# Menjalankan hanya Unit Test (54 pengujian)
php artisan test --testsuite=Unit

# Menjalankan hanya Feature Test (226 pengujian)
php artisan test --testsuite=Feature

# Menjalankan seluruh pengujian E2E Browser (74 skenario)
npx playwright test

# Menjalankan standardisasi kode PSR-12 / Laravel Pint
vendor/bin/pint --format agent
```

---
*Dokumen ini disusun secara lengkap dan mendalam sebagai laporan resmi Penjaminan Mutu Perangkat Lunak (Software Quality Assurance) Proyek PBL Jadwalin.*
