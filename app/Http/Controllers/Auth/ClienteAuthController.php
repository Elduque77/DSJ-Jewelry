<?php

/**
 * Autor: Samuel Correa Velasquez (Desarrollador)
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ClienteAuthController extends Controller
{
    public function mostrarLogin(): View
    {
        return view('cliente.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credenciales = $request->validate([
            'correo' => ['required', 'email'],
            'contrasena' => ['required', 'string'],
        ]);

        $recordar = $request->boolean('recordar');

        if (
            ! Auth::guard('cliente')->attempt([
                'correo' => $credenciales['correo'],
                'password' => $credenciales['contrasena'],
            ], $recordar)
        ) {
            return back()->withErrors([
                'correo' => 'Las credenciales no coinciden con ningun cliente registrado.',
            ])->onlyInput('correo');
        }

        $request->session()->regenerate();

        return redirect()->route('home');
    }

    public function mostrarRegistro(): View
    {
        return view('cliente.auth.registro');
    }

    public function registrar(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'correo' => ['required', 'email', 'unique:clientes,correo'],
            'contrasena' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $cliente = new Cliente;
        $cliente->setNombre($datos['nombre']);
        $cliente->setApellido($datos['apellido']);
        $cliente->setCorreo($datos['correo']);
        $cliente->setContrasena($datos['contrasena']);
        $cliente->save();

        Auth::guard('cliente')->login($cliente);

        $request->session()->regenerate();

        return redirect()->route('home')
            ->with('mensaje', 'Cuenta creada correctamente. ¡Bienvenido a DSJ Jewelry!');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('cliente')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
