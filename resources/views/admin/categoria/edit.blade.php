{{-- Autor: Samuel Correa Velasquez (Desarrollador) --}}
@extends('layouts.app')
@section('content')
<h1>Editar categoría</h1>
<form method="POST" action="{{ route('admin.categoria.update', $categoria) }}">@csrf @method('PUT')
    <label>Nombre<input name="nombre" value="{{ old('nombre', $categoria->getNombre()) }}" required></label>
    <label>Descripción<textarea name="descripcion" required>{{ old('descripcion', $categoria->getDescripcion()) }}</textarea></label>
    <button type="submit">Actualizar</button>
</form>
@endsection
