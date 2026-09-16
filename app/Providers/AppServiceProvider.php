<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
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
        // Las vistas de paginado que trae Laravel asumen Tailwind o Bootstrap.
        // El proyecto no usa ninguno, asi que se registra la propia.
        Paginator::defaultView('pagination.dsj');
    }
}
