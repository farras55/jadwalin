<?php

namespace App\Providers;

use App\Models\AuditLog;
use App\Models\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());

        if ($this->app->environment('production') || str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        View::composer('*', function ($view) {
            if (! array_key_exists('timezoneAbbr', $view->getData())) {
                $user = auth()->user();
                $tz = null;
                if ($user && $user->relationLoaded('company')) {
                    $tz = $user->company?->timezone;
                }
                $tz = $tz ?: config('app.timezone', 'Asia/Jakarta');
                $view->with('timezoneAbbr', AuditLog::getTimezoneAbbreviation($tz));
            }
        });

        View::composer('layouts.topbar', function ($view) {
            $unreadNotifCount = 0;
            $latestNotifications = collect();

            if (auth()->check()) {
                $userId = auth()->id();

                $unreadNotifCount = Cache::remember("user_{$userId}_unread_notif_count", 30, function () use ($userId) {
                    return Notification::where('user_id', $userId)
                        ->where('is_read', false)
                        ->count();
                });

                $latestNotifications = Notification::where('user_id', $userId)
                    ->orderByDesc('created_at')
                    ->limit(5)
                    ->get();
            }

            $view->with([
                'unreadNotifCount' => $unreadNotifCount,
                'latestNotifications' => $latestNotifications,
            ]);
        });
    }
}
