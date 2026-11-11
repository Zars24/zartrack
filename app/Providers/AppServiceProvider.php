<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        View::composer(['layouts.admin', 'layouts.portal'], function ($view) {
            $view->with('notifCount', auth()->user()?->unreadNotifications()->count() ?? 0);
        });
    }
}
