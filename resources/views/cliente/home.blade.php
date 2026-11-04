{{-- Autor: Samuel Correa Velasquez (Desarrollador) --}}
{{-- Autor: Juan Fernando Duque (Desarrollador) - busqueda por nombre --}}
@extends('layouts.app')
@section('content')
<div class="home-page">
    <section class="hero">
        <div class="hero-copy">
            <span class="eyebrow">{{ __('messages.hero_eyebrow') }}</span>
            <h1>{{ __('messages.hero_title') }}</h1>
            <p>{{ __('messages.hero_text') }}</p>
            <a class="hero-button" href="#coleccion">{{ __('messages.featured') }}</a>
        </div>
        <div class="hero-jewel" aria-hidden="true"><span>✦</span></div>
    </section>

    <section class="collection-heading" id="coleccion">
        <div>
            <span class="eyebrow">{{ __('messages.selection') }}</span>
            <h2>{{ __('messages.featured_title') }}</h2>
        </div>
        <p>{{ __('messages.selection_text') }}</p>
    </section>

<section class="card">
    <h2>{{ __('messages.help_title') }}</h2>
    <p>{{ __('messages.help_text') }}</p>
    <form method="POST" action="{{ route('recomendaciones') }}">
        @csrf
        <label for="necesidad">{{ __('messages.need_label') }}</label>
        <textarea id="necesidad" name="necesidad" rows="3" maxlength="300"
            placeholder="{{ __('messages.need_placeholder') }}">{{ old('necesidad', $necesidad) }}</textarea>
        <button type="submit">{{ __('messages.recommend') }}</button>
    </form>

    @if ($errorRecomendacion)
        <p class="error">{{ $errorRecomendacion }}</p>
    @elseif ($resultadoRecomendacion !== null)
        <h3>{{ __('messages.recommendations') }}</h3>
        <p>{{ $resultadoRecomendacion['explicacion'] }}</p>
        <div class="grid">
            @foreach ($resultadoRecomendacion['productos'] as $producto)
                <article class="card" id="recomendacion-producto-{{ $producto->getIdProducto() }}">
                    <img class="product-image" src="{{ asset('storage/'.$producto->getImagen()) }}" alt="{{ $producto->getNombre() }}">
                    <h3><a href="{{ route('producto.show', $producto) }}">{{ $producto->getNombre() }}</a></h3>
                    <p>{{ $producto->getDescripcion() }}</p>
                    <p><strong>{{ __('messages.material') }}:</strong> {{ $producto->getMaterial() }}</p>
                    <p><strong>{{ __('messages.price') }}:</strong> ${{ number_format($producto->getPrecio(), 2) }}</p>
                    <p><strong>{{ __('messages.available') }}:</strong> {{ $producto->getStock() }}</p>
                    @if ($producto->consultarDisponibilidad())
                        <form method="POST" action="{{ route('carrito.agregar', $producto->getIdProducto()) }}" style="background:none;padding:0;margin:0;border:none">
                            @csrf
                            <input type="hidden" name="ancla" value="recomendacion-producto-{{ $producto->getIdProducto() }}">
                            <button type="submit">{{ __('messages.add_to_cart') }}</button>
                        </form>
                    @endif
                </article>
            @endforeach
        </div>
    @endif
</section>

<form id="catalog-search" method="GET" action="{{ route('home') }}" style="display:flex;gap:.5rem;align-items:flex-end">
    <label style="flex:1;margin-top:0">{{ __('messages.search_label') }}
        <input id="catalog-search-input" type="search" name="buscar" placeholder="{{ __('messages.search_placeholder') }}" value="{{ $busqueda }}" autocomplete="off">
    </label>
</form>

<div class="grid">
    @forelse ($productos as $producto)
        <article class="card catalog-product-card" id="producto-{{ $producto->getIdProducto() }}" data-product-name="{{ $producto->getNombre() }}">
            @if ($producto->getImagen())
                <img class="product-image" src="{{ asset('storage/'.$producto->getImagen()) }}" alt="{{ $producto->getNombre() }}">
            @else
                <div class="product-image product-image-placeholder" aria-hidden="true">✦</div>
            @endif
            <h2><a href="{{ route('producto.show', $producto) }}">{{ $producto->getNombre() }}</a></h2>
            <p>{{ $producto->getDescripcion() }}</p>
            <p><strong>{{ __('messages.material') }}:</strong> {{ $producto->getMaterial() }}</p>
            <p><strong>{{ __('messages.price') }}:</strong> ${{ number_format($producto->getPrecio(), 2) }}</p>
            <p><strong>{{ __('messages.available') }}:</strong> {{ $producto->getStock() }}</p>
            @if ($producto->consultarDisponibilidad())
                <form method="POST" action="{{ route('carrito.agregar', $producto->getIdProducto()) }}" style="background:none;padding:0;margin:0;border:none">
                    @csrf
                    <input type="hidden" name="ancla" value="producto-{{ $producto->getIdProducto() }}">
                    <button type="submit">{{ __('messages.add_to_cart') }}</button>
                </form>
            @else
                <p><em>{{ __('messages.out_of_stock') }}</em></p>
            @endif
        </article>
    @empty
        @if ($busqueda !== '')
            <p>{{ __('messages.no_match', ['search' => $busqueda]) }}</p>
        @else
            <p>{{ __('messages.no_products') }}</p>
        @endif
    @endforelse
</div>
@if ($productos->isNotEmpty())
    <p id="catalog-no-results" data-no-results-template="{{ __('messages.no_match') }}" hidden></p>
@endif
</div>
@endsection
