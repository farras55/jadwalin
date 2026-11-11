<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class AuditLog extends Model
{
    use HasFactory;

    /**
     * Pemetaan nama model teknis ke label fitur yang ramah pengguna.
     */
    public const MODEL_LABELS = [
        'Shift' => 'Jadwal Shift',
        'ShiftAssignment' => 'Penugasan Karyawan',
        'ShiftSwap' => 'Tukar Shift',
        'OpenShiftClaim' => 'Klaim Shift Terbuka',
        'CompanySetting' => 'Pengaturan Perusahaan',
        'Availability' => 'Ketersediaan Waktu',
        'Department' => 'Departemen & Cabang',
        'User' => 'Pengguna',
        'AttendanceRegularization' => 'Koreksi Presensi',
    ];

    /**
     * Pemetaan nama aksi teknis / event sistem ke label operasional bisnis.
     */
    public const ACTION_LABELS = [
        'create' => 'Buat Baru',
        'update' => 'Pengeditan Data',
        'delete' => 'Penghapusan Data',
        'auto_clockout' => 'Absen Keluar Otomatis',
        'broadcast_existing_shift' => 'Pengiriman Jadwal Shift',
        'broadcast_open_shift' => 'Pengiriman Jadwal Terbuka',
        'approve_shift_swap' => 'Persetujuan Tukar Shift',
        'reject_shift_swap' => 'Penolakan Tukar Shift',
        'swap_approved' => 'Persetujuan Tukar Shift',
        'swap_rejected' => 'Penolakan Tukar Shift',
        'claim_approved' => 'Persetujuan Klaim',
        'claim_rejected' => 'Penolakan Klaim',
        'regularization_submitted' => 'Pengajuan Koreksi Presensi',
        'regularization_approved' => 'Persetujuan Koreksi Presensi',
        'regularization_rejected' => 'Penolakan Koreksi Presensi',
    ];

    /**
     * Pemetaan nama kolom/field database ke istilah ramah pengguna awam.
     */
    public const FIELD_LABELS = [
        'title' => 'Judul Shift',
        'name' => 'Nama',
        'email' => 'Email',
        'role' => 'Jabatan / Peran',
        'phone_number' => 'Nomor Telepon',
        'start_time' => 'Jam Mulai',
        'end_time' => 'Jam Selesai',
        'date' => 'Tanggal',
        'quota' => 'Kuota Karyawan',
        'status' => 'Status',
        'department_id' => 'Departemen',
        'position_id' => 'Posisi / Jabatan',
        'user_id' => 'Karyawan',
        'check_in' => 'Waktu Masuk',
        'check_out' => 'Waktu Pulang',
        'regular_hours' => 'Jam Kerja Reguler',
        'overtime_hours' => 'Jam Kerja Lembur',
        'is_holiday' => 'Hari Libur Nasional',
        'reason' => 'Alasan / Keterangan',
        'note' => 'Catatan',
        'review_note' => 'Catatan Peninjauan',
        'latitude' => 'Titik Lintang (Latitude)',
        'longitude' => 'Titik Bujur (Longitude)',
        'radius_meters' => 'Radius Presensi (Meter)',
        'address' => 'Alamat Kantor / Lokasi',
        'max_regular_hours' => 'Batas Jam Reguler',
        'max_overtime_hours' => 'Batas Jam Lembur',
        'break_minutes' => 'Durasi Istirahat (Menit)',
        'color' => 'Warna Label',
        'type' => 'Jenis / Tipe',
        'approved_by' => 'Disetujui Oleh',
        'approved_at' => 'Waktu Persetujuan',
        'rejected_by' => 'Ditolak Oleh',
        'rejected_at' => 'Waktu Penolakan',
        'timezone' => 'Zona Waktu',
    ];

    /**
     * Field internal/teknis yang disembunyikan dari peninjauan awam.
     */
    public const HIDDEN_FIELDS = [
        'id',
        'company_id',
        'created_at',
        'updated_at',
        'deleted_at',
        'remember_token',
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'email_verified_at',
    ];

    protected $fillable = [
        'company_id',
        'user_id',
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'model_id' => 'integer',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The "booted" method of the model.
     * Menerapkan Global Scope agar audit log selalu terisolasi berdasarkan company_id tenant.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('company', function ($query): void {
            if (Auth::check() && Auth::user()->company_id) {
                $query->where((new static)->getTable().'.company_id', Auth::user()->company_id);
            }
        });
    }

    /**
     * Format timestamp createdAt sesuai timezone perusahaan secara dinamis.
     */
    public function formattedCreatedAt(?string $timezone = null): string
    {
        if (! $this->created_at) {
            return '-';
        }

        $tz = $timezone
            ?: ($this->company?->timezone ?? (Auth::check() ? Auth::user()->company?->timezone : null) ?? config('app.timezone', 'Asia/Jakarta'));

        $abbr = self::getTimezoneAbbreviation($tz);

        return $this->created_at->timezone($tz)->locale('id')->translatedFormat('l, d M Y • H:i').' '.$abbr;
    }

    /**
     * Dapatkan singkatan zona waktu (WIB, WITA, WIT, dll.) secara dinamis.
     */
    public static function getTimezoneAbbreviation(string $timezone): string
    {
        $map = [
            'Asia/Jakarta' => 'WIB',
            'Asia/Pontianak' => 'WIB',
            'Asia/Makassar' => 'WITA',
            'Asia/Ujung_Pandang' => 'WITA',
            'Asia/Jayapura' => 'WIT',
        ];

        if (isset($map[$timezone])) {
            return $map[$timezone];
        }

        try {
            $abbr = now($timezone)->format('T');

            return match ($abbr) {
                '+07' => 'WIB',
                '+08' => 'WITA',
                '+09' => 'WIT',
                default => $abbr,
            };
        } catch (\Throwable) {
            return 'WIB';
        }
    }

    /**
     * Mendapatkan label aksi yang ramah bagi pengguna non-teknis.
     */
    public function getActionLabelAttribute(): string
    {
        $rawAction = strtolower(trim($this->action ?? ''));

        if (isset(self::ACTION_LABELS[$rawAction])) {
            return self::ACTION_LABELS[$rawAction];
        }

        $upperAction = strtoupper($this->action ?? '');
        if (in_array($upperAction, ['CREATE', 'UPDATE', 'DELETE'], true)) {
            return $upperAction;
        }

        return ucwords(str_replace(['_', '-'], ' ', $rawAction));
    }

    /**
     * Mendapatkan kelas CSS styling badge untuk aksi terkait.
     */
    public function getActionBadgeClassAttribute(): string
    {
        $rawAction = strtolower(trim($this->action ?? ''));

        return match ($rawAction) {
            'create' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'update' => 'bg-blue-50 text-blue-700 border-blue-200',
            'delete' => 'bg-rose-50 text-rose-700 border-rose-200',
            'auto_clockout' => 'bg-amber-50 text-amber-700 border-amber-200',
            'broadcast_existing_shift', 'broadcast_open_shift' => 'bg-purple-50 text-purple-700 border-purple-200',
            'approve_shift_swap', 'swap_approved', 'claim_approved', 'regularization_approved' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            'reject_shift_swap', 'swap_rejected', 'claim_rejected', 'regularization_rejected' => 'bg-rose-50 text-rose-700 border-rose-200',
            default => 'bg-purple-50 text-purple-700 border-purple-200',
        };
    }

    /**
     * Mendapatkan kelas warna dot status pada badge aksi.
     */
    public function getActionDotClassAttribute(): string
    {
        $rawAction = strtolower(trim($this->action ?? ''));

        return match ($rawAction) {
            'create' => 'bg-emerald-500',
            'update' => 'bg-blue-500',
            'delete' => 'bg-rose-500',
            'auto_clockout' => 'bg-amber-500',
            'broadcast_existing_shift', 'broadcast_open_shift' => 'bg-purple-500',
            'approve_shift_swap', 'swap_approved', 'claim_approved', 'regularization_approved' => 'bg-indigo-500',
            'reject_shift_swap', 'swap_rejected', 'claim_rejected', 'regularization_rejected' => 'bg-rose-500',
            default => 'bg-purple-500',
        };
    }

    /**
     * Mendapatkan nama fitur yang ramah pengguna dari model terkait.
     */
    public function getModelLabelAttribute(): string
    {
        $basename = class_basename($this->model_type ?? '');

        return self::MODEL_LABELS[$basename] ?? $basename;
    }
}
