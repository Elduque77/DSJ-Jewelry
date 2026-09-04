{{-- Autor: Samuel Correa Velasquez (Desarrollador) --}}
@extends('layouts.app')
@section('content')
<h1>Productos</h1><a class="button" href="{{ route('admin.producto.create') }}">Nuevo producto</a>
<table><thead><tr><th>Nombre</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Acciones</th></tr></thead><tbody>
@forelse ($productos as $producto)
<tr><td>{{ $producto->getNombre() }}</td><td>{{ $producto->getCategoria()->getNombre() }}</td><td>${{ number_format($producto->getPrecio(), 2) }}</td><td>{{ $producto->getStock() }}</td><td>
<a href="{{ route('admin.producto.edit', $producto) }}">Editar</a>
<form method="POST" action="{{ route('admin.producto.destroy', $producto) }}" style="display:inline;padding:0;background:none">@csrf @method('DELETE')<button class="danger" type="submit" onclick="return confirm('¿Eliminar producto?')">Eliminar</button></form>
</td></tr>
@empty <tr><td colspan="5">No hay productos.</td></tr>@endforelse
</tbody></table>
@endsection
