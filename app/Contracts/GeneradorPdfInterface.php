<?php

/**
 * Autor: Juan Fernando Duque (Desarrollador)
 */

namespace App\Contracts;

/**
 * Contrato para convertir HTML en PDF. Los servicios que generan documentos
 * dependen de esta interfaz y no de una libreria concreta (Dompdf, Snappy...).
 */
interface GeneradorPdfInterface
{
    /**
     * @param  string  $orientacion  "portrait" o "landscape"
     * @return string contenido binario del PDF
     */
    public function generar(string $html, string $papel = 'A4', string $orientacion = 'portrait'): string;
}
