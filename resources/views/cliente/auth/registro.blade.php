{{-- Autor: Samuel Correa Velasquez (Desarrollador) --}}
@extends('layouts.app')
@section('content')
<h1>Crear cuenta</h1>
<form method="POST" action="{{ route('cliente.registro.submit') }}">
    @csrf
    <label>Nombre<input name="nombre" value="{{ old('nombre') }}" required></label>
    <label>Apellido<input name="apellido" value="{{ old('apellido') }}" required></label>
    <label>Correo<input type="email" name="correo" value="{{ old('correo') }}" required></label>
    <label>Contraseña<input type="password" name="contrasena" required></label>
    <label>Confirmar contraseña<input type="password" name="contrasena_confirmation" required></label>
    <button type="submit">Registrarme</button>
</form>
@endsection
