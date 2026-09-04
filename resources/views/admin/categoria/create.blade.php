{{-- Autor: Samuel Correa Velasquez (Desarrollador) --}}
@extends('layouts.app')
@section('content')
<h1>Nueva categoría</h1>
<form method="POST" action="{{ route('admin.categoria.store') }}">@csrf
    <label>Nombre<input name="nombre" value="{{ old('nombre') }}" required></label>
    <label>Descripción<textarea name="descripcion" required>{{ old('descripcion') }}</textarea></label>
    <button type="submit">Guardar</button>
</form>
@endsection
