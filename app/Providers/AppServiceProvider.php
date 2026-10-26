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

            if (auth()->check()) {
                $unreadNotifCount = Notification::where('user_id', auth()->id())
                    ->where('is_read', false)
                    ->count();
            }

            $view->with('unreadNotifCount', $unreadNotifCount);
        });
    }
}