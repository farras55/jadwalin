<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * Kirim notifikasi in-app ke pengguna.
     */
    public static function send(int|User $user, string $type, string $title, string $message, ?int $companyId = null): Notification
    {
        $userId = $user instanceof User ? $user->id : $user;
        $companyId = $companyId ?? ($user instanceof User ? $user->company_id : User::find($userId)?->company_id);

        return Notification::create([
            'company_id' => $companyId,
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'is_read' => false,
        ]);
    }
}
