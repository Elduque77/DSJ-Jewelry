{{-- Autor: Diego (Arquitecto) - productos consumidos desde la API de la carniceria aliada --}}
@extends('layouts.app', ['title' => 'Carnicería aliada · DSJ Jewelry'])
@section('content')
<style>
    .carniceria-page .filtros { display: flex; flex-wrap: wrap; gap: .5rem; margin-bottom: 1.5rem; }
    .carniceria-page .chip { padding: .45rem .9rem; border: 1px solid var(--line); color: var(--text); text-decoration: none; font: 600 .72rem Arial, sans-serif; letter-spacing: .1em; text-transform: uppercase; }
    .carniceria-page .chip:hover { border-color: var(--accent); }
    .carniceria-page .chip.activo { background: var(--accent); border-color: var(--accent); color: var(--button-text); }
    .carniceria-page .chip small { opacity: .7; margin-left: .3rem; }
    .carniceria-page .producto { position: relative; display: flex; flex-direction: column; }
    .carniceria-page .producto h3 { margin: .6rem 0 .3rem; font-size: 1.35rem; }
    .carniceria-page .producto .tipo { margin: 0; font-size: .9rem; }
    .carniceria-page .precios { margin-top: auto; padding-top: 1rem; }
    .carniceria-page .precio-hoy { color: var(--accent); font-size: 1.5rem; }
    .carniceria-page .precio-antes { color: color-mix(in srgb, var(--text) 50%, transparent); text-decoration: line-through; margin-left: .5rem; }
    .carniceria-page .badge { position: absolute; top: 1rem; right: 1rem; background: var(--accent-soft); color: var(--text); padding: .3rem .55rem; font: 700 .7rem Arial, sans-serif; letter-spacing: .08em; }
    .carniceria-page .producto .button { align-self: flex-start; color: var(--button-text); }
    .carniceria-page .fuente { margin-top: 2rem; font-size: .85rem; }
    .carniceria-page .aviso { border-color: var(--accent); }
</style>

<div class="home-page carniceria-page">
    <section class="collection-heading">
        <div>
            <span class="eyebrow">Tienda aliada</span>
            <h2>{{ $tienda }}</h2>
        </div>
        <p>Productos en venta hoy, consultados en tiempo real desde la API de nuestra carnicería aliada.</p>
    </section>

    @if ($error)
        <section class="card aviso">
            <h3>No pudimos cargar los productos</h3>
            <p>{{ $error }}</p>
            <a class="button" href="{{ route('carniceria.index') }}">Reintentar</a>
        </section>
    @else
        @if ($desactualizado)
            <section class="card aviso">
                <p>La carnicería no responde en este momento. Mostramos la última información disponible.</p>
            </section>
        @endif

        <nav class="filtros" aria-label="Filtrar por tipo de carne">
            <a class="chip {{ $tipo === '' && ! $soloOfertas ? 'activo' : '' }}" href="{{ route('carniceria.index') }}">
                Todos<small>{{ array_sum($tipos) }}</small>
            </a>
            @foreach ($tipos as $nombreTipo => $cantidad)
                <a class="chip {{ $tipo === $nombreTipo ? 'activo' : '' }}"
                   href="{{ route('carniceria.index', array_filter(['tipo' => $nombreTipo, 'ofertas' => $soloOfertas ? 1 : null])) }}">
                    {{ $nombreTipo }}<small>{{ $cantidad }}</small>
                </a>
            @endforeach
            @if ($totalOfertas > 0)
                <a class="chip {{ $soloOfertas ? 'activo' : '' }}"
                   href="{{ route('carniceria.index', array_filter(['tipo' => $tipo ?: null, 'ofertas' => $soloOfertas ? null : 1])) }}">
                    Ofertas de hoy<small>{{ $totalOfertas }}</small>
                </a>
            @endif
        </nav>

        <div class="grid">
            @forelse ($productos as $producto)
                <article class="card producto">
                    @if ($producto['descuento_hoy'] > 0)
                        <span class="badge">-{{ $producto['descuento_hoy'] }}% HOY</span>
                    @endif
                    <span class="eyebrow">{{ $producto['categoria'] }}</span>
                    <h3>{{ $producto['nombre'] }}</h3>
                    <p class="tipo">Carne de {{ mb_strtolower($producto['tipo_carne']) }}</p>
                    <div class="precios">
                        <span class="precio-hoy">${{ number_format($producto['precio_hoy'], 0, ',', '.') }}</span>
                        @if ($producto['descuento_hoy'] > 0)
                            <span class="precio-antes">${{ number_format($producto['precio'], 0, ',', '.') }}</span>
                        @endif
                    </div>
                    @if ($producto['enlace'])
                        <a class="button" href="{{ $producto['enlace'] }}" target="_blank" rel="noopener noreferrer">Ver en la tienda</a>
                    @endif
                </article>
            @empty
                <p>No hay productos para este filtro.</p>
            @endforelse
        </div>

        <p class="fuente">
            Fuente: API de {{ $tienda }}
            @if ($generado)
                · actualizado el {{ $generado->format('d/m/Y H:i') }}
            @endif
        </p>
    @endif
</div>
@endsection
