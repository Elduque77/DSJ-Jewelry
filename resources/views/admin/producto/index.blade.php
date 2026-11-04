{{-- Autor: Samuel Correa Velasquez (Desarrollador) --}}
@extends('layouts.app')
@section('content')
<h1>{{ __('messages.products') }}</h1><a class="button" href="{{ route('admin.producto.create') }}">{{ __('messages.new_product') }}</a>
<table><thead><tr><th>{{ __('messages.name') }}</th><th>{{ __('messages.category') }}</th><th>{{ __('messages.price') }}</th><th>{{ __('messages.available') }}</th><th>{{ __('messages.actions') }}</th></tr></thead><tbody>
@forelse ($productos as $producto)
<tr><td>{{ $producto->getNombre() }}</td><td>{{ $producto->getCategoria()->getNombre() }}</td><td>${{ number_format($producto->getPrecio(), 2) }}</td><td>{{ $producto->getStock() }}</td><td>
<a href="{{ route('admin.producto.edit', $producto) }}">{{ __('messages.edit_product') }}</a>
<form method="POST" action="{{ route('admin.producto.destroy', $producto) }}" style="display:inline;padding:0;background:none">@csrf @method('DELETE')<button class="danger" type="submit" onclick="return confirm('{{ __('messages.delete') }}?')">{{ __('messages.delete') }}</button></form>
</td></tr>
@empty <tr><td colspan="5">{{ __('messages.no_products') }}</td></tr>@endforelse
</tbody></table>
{{ $productos->links() }}
@endsection
