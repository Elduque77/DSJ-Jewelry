{{-- Autor: Samuel Correa Velasquez (Desarrollador) --}}
{{-- Autor: Juan Fernando Duque (Desarrollador) - busqueda por nombre --}}
@extends('layouts.app')
@section('content')
<h1>Joyería DSJ</h1>
<p>Descubre nuestros productos.</p>

<section class="card">
    <h2>¿Necesitas ayuda para elegir?</h2>
    <p>Pregúntale a nuestro asistente inteligente y te recomendará productos según tus necesidades.</p>
    <form method="POST" action="{{ route('recomendaciones') }}">
        @csrf
        <label for="necesidad">Cuéntanos qué estás buscando</label>
        <textarea id="necesidad" name="necesidad" rows="3" maxlength="300"
            placeholder="Ej: Busco un anillo elegante de plata para regalar">{{ old('necesidad', $necesidad) }}</textarea>
        <button type="submit">Recomendarme productos</button>
    </form>

    @if ($errorRecomendacion)
        <p class="error">{{ $errorRecomendacion }}</p>
    @elseif ($resultadoRecomendacion !== null)
        <h3>Recomendaciones para ti</h3>
        <p>{{ $resultadoRecomendacion['explicacion'] }}</p>
        <div class="grid">
            @foreach ($resultadoRecomendacion['productos'] as $producto)
                <article class="card">
                    <h3><a href="{{ route('producto.show', $producto) }}">{{ $producto->getNombre() }}</a></h3>
                    <p>{{ $producto->getDescripcion() }}</p>
                    <p><strong>Material:</strong> {{ $producto->getMaterial() }}</p>
                    <p><strong>Precio:</strong> ${{ number_format($producto->getPrecio(), 2) }}</p>
                    <a class="button" href="{{ route('producto.show', $producto) }}">Ver producto</a>
                </article>
            @endforeach
        </div>
    @endif
</section>

<form method="GET" action="{{ route('home') }}" style="display:flex;gap:.5rem;align-items:flex-end">
    <label style="flex:1;margin-top:0">Buscar por nombre
        <input type="text" name="buscar" placeholder="Ej: anillo, cadena..." value="{{ $busqueda }}">
    </label>
    <button type="submit" style="margin-top:.3rem">Buscar</button>
    @if ($busqueda !== '')
        <a class="button" style="margin-top:.3rem" href="{{ route('home') }}">Limpiar</a>
    @endif
</form>

<div class="grid">
    @forelse ($productos as $producto)
        <article class="card">
            <h2><a href="{{ route('producto.show', $producto) }}">{{ $producto->getNombre() }}</a></h2>
            <p>{{ $producto->getDescripcion() }}</p>
            <p><strong>Material:</strong> {{ $producto->getMaterial() }}</p>
            <p><strong>Precio:</strong> ${{ number_format($producto->getPrecio(), 2) }}</p>
            <p><strong>Disponibles:</strong> {{ $producto->getStock() }}</p>
            @if ($producto->consultarDisponibilidad())
                <form method="POST" action="{{ route('carrito.agregar', $producto->getIdProducto()) }}">
                    @csrf
                    <button type="submit">Agregar al carrito</button>
                </form>
            @else
                <p><em>Sin stock disponible</em></p>
            @endif
        </article>
    @empty
        @if ($busqueda !== '')
            <p>No encontramos productos que coincidan con "{{ $busqueda }}".</p>
        @else
            <p>No hay productos disponibles.</p>
        @endif
    @endforelse
</div>
@endsection
