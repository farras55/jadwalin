<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * Membuat notifikasi untuk user tertentu.
     */
    public static function send(
        int $userId,
        string $type,
        string $title,
        string $message
    ): Notification {
        $user = User::findOrFail($userId);

        return Notification::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'is_read' => false,
        ]);
    }
}