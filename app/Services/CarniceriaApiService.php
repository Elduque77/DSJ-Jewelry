<?php

/**
 * Autor: Diego (Arquitecto)
 *
 * Consume la API externa de "Carniceria Virtual" (productos en venta).
 * Guarda la respuesta en cache unos minutos y conserva una copia de respaldo
 * para seguir mostrando productos si el servidor externo no responde.
 */

namespace App\Services;

use App\Exceptions\CarniceriaApiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CarniceriaApiService
{
    private const CACHE_KEY = 'carniceria.productos';

    private const CACHE_RESPALDO = 'carniceria.productos.respaldo';

    /**
     * @return array{
     *     tienda: string,
     *     generado: ?string,
     *     productos: list<array{id: int, nombre: string, tipo_carne: string, categoria: string, precio: int, descuento_hoy: int, precio_hoy: int, enlace: ?string}>,
     *     desactualizado: bool
     * }
     */
    public function productosEnVenta(): array
    {
        $cacheados = Cache::get(self::CACHE_KEY);
        if (is_array($cacheados)) {
            return [...$cacheados, 'desactualizado' => false];
        }

        try {
            $datos = $this->consultar();
        } catch (CarniceriaApiException $exception) {
            $respaldo = Cache::get(self::CACHE_RESPALDO);
            if (is_array($respaldo)) {
                return [...$respaldo, 'desactualizado' => true];
            }

            throw $exception;
        }

        Cache::put(self::CACHE_KEY, $datos, now()->addMinutes((int) config('services.carniceria.cache_minutos', 5)));
        Cache::put(self::CACHE_RESPALDO, $datos, now()->addDay());

        return [...$datos, 'desactualizado' => false];
    }

    /**
     * @return array{tienda: string, generado: ?string, productos: list<array<string, mixed>>}
     */
    private function consultar(): array
    {
        $url = (string) config('services.carniceria.url');
        if ($url === '') {
            throw new CarniceriaApiException('La API de la carnicería no está configurada. Define CARNICERIA_API_URL en .env.');
        }

        try {
            $response = Http::connectTimeout(3)
                ->timeout(8)
                ->acceptJson()
                ->get($url)
                ->throw();
        } catch (ConnectionException|RequestException $exception) {
            throw new CarniceriaApiException('La carnicería aliada no está disponible en este momento. Inténtalo más tarde.', 0, $exception);
        }

        $productos = $response->json('productos');
        if (! is_array($productos)) {
            throw new CarniceriaApiException('La API de la carnicería devolvió una respuesta con un formato inesperado.');
        }

        $tienda = $response->json('tienda');
        $generado = $response->json('generado');

        return [
            'tienda' => is_string($tienda) && $tienda !== '' ? $tienda : 'Carnicería aliada',
            'generado' => is_string($generado) ? $generado : null,
            'productos' => array_values(array_filter(array_map($this->normalizar(...), $productos))),
        ];
    }

    /**
     * @return array{id: int, nombre: string, tipo_carne: string, categoria: string, precio: int, descuento_hoy: int, precio_hoy: int, enlace: ?string}|null
     */
    private function normalizar(mixed $producto): ?array
    {
        if (! is_array($producto) || ! is_string($producto['nombre'] ?? null) || trim($producto['nombre']) === '') {
            return null;
        }

        $precio = (int) ($producto['precio'] ?? 0);
        $descuento = max(0, min(100, (int) ($producto['descuento_hoy'] ?? 0)));
        $enlace = $producto['enlace'] ?? null;

        return [
            'id' => (int) ($producto['id'] ?? 0),
            'nombre' => trim($producto['nombre']),
            'tipo_carne' => is_string($producto['tipo_carne'] ?? null) ? $producto['tipo_carne'] : 'Otro',
            'categoria' => is_string($producto['categoria'] ?? null) ? $producto['categoria'] : 'General',
            'precio' => $precio,
            'descuento_hoy' => $descuento,
            'precio_hoy' => isset($producto['precio_hoy']) ? (int) $producto['precio_hoy'] : $precio,
            // Solo se aceptan enlaces http(s) para no inyectar esquemas como javascript: en el href.
            'enlace' => is_string($enlace) && preg_match('#^https?://#i', $enlace) === 1 ? $enlace : null,
        ];
    }
}
