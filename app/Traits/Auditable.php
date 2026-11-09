<?php

namespace App\Traits;

use App\Models\AuditLog;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    /**
     * Boot the Auditable trait on the model.
     */
    public static function bootAuditable(): void
    {
        static::created(function (Model $model): void {
            static::recordAuditLog($model, 'create');
        });

        static::updated(function (Model $model): void {
            static::recordAuditLog($model, 'update');
        });

        static::deleted(function (Model $model): void {
            static::recordAuditLog($model, 'delete');
        });
    }

    /**
     * Record an audit log entry for the given action and model.
     */
    protected static function recordAuditLog(Model $model, string $action): void
    {
        // Allow models or callers to temporarily disable audit logging
        if (property_exists($model, 'auditDisabled') && $model->auditDisabled) {
            return;
        }

        $oldValues = null;
        $newValues = null;

        if ($action === 'create') {
            $newValues = static::filterAuditableAttributes($model, $model->getAttributes());
        } elseif ($action === 'delete') {
            $oldValues = static::filterAuditableAttributes($model, $model->getOriginal());
        } elseif ($action === 'update') {
            $changes = $model->getChanges();
            $old = [];
            $new = [];

            foreach ($changes as $key => $newValue) {
                if (static::isIgnoredAuditAttribute($model, $key)) {
                    continue;
                }

                $oldValue = $model->getOriginal($key);

                // Only record if the value actually changed
                if ($oldValue !== $newValue) {
                    $old[$key] = $oldValue;
                    $new[$key] = $newValue;
                }
            }

            // If no auditable attributes changed, do not create an audit log
            if (empty($old) && empty($new)) {
                return;
            }

            $oldValues = $old;
            $newValues = $new;
        }

        // Tentukan company ID secara otomatis dan aman
        $companyId = static::resolveCompanyId($model);

        // Validasi ketat: Log tidak boleh dibuat jika company_id bernilai null
        if ($companyId === null) {
            return;
        }

        // Determine user ID (authenticated user or fallback to model user_id)
        $userId = Auth::id() ?? ($model->user_id ?? null);

        // Capture IP address and User Agent
        $ipAddress = Request::ip();
        $userAgent = Request::userAgent();

        AuditLog::create([
            'company_id' => $companyId,
            'user_id' => $userId,
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    /**
     * Menyelesaikan company_id secara otomatis dari model, relasi, atau user login.
     */
    protected static function resolveCompanyId(Model $model): ?int
    {
        // 1. Cek langsung atribut company_id atau nilai original model
        if (! empty($model->company_id)) {
            return (int) $model->company_id;
        }

        if (! empty($model->getOriginal('company_id'))) {
            return (int) $model->getOriginal('company_id');
        }

        // 2. Jika model adalah instance Company itu sendiri
        if ($model instanceof Company) {
            return (int) $model->getKey();
        }

        // 3. Cek relasi 'company' jika tersedia
        if (method_exists($model, 'company')) {
            try {
                $company = $model->company;
                if ($company instanceof Model && ! empty($company->getKey())) {
                    return (int) $company->getKey();
                }
            } catch (\Throwable) {
                // Lewati jika pemanggilan relasi gagal
            }
        }

        // 4. Cek relasi 'department' jika tersedia
        if (method_exists($model, 'department')) {
            try {
                $department = $model->department;
                if ($department instanceof Model && ! empty($department->company_id)) {
                    return (int) $department->company_id;
                }
            } catch (\Throwable) {
                // Lewati jika pemanggilan relasi gagal
            }
        }

        // 5. Cek relasi 'user' jika tersedia
        if (method_exists($model, 'user')) {
            try {
                $user = $model->user;
                if ($user instanceof Model && ! empty($user->company_id)) {
                    return (int) $user->company_id;
                }
            } catch (\Throwable) {
                // Lewati jika pemanggilan relasi gagal
            }
        }

        // 6. Fallback ke company_id milik pengguna yang sedang login
        if (Auth::check() && ! empty(Auth::user()->company_id)) {
            return (int) Auth::user()->company_id;
        }

        return null;
    }

    /**
     * Check if a model attribute should be excluded from audit logging.
     */
    protected static function isIgnoredAuditAttribute(Model $model, string $attribute): bool
    {
        $sensitive = [
            'password',
            'remember_token',
            'two_factor_secret',
            'two_factor_recovery_codes',
            'two_factor_confirmed_at',
            'updated_at',
        ];

        $modelHidden = method_exists($model, 'getHidden') ? $model->getHidden() : [];
        $modelIgnored = property_exists($model, 'auditIgnore') ? (array) $model->auditIgnore : [];

        $allIgnored = array_unique(array_merge($sensitive, $modelHidden, $modelIgnored));

        return in_array($attribute, $allIgnored, true);
    }

    /**
     * Filter an attributes array to strip out sensitive and ignored fields.
     */
    protected static function filterAuditableAttributes(Model $model, array $attributes): array
    {
        $filtered = [];

        foreach ($attributes as $key => $value) {
            if (static::isIgnoredAuditAttribute($model, $key)) {
                continue;
            }
            $filtered[$key] = $value;
        }

        return $filtered;
    }
}
