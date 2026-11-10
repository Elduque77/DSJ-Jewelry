<?php

namespace App\Providers;

use App\Contracts\GeneradorPdfInterface;
use App\Contracts\PagoFactoryInterface;
use App\Services\Pagos\PagoFactory;
use App\Services\Pdf\DompdfGenerador;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Inversion de dependencias: aqui se decide que implementacion concreta
        // recibe quien pida cada contrato. Cambiar de libreria de PDF o de
        // estrategia para elegir el pago es cambiar solo estas lineas.
        $this->app->bind(PagoFactoryInterface::class, PagoFactory::class);
        $this->app->bind(GeneradorPdfInterface::class, DompdfGenerador::class);
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
