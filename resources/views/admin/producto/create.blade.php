{{-- Autor: Samuel Correa Velasquez (Desarrollador) --}}
@extends('layouts.app')
@section('content')
<h1>Nuevo producto</h1>
<form method="POST" action="{{ route('admin.producto.store') }}">@csrf
    @include('admin.producto.form')
    <button type="submit">Guardar</button>
</form>
@endsection
