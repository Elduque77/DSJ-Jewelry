<?php

/**
 * Autor: Juan Fernando Duque (Desarrollador)
 */

namespace App\Services\Pagos;

use App\Contracts\GeneradorPdfInterface;
use App\Models\Compra;

/**
 * Arma el cheque de una compra. Decide que datos lleva y como se ve; la
 * conversion a PDF la delega en GeneradorPdfInterface.
 */
class ChequePdfService
{
    public function __construct(private readonly GeneradorPdfInterface $pdf) {}

    public function generar(Compra $compra): string
    {
        $html = view('cliente.cheque', [
            'compra' => $compra,
            'cliente' => $compra->getCliente(),
            'numero' => 'CHQ-'.str_pad((string) $compra->getIdCompra(), 6, '0', STR_PAD_LEFT),
        ])->render();

        return $this->pdf->generar($html, 'A4', 'landscape');
    }
}
