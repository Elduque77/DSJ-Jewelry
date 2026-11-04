{{-- Autor: Samuel Correa Velasquez (Desarrollador) --}}
<label>{{ __('messages.category') }}<select name="idCategoria" required><option value="">{{ __('messages.select_category') }}</option>
@foreach ($categorias as $categoria)<option value="{{ $categoria->getIdCategoria() }}" @selected(old('idCategoria', $producto?->getIdCategoria() ?? '') == $categoria->getIdCategoria())>{{ $categoria->getNombre() }}</option>@endforeach
</select></label>
<label>{{ __('messages.name') }}<input name="nombre" value="{{ old('nombre', $producto?->getNombre() ?? '') }}" required></label>
<label>{{ __('messages.description') }}<textarea name="descripcion" required>{{ old('descripcion', $producto?->getDescripcion() ?? '') }}</textarea></label>
<label>{{ __('messages.material') }}<input name="material" value="{{ old('material', $producto?->getMaterial() ?? '') }}" required></label>
<label>{{ __('messages.price') }}<input type="number" name="precio" step="0.01" min="0" value="{{ old('precio', $producto?->getPrecio() ?? '') }}" required></label>
<label>{{ __('messages.available') }}<input type="number" name="stock" min="0" value="{{ old('stock', $producto?->getStock() ?? 0) }}" required></label>
<label>{{ __('messages.image') }}<input type="file" name="imagen" accept="image/jpeg,image/png,image/webp"></label>
@if ($producto?->getImagen())
    <img class="product-image-preview" src="{{ asset('storage/'.$producto->getImagen()) }}" alt="{{ $producto->getNombre() }}">
@endif
