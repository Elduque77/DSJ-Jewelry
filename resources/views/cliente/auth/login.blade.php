{{-- Autor: Samuel Correa Velasquez (Desarrollador) --}}
@extends('layouts.app')
@section('content')
<h1>{{ __('messages.login_title') }}</h1>
<form method="POST" action="{{ route('cliente.login.submit') }}">
    @csrf
    <label>{{ __('messages.email') }}<input type="email" name="correo" value="{{ old('correo') }}" required></label>
    <label>{{ __('messages.password') }}<input type="password" name="contrasena" required></label>
    <label><input type="checkbox" name="recordar" value="1"> {{ __('messages.remember') }}</label>
    <button type="submit">{{ __('messages.enter') }}</button>
</form>
@endsection
