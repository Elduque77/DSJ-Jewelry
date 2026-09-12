{{-- Autor: Samuel Correa Velasquez (Desarrollador) --}}
@extends('layouts.app')
@section('content')
<h1>Joyería DSJ</h1>
<p>Descubre nuestros productos.</p>
<div class="grid">
    @forelse ($productos as $producto)
        <article class="card">
            <h2>{{ $producto->getNombre() }}</h2>
            <p>{{ $producto->getDescripcion() }}</p>
            <p><strong>Material:</strong> {{ $producto->getMaterial() }}</p>
            <p><strong>Precio:</strong> ${{ number_format($producto->getPrecio(), 2) }}</p>
            <p><strong>Disponibles:</strong> {{ $producto->getStock() }}</p>
        </article>
    @empty
        <p>No hay productos disponibles.</p>
    @endforelse
</div>
@endsection
