{{-- Autor: Diego (Arquitecto) --}}
@forelse ($resenas as $resena)
    <article class="card">
        <p>
            <strong>{{ $resena->getCliente()->getNombreCompleto() }}</strong>
            — {{ str_repeat('★', $resena->getCalificacion()) }}{{ str_repeat('☆', 5 - $resena->getCalificacion()) }}
            <small>{{ $resena->getFechaFormateada() }}</small>
        </p>
        @if ($resena->getComentario())
            <p>{{ $resena->getComentario() }}</p>
        @endif
    </article>
@empty
    <p>Todavía no hay reseñas para este producto.</p>
@endforelse
