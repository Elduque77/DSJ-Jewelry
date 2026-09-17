{{-- Autor: Juan Fernando Duque (Desarrollador) --}}
@extends('layouts.app')
@section('content')
<h1>Tu carrito</h1>

@if ($lineas->isEmpty())
    <p>Tu carrito esta vacio. <a href="{{ route('home') }}">Ver productos</a>.</p>
@else
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Precio</th>
                <th>Cantidad</th>
                <th>Subtotal</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($lineas as $linea)
                <tr>
                    <td>{{ $linea['producto']->getNombre() }}</td>
                    <td>${{ number_format($linea['producto']->getPrecio(), 2) }}</td>
                    <td>
                        <form method="POST" action="{{ route('carrito.actualizar', $linea['producto']->getIdProducto()) }}" style="display:flex;gap:.5rem;background:none;padding:0;margin:0;border:none">
                            @csrf @method('PATCH')
                            <input type="number" name="cantidad" value="{{ $linea['cantidad'] }}" min="1" max="{{ $linea['producto']->getStock() }}" style="width:70px">
                            <button type="submit">Actualizar</button>
                        </form>
                    </td>
                    <td>${{ number_format($linea['subtotal'], 2) }}</td>
                    <td>
                        <form method="POST" action="{{ route('carrito.eliminar', $linea['producto']->getIdProducto()) }}" style="background:none;padding:0;margin:0;border:none">
                            @csrf @method('DELETE')
                            <button type="submit" class="danger">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p><strong>Total: ${{ number_format($total, 2) }}</strong></p>

    @auth('cliente')
        <form method="POST" action="{{ route('carrito.confirmar') }}">
            @csrf
            <button type="submit">Confirmar compra</button>
        </form>
    @else
        <p>
            <a class="button" href="{{ route('cliente.login') }}">Inicia sesión para confirmar tu compra</a>
        </p>
    @endauth
@endif
@endsection
