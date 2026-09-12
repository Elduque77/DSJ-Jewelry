{{-- Autor: Diego (Arquitecto) --}}
@extends('layouts.app')
@section('content')
<article class="card">
    <h1>{{ $producto->getNombre() }}</h1>
    <p><strong>Categoría:</strong> {{ $producto->getCategoria()->getNombre() }}</p>
    <p>{{ $producto->getDescripcion() }}</p>
    <p><strong>Material:</strong> {{ $producto->getMaterial() }}</p>
    <p><strong>Precio:</strong> ${{ number_format($producto->getPrecio(), 2) }}</p>
    <p><strong>Disponibles:</strong> {{ $producto->getStock() }}</p>
    <a href="{{ route('home') }}">Volver al catálogo</a>
</article>

<h2>Reseñas</h2>
@if ($producto->getTotalResenas() > 0)
    <p>
        {{ str_repeat('★', (int) round($producto->getPromedioCalificacion())) }}{{ str_repeat('☆', 5 - (int) round($producto->getPromedioCalificacion())) }}
        {{ number_format($producto->getPromedioCalificacion(), 1) }} / 5
        ({{ $producto->getTotalResenas() }} {{ $producto->getTotalResenas() === 1 ? 'reseña' : 'reseñas' }})
    </p>
@endif

@include('cliente.resena.lista', ['resenas' => $producto->getResenas()])

@auth('cliente')
    @include('cliente.resena.form', ['producto' => $producto, 'resena' => $resenaPropia])
@else
    <p class="card"><a href="{{ route('cliente.login') }}">Inicia sesión</a> para dejar tu reseña.</p>
@endauth
@endsection
