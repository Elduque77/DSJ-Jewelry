{{-- Autor: Diego (Arquitecto) --}}
@extends('layouts.app')
@section('content')
<article class="card">
    @if ($producto->getImagen())
        <img class="product-detail-image" src="{{ asset('storage/'.$producto->getImagen()) }}" alt="{{ $producto->getNombre() }}">
    @endif
    <h1>{{ $producto->getNombre() }}</h1>
    <p><strong>{{ __('messages.category') }}:</strong> {{ $producto->getCategoria()->getNombre() }}</p>
    <p>{{ $producto->getDescripcion() }}</p>
    <p><strong>{{ __('messages.material') }}:</strong> {{ $producto->getMaterial() }}</p>
    <p><strong>{{ __('messages.price') }}:</strong> ${{ number_format($producto->getPrecio(), 2) }}</p>
    <p><strong>{{ __('messages.available') }}:</strong> {{ $producto->getStock() }}</p>

    
    <a href="{{ route('home') }}">{{ __('messages.back_to_catalog') }}</a>
</article>

<h2>{{ __('messages.reviews') }}</h2>
@if ($producto->getTotalResenas() > 0)
    <p>
        {{ str_repeat('★', (int) round($producto->getPromedioCalificacion())) }}{{ str_repeat('☆', 5 - (int) round($producto->getPromedioCalificacion())) }}
        {{ number_format($producto->getPromedioCalificacion(), 1) }} / 5
        ({{ $producto->getTotalResenas() }} {{ $producto->getTotalResenas() === 1 ? __('messages.review') : __('messages.reviews_plural') }})
    </p>
@endif

@include('cliente.resena.lista', ['resenas' => $producto->getResenas()])

@auth('cliente')
    @include('cliente.resena.form', ['producto' => $producto, 'resena' => $resenaPropia])
@else
    <p class="card"><a href="{{ route('cliente.login') }}">{{ __('messages.login_to_review') }}</a></p>
@endauth
@endsection
