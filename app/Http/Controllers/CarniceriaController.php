<?php

/**
 * Autor: Diego (Arquitecto)
 */

namespace App\Http\Controllers;

use App\Exceptions\CarniceriaApiException;
use App\Services\CarniceriaApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class CarniceriaController extends Controller
{
    public function index(Request $request, CarniceriaApiService $carniceria): View
    {
        $tipo = trim((string) $request->query('tipo', ''));
        $soloOfertas = $request->boolean('ofertas');

        try {
            $datos = $carniceria->productosEnVenta();
        } catch (CarniceriaApiException $exception) {
            return view('cliente.carniceria.index', [
                'error' => $exception->getMessage(),
                'tienda' => 'Carnicería aliada',
                'generado' => null,
                'desactualizado' => false,
                'productos' => [],
                'tipos' => [],
                'tipo' => $tipo,
                'soloOfertas' => $soloOfertas,
                'totalOfertas' => 0,
            ]);
        }

        $todos = collect($datos['productos']);

        $productos = $todos
            ->when($tipo !== '', fn ($c) => $c->where('tipo_carne', $tipo))
            ->when($soloOfertas, fn ($c) => $c->where('descuento_hoy', '>', 0))
            ->sortBy('nombre')
            ->values()
            ->all();

        return view('cliente.carniceria.index', [
            'error' => null,
            'tienda' => $datos['tienda'],
            'generado' => $this->fecha($datos['generado']),
            'desactualizado' => $datos['desactualizado'],
            'productos' => $productos,
            'tipos' => $todos->countBy('tipo_carne')->sortKeys()->all(),
            'tipo' => $tipo,
            'soloOfertas' => $soloOfertas,
            'totalOfertas' => $todos->where('descuento_hoy', '>', 0)->count(),
        ]);
    }

    private function fecha(?string $valor): ?Carbon
    {
        if ($valor === null) {
            return null;
        }

        try {
            return Carbon::parse($valor)->timezone(config('app.timezone'));
        } catch (\Throwable) {
            return null;
        }
    }
}
