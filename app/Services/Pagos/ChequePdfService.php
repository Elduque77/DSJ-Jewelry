<?php

/**
 * Autor: Juan Fernando Duque (Desarrollador)
 */

namespace App\Services\Pagos;

use App\Models\Compra;
use Dompdf\Dompdf;
use Dompdf\Options;

class ChequePdfService
{
    public function generar(Compra $compra): string
    {
        $opciones = new Options;
        $opciones->set('defaultFont', 'DejaVu Sans');

        $pdf = new Dompdf($opciones);
        $pdf->loadHtml(view('cliente.cheque', [
            'compra' => $compra,
            'cliente' => $compra->getCliente(),
            'numero' => 'CHQ-'.str_pad((string) $compra->getIdCompra(), 6, '0', STR_PAD_LEFT),
        ])->render());
        $pdf->setPaper('A4', 'landscape');
        $pdf->render();

        return $pdf->output();
    }
}
