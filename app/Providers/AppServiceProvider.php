<?php

namespace App\Providers;

use App\Contracts\PagoInterface;
use App\Services\Pagos\PagoConSaldo;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Inversion de dependencias: quien pida PagoInterface sin elegir metodo
        // recibe PagoConSaldo. Los metodos elegibles viven en config/pagos.php.
        $this->app->bind(PagoInterface::class, PagoConSaldo::class);
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
