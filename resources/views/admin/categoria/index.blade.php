{{-- Autor: Samuel Correa Velasquez (Desarrollador) --}}
@extends('layouts.app')
@section('content')
<h1>{{ __('messages.categories') }}</h1><a class="button" href="{{ route('admin.categoria.create') }}">{{ __('messages.new_category') }}</a>
<table><thead><tr><th>{{ __('messages.name') }}</th><th>{{ __('messages.description') }}</th><th>{{ __('messages.actions') }}</th></tr></thead><tbody>
@forelse ($categorias as $categoria)
<tr><td>{{ $categoria->getNombre() }}</td><td>{{ $categoria->getDescripcion() }}</td><td>
<a href="{{ route('admin.categoria.edit', $categoria) }}">{{ __('messages.edit_category') }}</a>
<form method="POST" action="{{ route('admin.categoria.destroy', $categoria) }}" style="display:inline;padding:0;background:none">@csrf @method('DELETE')<button class="danger" type="submit" onclick="return confirm('{{ __('messages.delete') }}?')">{{ __('messages.delete') }}</button></form>
</td></tr>
@empty <tr><td colspan="3">{{ __('messages.no_categories') }}</td></tr>@endforelse
</tbody></table>
{{ $categorias->links() }}
@endsection
