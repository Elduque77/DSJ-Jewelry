{{-- Autor: Samuel Correa Velasquez (Desarrollador) --}}
@extends('layouts.app')
@section('content')
<h1>Editar producto</h1>
<form method="POST" action="{{ route('admin.producto.update', $producto) }}">@csrf @method('PUT')
    @include('admin.producto.form', ['producto' => $producto])
    <button type="submit">Actualizar</button>
</form>
@endsection
