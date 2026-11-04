{{-- Autor: Diego (Arquitecto) --}}
<h3>{{ $resena ? __('messages.edit_review') : __('messages.write_review') }}</h3>
<form method="POST" action="{{ $resena ? route('resena.update', $resena) : route('resena.store', $producto) }}">
    @csrf
    @if ($resena) @method('PUT') @endif
    <label>{{ __('messages.rating') }}<select name="calificacion" required>
        @for ($valor = 5; $valor >= 1; $valor--)
            <option value="{{ $valor }}" @selected(old('calificacion', $resena?->getCalificacion() ?? '') == $valor)>{{ $valor }} - {{ str_repeat('★', $valor) }}</option>
        @endfor
    </select></label>
    <label>{{ __('messages.comment') }}<textarea name="comentario" rows="4">{{ old('comentario', $resena?->getComentario() ?? '') }}</textarea></label>
    <button type="submit">{{ $resena ? __('messages.update_review') : __('messages.publish_review') }}</button>
</form>
@if ($resena)
    <form method="POST" action="{{ route('resena.destroy', $resena) }}">
        @csrf @method('DELETE')
        <button class="danger" type="submit" onclick="return confirm('{{ __('messages.confirm_delete_review') }}')">{{ __('messages.delete_my_review') }}</button>
    </form>
@endif
