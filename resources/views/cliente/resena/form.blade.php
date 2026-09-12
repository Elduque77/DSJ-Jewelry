{{-- Autor: Diego (Arquitecto) --}}
<h3>{{ $resena ? 'Editar mi reseña' : 'Escribe tu reseña' }}</h3>
<form method="POST" action="{{ $resena ? route('resena.update', $resena) : route('resena.store', $producto) }}">
    @csrf
    @if ($resena) @method('PUT') @endif
    <label>Calificación<select name="calificacion" required>
        @for ($valor = 5; $valor >= 1; $valor--)
            <option value="{{ $valor }}" @selected(old('calificacion', $resena?->getCalificacion() ?? '') == $valor)>{{ $valor }} - {{ str_repeat('★', $valor) }}</option>
        @endfor
    </select></label>
    <label>Comentario<textarea name="comentario" rows="4">{{ old('comentario', $resena?->getComentario() ?? '') }}</textarea></label>
    <button type="submit">{{ $resena ? 'Actualizar reseña' : 'Publicar reseña' }}</button>
</form>
@if ($resena)
    <form method="POST" action="{{ route('resena.destroy', $resena) }}">
        @csrf @method('DELETE')
        <button class="danger" type="submit" onclick="return confirm('¿Eliminar reseña?')">Eliminar mi reseña</button>
    </form>
@endif
