{{-- Autor: Samuel Correa Velasquez (Desarrollador) --}}
@extends('layouts.app')
@section('content')
<h1>Panel de administración</h1>
<div class="grid">
    <div class="card"><h2>{{ $totalCategorias }}</h2><p>Categorías</p><a class="button" href="{{ route('admin.categoria.index') }}">Gestionar</a></div>
    <div class="card"><h2>{{ $totalProductos }}</h2><p>Productos</p><a class="button" href="{{ route('admin.producto.index') }}">Gestionar</a></div>
</div>
@endsection
