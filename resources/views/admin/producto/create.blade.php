{{-- Autor: Samuel Correa Velasquez (Desarrollador) --}}
@extends('layouts.app')
@section('content')
<h1>{{ __('messages.new_product') }}</h1>
<form method="POST" action="{{ route('admin.producto.store') }}" enctype="multipart/form-data">@csrf
    @include('admin.producto.form')
    <button type="submit">{{ __('messages.save') }}</button>
</form>
@endsection
