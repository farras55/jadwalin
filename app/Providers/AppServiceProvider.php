<?php

namespace App\Providers;

use App\Models\Notification;
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
        if ($this->app->environment('production') || str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        View::composer('layouts.topbar', function ($view) {
            $unreadNotifCount = 0;
            $latestNotifications = collect();

            if (auth()->check()) {
                $userId = auth()->id();

                $unreadNotifCount = Notification::where('user_id', $userId)
                    ->where('is_read', false)
                    ->count();

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
