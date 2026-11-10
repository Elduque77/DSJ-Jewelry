<?php

namespace Tests\Unit;

use App\Contracts\GeneradorPdfInterface;
use App\Contracts\PagoFactoryInterface;
use App\Contracts\PagoInterface;
use App\Exceptions\PagoException;
use App\Services\Pagos\PagoConCheque;
use App\Services\Pagos\PagoConSaldo;
use App\Services\Pagos\PagoFactory;
use App\Services\Pdf\DompdfGenerador;
use stdClass;
use Tests\TestCase;

class PagoFactoryTest extends TestCase
{
    public function test_el_contenedor_resuelve_los_contratos_con_sus_implementaciones(): void
    {
        $this->assertInstanceOf(PagoFactory::class, $this->app->make(PagoFactoryInterface::class));
        $this->assertInstanceOf(DompdfGenerador::class, $this->app->make(GeneradorPdfInterface::class));
    }

    public function test_factory_entrega_la_clase_concreta_segun_el_metodo(): void
    {
        $factory = $this->app->make(PagoFactoryInterface::class);

        $this->assertInstanceOf(PagoConSaldo::class, $factory->crear('saldo'));
        $this->assertInstanceOf(PagoConCheque::class, $factory->crear('cheque'));
        $this->assertInstanceOf(PagoInterface::class, $factory->crear('cheque'));
    }

    public function test_factory_rechaza_un_metodo_inexistente(): void
    {
        $this->expectException(PagoException::class);

        $this->app->make(PagoFactoryInterface::class)->crear('bitcoin');
    }

    public function test_factory_rechaza_una_clase_que_no_implementa_el_contrato(): void
    {
        config(['pagos.metodos.roto' => stdClass::class]);

        $this->expectException(PagoException::class);

        $this->app->make(PagoFactoryInterface::class)->crear('roto');
    }
}
