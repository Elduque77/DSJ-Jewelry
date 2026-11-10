{{-- Autor: Samuel Correa Velasquez (Desarrollador) --}}
@extends('layouts.app')
@section('content')
<h1>{{ __('messages.new_category') }}</h1>
<form method="POST" action="{{ route('admin.categoria.store') }}">@csrf
    <label>{{ __('messages.name') }}<input name="nombre" value="{{ old('nombre') }}" required></label>
    <label>{{ __('messages.description') }}<textarea name="descripcion" required>{{ old('descripcion') }}</textarea></label>
    <button type="submit">{{ __('messages.save') }}</button>
</form>
@endsection
