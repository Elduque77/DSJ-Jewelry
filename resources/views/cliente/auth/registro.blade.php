{{-- Autor: Samuel Correa Velasquez (Desarrollador) --}}
@extends('layouts.app')
@section('content')
<h1>{{ __('messages.register_title') }}</h1>
<form method="POST" action="{{ route('cliente.registro.submit') }}">
    @csrf
    <label>{{ __('messages.first_name') }}<input name="nombre" value="{{ old('nombre') }}" required></label>
    <label>{{ __('messages.last_name') }}<input name="apellido" value="{{ old('apellido') }}" required></label>
    <label>{{ __('messages.email') }}<input type="email" name="correo" value="{{ old('correo') }}" required></label>
    <label>{{ __('messages.password') }}<input type="password" name="contrasena" required></label>
    <label>{{ __('messages.confirm_password') }}<input type="password" name="contrasena_confirmation" required></label>
    <button type="submit">{{ __('messages.sign_up') }}</button>
</form>
@endsection
