{{-- Autor: Samuel Correa Velasquez (Desarrollador) --}}
<label>Categoría<select name="idCategoria" required><option value="">Selecciona una categoría</option>
@foreach ($categorias as $categoria)<option value="{{ $categoria->getIdCategoria() }}" @selected(old('idCategoria', $producto?->getIdCategoria() ?? '') == $categoria->getIdCategoria())>{{ $categoria->getNombre() }}</option>@endforeach
</select></label>
<label>Nombre<input name="nombre" value="{{ old('nombre', $producto?->getNombre() ?? '') }}" required></label>
<label>Descripción<textarea name="descripcion" required>{{ old('descripcion', $producto?->getDescripcion() ?? '') }}</textarea></label>
<label>Material<input name="material" value="{{ old('material', $producto?->getMaterial() ?? '') }}" required></label>
<label>Precio<input type="number" name="precio" step="0.01" min="0" value="{{ old('precio', $producto?->getPrecio() ?? '') }}" required></label>
<label>Stock<input type="number" name="stock" min="0" value="{{ old('stock', $producto?->getStock() ?? 0) }}" required></label>
