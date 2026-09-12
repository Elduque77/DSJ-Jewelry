{{-- Autor: Samuel Correa Velasquez (Desarrollador) --}}
@extends('layouts.app')
@section('content')
<h1>Iniciar sesión</h1>
<form method="POST" action="{{ route('cliente.login.submit') }}">
    @csrf
    <label>Correo<input type="email" name="correo" value="{{ old('correo') }}" required></label>
    <label>Contraseña<input type="password" name="contrasena" required></label>
    <label><input type="checkbox" name="recordar" value="1"> Recordarme</label>
    <button type="submit">Entrar</button>
</form>
@endsection
