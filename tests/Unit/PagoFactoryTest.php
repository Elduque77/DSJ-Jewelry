<?php

namespace Tests\Unit;

use App\Contracts\PagoInterface;
use App\Exceptions\PagoException;
use App\Services\Pagos\PagoConCheque;
use App\Services\Pagos\PagoConSaldo;
use App\Services\Pagos\PagoFactory;
use Tests\TestCase;

class PagoFactoryTest extends TestCase
{
    public function test_factory_entrega_la_clase_concreta_segun_el_metodo(): void
    {
        $factory = $this->app->make(PagoFactory::class);

        $this->assertInstanceOf(PagoConSaldo::class, $factory->crear('saldo'));
        $this->assertInstanceOf(PagoConCheque::class, $factory->crear('cheque'));
        $this->assertInstanceOf(PagoInterface::class, $factory->crear('cheque'));
    }

    public function test_factory_rechaza_un_metodo_inexistente(): void
    {
        $this->expectException(PagoException::class);

        $this->app->make(PagoFactory::class)->crear('bitcoin');
    }
}
