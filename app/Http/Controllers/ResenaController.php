<?php

/**
 * Autor: Diego (Arquitecto)
 */

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Resena;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResenaController extends Controller
{
    public function store(Request $request, Producto $producto): RedirectResponse
    {
        $datos = $this->validarDatos($request);

        $idCliente = Auth::guard('cliente')->id();

        if (Resena::where('idCliente', $idCliente)->where('idProducto', $producto->getIdProducto())->exists()) {
            return back()->withErrors([
                'calificacion' => 'Ya publicaste una resena para este producto.',
            ]);
        }

        $resena = new Resena;
        $resena->setIdCliente($idCliente);
        $resena->setIdProducto($producto->getIdProducto());
        $resena->setCalificacion($datos['calificacion']);
        $resena->setComentario($datos['comentario'] ?? null);
        // Todavia no existe el modulo de pedidos, asi que no hay compra contra
        // la que verificar. El panel de administracion marcara esto mas adelante.
        $resena->setCompraVerificada(false);
        $resena->setFecha(now()->toDateString());
        $resena->save();

        return redirect()->route('producto.show', $producto)
            ->with('mensaje', 'Resena publicada correctamente.');
    }

    public function update(Request $request, Resena $resena): RedirectResponse
    {
        $this->autorizarPropietario($resena);

        $datos = $this->validarDatos($request);

        // La fecha no se toca: representa la publicacion original, no la edicion.
        $resena->setCalificacion($datos['calificacion']);
        $resena->setComentario($datos['comentario'] ?? null);
        $resena->save();

        return redirect()->route('producto.show', $resena->getIdProducto())
            ->with('mensaje', 'Resena actualizada correctamente.');
    }

    public function destroy(Resena $resena): RedirectResponse
    {
        $this->autorizarPropietario($resena);

        $idProducto = $resena->getIdProducto();
        $resena->delete();

        return redirect()->route('producto.show', $idProducto)
            ->with('mensaje', 'Resena eliminada correctamente.');
    }

    // El proyecto no usa policies. La pertenencia se comprueba aqui, igual que
    // el acceso al panel se resuelve con el middleware de la ruta y nada mas.
    private function autorizarPropietario(Resena $resena): void
    {
        abort_unless($resena->getIdCliente() === Auth::guard('cliente')->id(), 403);
    }

    // Se valida antes de tocar el modelo para que la excepcion de
    // Resena::setCalificacion() nunca llegue al usuario como error 500.
    private function validarDatos(Request $request): array
    {
        return $request->validate([
            'calificacion' => ['required', 'integer', 'min:1', 'max:5'],
            'comentario' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
