{{-- Autor: Juan Fernando Duque (Desarrollador) --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('messages.cheque_title') }} {{ $numero }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; color: #222; }
        .cheque { border: 2px solid #8a6d3b; padding: 28px 34px; }
        .fila { width: 100%; margin-bottom: 18px; }
        .fila td { vertical-align: top; }
        h1 { margin: 0; font-size: 22px; color: #8a6d3b; }
        .etiqueta { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #777; }
        .valor { font-size: 15px; border-bottom: 1px solid #999; padding-bottom: 4px; }
        .monto { font-size: 22px; font-weight: bold; text-align: right; }
        .nota { margin-top: 24px; font-size: 11px; color: #555; }
    </style>
</head>
<body>
    <div class="cheque">
        <table class="fila">
            <tr>
                <td><h1>DSJ Jewelry</h1><div class="etiqueta">{{ __('messages.cheque_title') }}</div></td>
                <td style="text-align:right">
                    <div class="etiqueta">{{ __('messages.cheque_number') }}</div>
                    <div class="valor">{{ $numero }}</div>
                </td>
            </tr>
        </table>

        <table class="fila">
            <tr>
                <td style="width:65%">
                    <div class="etiqueta">{{ __('messages.cheque_pay_to') }}</div>
                    <div class="valor">DSJ Jewelry</div>
                </td>
                <td style="width:35%">
                    <div class="etiqueta">{{ __('messages.cheque_amount') }}</div>
                    <div class="valor monto">${{ number_format($compra->getTotal(), 2) }}</div>
                </td>
            </tr>
        </table>

        <table class="fila">
            <tr>
                <td style="width:65%">
                    <div class="etiqueta">{{ __('messages.cheque_payer') }}</div>
                    <div class="valor">{{ $cliente->getNombreCompleto() }}</div>
                </td>
                <td style="width:35%">
                    <div class="etiqueta">{{ __('messages.cheque_date') }}</div>
                    <div class="valor">{{ $compra->getFecha() }}</div>
                </td>
            </tr>
        </table>

        <p class="nota">{{ __('messages.cheque_note', ['purchase' => $compra->getIdCompra()]) }}</p>
    </div>
</body>
</html>
