{{-- Autor: Samuel Correa Velasquez (Desarrollador) --}}
@extends('layouts.app')
@section('content')
<h1>Categorías</h1><a class="button" href="{{ route('admin.categoria.create') }}">Nueva categoría</a>
<table><thead><tr><th>Nombre</th><th>Descripción</th><th>Acciones</th></tr></thead><tbody>
@forelse ($categorias as $categoria)
<tr><td>{{ $categoria->getNombre() }}</td><td>{{ $categoria->getDescripcion() }}</td><td>
<a href="{{ route('admin.categoria.edit', $categoria) }}">Editar</a>
<form method="POST" action="{{ route('admin.categoria.destroy', $categoria) }}" style="display:inline;padding:0;background:none">@csrf @method('DELETE')<button class="danger" type="submit" onclick="return confirm('¿Eliminar categoría?')">Eliminar</button></form>
</td></tr>
@empty <tr><td colspan="3">No hay categorías.</td></tr>@endforelse
</tbody></table>
@endsection
