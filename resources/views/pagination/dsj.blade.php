{{-- Autor: Diego (Arquitecto) --}}
{{--
    Vista de paginado propia. Las que trae Laravel por defecto estan escritas
    para Tailwind o Bootstrap y el proyecto no usa ninguno de los dos: todo el
    estilo vive en layouts/app.blade.php. Se registra como vista por defecto
    en AppServiceProvider, asi que basta con llamar a $paginador->links().
--}}
@if ($paginator->hasPages())
    <nav class="paginacion" role="navigation" aria-label="Paginación">
        @if ($paginator->onFirstPage())
            <span class="pagina inactiva" aria-disabled="true">{!! __('pagination.previous') !!}</span>
        @else
            <a class="pagina" href="{{ $paginator->previousPageUrl() }}" rel="prev">{!! __('pagination.previous') !!}</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="pagina inactiva" aria-disabled="true">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="pagina actual" aria-current="page">{{ $page }}</span>
                    @else
                        <a class="pagina" href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a class="pagina" href="{{ $paginator->nextPageUrl() }}" rel="next">{!! __('pagination.next') !!}</a>
        @else
            <span class="pagina inactiva" aria-disabled="true">{!! __('pagination.next') !!}</span>
        @endif

        <span class="paginacion-resumen">
            {{ $paginator->firstItem() }}&ndash;{{ $paginator->lastItem() }} de {{ $paginator->total() }}
        </span>
    </nav>
@endif
