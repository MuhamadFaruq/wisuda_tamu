<?php

namespace App\Providers;

use App\Models\EventSetting;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

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
        View::composer('*', function ($view) {
            $agenda = Schema::hasTable('event_settings')
                ? EventSetting::where('is_active', true)->first()
                : null;
            $view->with('activeAgenda', $agenda);
        });
    }
}
