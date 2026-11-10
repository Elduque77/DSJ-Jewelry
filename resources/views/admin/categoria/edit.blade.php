{{-- Autor: Samuel Correa Velasquez (Desarrollador) --}}
@extends('layouts.app')
@section('content')
<h1>{{ __('messages.edit_category') }}</h1>
<form method="POST" action="{{ route('admin.categoria.update', $categoria) }}">@csrf @method('PUT')
    <label>{{ __('messages.name') }}<input name="nombre" value="{{ old('nombre', $categoria->getNombre()) }}" required></label>
    <label>{{ __('messages.description') }}<textarea name="descripcion" required>{{ old('descripcion', $categoria->getDescripcion()) }}</textarea></label>
    <button type="submit">{{ __('messages.update') }}</button>
</form>
@endsection
