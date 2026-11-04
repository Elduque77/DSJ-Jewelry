{{-- Autor: Samuel Correa Velasquez (Desarrollador) --}}
@extends('layouts.app')
@section('content')
<h1>{{ __('messages.edit_product') }}</h1>
<form method="POST" action="{{ route('admin.producto.update', $producto) }}" enctype="multipart/form-data">@csrf @method('PUT')
    @include('admin.producto.form', ['producto' => $producto])
    <button type="submit">{{ __('messages.update') }}</button>
</form>
@endsection
