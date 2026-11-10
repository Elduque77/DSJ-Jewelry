<?php

/**
 * Autor: Juan Fernando Duque (Desarrollador)
 */

namespace App\Services\Pdf;

use App\Contracts\GeneradorPdfInterface;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Implementacion de GeneradorPdfInterface con Dompdf. Es el unico lugar del
 * proyecto que conoce la libreria.
 */
class DompdfGenerador implements GeneradorPdfInterface
{
    public function generar(string $html, string $papel = 'A4', string $orientacion = 'portrait'): string
    {
        $opciones = new Options;
        $opciones->set('defaultFont', 'DejaVu Sans');

        $pdf = new Dompdf($opciones);
        $pdf->loadHtml($html);
        $pdf->setPaper($papel, $orientacion);
        $pdf->render();

        return $pdf->output();
    }
}
