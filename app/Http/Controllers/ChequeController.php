<?php

/**
 * Autor: Juan Fernando Duque (Desarrollador)
 */

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Services\Pagos\ChequePdfService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ChequeController extends Controller
{
    public function mostrar(Compra $compra, ChequePdfService $cheques): Response
    {
        // Cada cliente solo puede ver el cheque de sus propias compras.
        abort_unless($compra->getIdCliente() === Auth::guard('cliente')->id(), 403);

        return response($cheques->generar($compra), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="cheque-'.$compra->getIdCompra().'.pdf"',
        ]);
    }
}
