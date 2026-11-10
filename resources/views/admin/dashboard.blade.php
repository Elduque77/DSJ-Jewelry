{{-- Autor: Samuel Correa Velasquez (Desarrollador) --}}
@extends('layouts.app')
@section('content')
<h1>{{ __('messages.admin_panel') }}</h1>
<div class="grid">
    <div class="card"><h2>{{ $totalCategorias }}</h2><p>{{ __('messages.categories') }}</p><a class="button" href="{{ route('admin.categoria.index') }}">{{ __('messages.manage') }}</a></div>
    <div class="card"><h2>{{ $totalProductos }}</h2><p>{{ __('messages.products') }}</p><a class="button" href="{{ route('admin.producto.index') }}">{{ __('messages.manage') }}</a></div>
</div>
@endsection
