<?php

/**
 * Autor: Samuel Correa Velasquez (Desarrollador)
 */

namespace App\Services;

use App\Exceptions\RecomendacionProductoException;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class RecomendacionProductoService
{
    /**
     * @return array{productos: Collection<int, Producto>, explicacion: string}
     */
    public function recomendar(string $necesidad, int $limite = 3): array
    {
        $apiKey = (string) config('services.gemini.api_key');
        if ($apiKey === '') {
            throw new RecomendacionProductoException(__('messages.recommendation_api_missing'));
        }

        $catalogo = Producto::with('categoria')
            ->where('stock', '>', 0)
            ->get();

        if ($catalogo->isEmpty()) {
            throw new RecomendacionProductoException(__('messages.recommendation_no_products'));
        }

        $texto = $this->consultarGemini($apiKey, $necesidad, $catalogo, $limite);
        $resultado = $this->decodificarRespuesta($texto, $limite);
        $productos = $catalogo->whereIn('idProducto', $resultado['ids'])->values();

        if ($productos->isEmpty()) {
            throw new RecomendacionProductoException(__('messages.recommendation_invalid_products'));
        }

        return [
            'productos' => $productos,
            'explicacion' => $resultado['explicacion'],
        ];
    }

    private function consultarGemini(string $apiKey, string $necesidad, Collection $catalogo, int $limite): string
    {
        $productos = $catalogo->map(fn (Producto $producto): array => [
            'id' => $producto->getIdProducto(),
            'nombre' => $producto->getNombre(),
            'descripcion' => $producto->getDescripcion(),
            'material' => $producto->getMaterial(),
            'precio' => $producto->getPrecio(),
            'categoria' => $producto->getCategoria()->getNombre(),
        ])->values()->all();

        $prompt = sprintf(
            'Eres el asesor de compras de DSJ Jewelry. Tu objetivo es ayudar a que la persona elija con ilusión y seguridad, sin exagerar ni prometer que le gustará con certeza. Analiza su necesidad, el contexto del regalo y sus preferencias. Recomienda como máximo %d productos únicamente del catálogo recibido. Responde SOLO JSON válido con esta estructura: {"producto_ids":[1,2],"explicacion":"..."}. La explicación debe ser breve (máximo 2 frases), cálida, natural y convincente, en idioma %s. Habla de por qué el diseño, material o estilo encaja con lo que busca y qué sensación puede transmitir al regalarlo. No menciones IDs, stock, catálogo, precios, inteligencia artificial ni aspectos técnicos. Necesidad de la persona: %s. Catálogo: %s',
            $limite,
            app()->getLocale() === 'en' ? 'inglés' : 'español',
            $necesidad,
            json_encode($productos, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        );

        try {
            $response = Http::connectTimeout(3)
                ->timeout(12)
                ->retry(1, 250, fn ($exception): bool => $exception instanceof ConnectionException)
                ->withHeaders([
                    'x-goog-api-key' => $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post('https://generativelanguage.googleapis.com/v1beta/models/'.config('services.gemini.model').':generateContent', [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => [
                        'temperature' => 0.2,
                        'maxOutputTokens' => 256,
                        'responseMimeType' => 'application/json',
                        'thinkingConfig' => [
                            'thinkingBudget' => 0,
                        ],
                    ],
                ])
                ->throw();
        } catch (ConnectionException|RequestException $exception) {
            $status = $exception instanceof RequestException
                ? $exception->response->status()
                : null;

            $mensaje = match ($status) {
                401, 403 => __('messages.recommendation_unauthorized'),
                429 => __('messages.recommendation_quota'),
                default => __('messages.recommendation_unavailable'),
            };

            throw new RecomendacionProductoException($mensaje, 0, $exception);
        }

        $texto = $response->json('candidates.0.content.parts.0.text');
        if (! is_string($texto) || trim($texto) === '') {
            throw new RecomendacionProductoException(__('messages.recommendation_empty'));
        }

        return $texto;
    }

    /**
     * @return array{ids: list<int>, explicacion: string}
     */
    private function decodificarRespuesta(string $respuesta, int $limite): array
    {
        $datos = json_decode(trim($respuesta), true);
        if (! is_array($datos) || ! isset($datos['producto_ids']) || ! is_array($datos['producto_ids'])) {
            throw new RecomendacionProductoException(__('messages.recommendation_invalid_format'));
        }

        $ids = array_values(array_unique(array_filter($datos['producto_ids'], 'is_int')));
        if ($ids === []) {
            throw new RecomendacionProductoException(__('messages.recommendation_no_match'));
        }

        return [
            'ids' => array_slice($ids, 0, $limite),
            'explicacion' => is_string($datos['explicacion'] ?? null)
                ? $datos['explicacion']
                : __('messages.recommendation_default'),
        ];
    }
}
