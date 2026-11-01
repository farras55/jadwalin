<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Menampilkan daftar notifikasi user yang sedang login.
     */
    public function index(): View
    {
        $user = auth()->user();

        $notifications = Notification::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        $unreadNotifCount = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return view('notifications.index', [
            'notifications' => $notifications,
            'unreadNotifCount' => $unreadNotifCount,
        ]);
    }

    /**
     * Menandai satu notifikasi sebagai sudah dibaca.
     */
    public function markAsRead(Notification $notification): RedirectResponse
    {
        $user = auth()->user();

        abort_unless($notification->user_id === $user->id, 403);

        $notification->update([
            'is_read' => true,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Notifikasi ditandai sudah dibaca.');
    }

    /**
     * Menandai semua notifikasi user sebagai sudah dibaca.
     */
    public function markAllAsRead(): RedirectResponse
    {
        $user = auth()->user();

        Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
            ]);

        return redirect()
            ->back()
            ->with('success', 'Semua notifikasi sudah dibaca.');
    }
}
