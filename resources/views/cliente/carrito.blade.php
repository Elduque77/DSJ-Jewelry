{{-- Autor: Juan Fernando Duque (Desarrollador) --}}
@extends('layouts.app')
@section('content')
@if ($lineas->isEmpty())
    <section class="empty-cart" aria-labelledby="empty-cart-title">
        <svg class="empty-cart-icon" viewBox="0 0 85 85" role="img" aria-label="{{ __('messages.cart_title') }}">
            <path d="M12 14h10l8 39h40l9-29H25" />
            <circle cx="37" cy="70" r="6" />
            <circle cx="67" cy="70" r="6" />
        </svg>
        <h1 id="empty-cart-title">{{ __('messages.cart_title') }}</h1>
        <p>{{ __('messages.empty_cart') }}</p>
        <p class="empty-cart-total"><strong>{{ __('messages.total') }}: $0.00</strong></p>
        <a class="button empty-cart-button" href="{{ route('home') }}">{{ __('messages.view_products') }}</a>
    </section>
@else
    <h1>{{ __('messages.cart_title') }}</h1>
    <table>
        <thead>
            <tr>
                <th>{{ __('messages.product') }}</th>
                <th>{{ __('messages.price') }}</th>
                <th>{{ __('messages.quantity') }}</th>
                <th>{{ __('messages.subtotal') }}</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($lineas as $linea)
                <tr data-cart-row data-cart-price="{{ $linea['producto']->getPrecio() }}">
                    <td>{{ $linea['producto']->getNombre() }}</td>
                    <td>${{ number_format($linea['producto']->getPrecio(), 2) }}</td>
                    <td>
                        <form data-cart-update-form method="POST" action="{{ route('carrito.actualizar', $linea['producto']->getIdProducto()) }}" style="display:flex;gap:.5rem;background:none;padding:0;margin:0;border:none">
                            @csrf @method('PATCH')
                            <input data-cart-quantity type="number" name="cantidad" value="{{ $linea['cantidad'] }}" min="1" max="{{ $linea['producto']->getStock() }}" style="width:70px">
                        </form>
                    </td>
                    <td data-cart-subtotal>${{ number_format($linea['subtotal'], 2) }}</td>
                    <td>
                        <form method="POST" action="{{ route('carrito.eliminar', $linea['producto']->getIdProducto()) }}" style="background:none;padding:0;margin:0;border:none">
                            @csrf @method('DELETE')
                            <button type="submit" class="danger">{{ __('messages.delete') }}</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p><strong>{{ __('messages.total') }}: <span data-cart-total>${{ number_format($total, 2) }}</span></strong></p>

    @auth('cliente')
        <form method="POST" action="{{ route('carrito.confirmar') }}">
            @csrf
            <button type="submit">{{ __('messages.confirm_purchase') }}</button>
        </form>
    @else
        <p>
            <a class="button" href="{{ route('cliente.login') }}">{{ __('messages.login_to_confirm') }}</a>
        </p>
    @endauth
@endif
@endsection
