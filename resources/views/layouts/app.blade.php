{{-- Autor: Samuel Correa Velasquez (Desarrollador) --}}
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'DSJ Jewelry' }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f6f4f1; color: #292524; }
        nav { background: #292524; padding: 1rem 2rem; }
        nav a, nav button { color: white; margin-right: 1rem; text-decoration: none; background: none; border: 0; cursor: pointer; font-size: 1rem; }
        main { max-width: 1100px; margin: 2rem auto; padding: 0 1rem; }
        .card, form { background: white; padding: 1.25rem; border-radius: 8px; margin-bottom: 1rem; }
        label { display: block; margin-top: .75rem; font-weight: bold; }
        input, textarea, select { width: 100%; box-sizing: border-box; padding: .65rem; margin-top: .3rem; border: 1px solid #d6d3d1; border-radius: 4px; }
        button, .button { display: inline-block; background: #57534e; color: white; border: 0; padding: .65rem 1rem; border-radius: 4px; text-decoration: none; cursor: pointer; margin-top: 1rem; }
        .danger { background: #b91c1c; } .success { color: #166534; } .error { color: #b91c1c; }
        table { width: 100%; border-collapse: collapse; background: white; } th, td { padding: .75rem; border-bottom: 1px solid #e7e5e4; text-align: left; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; }
    </style>
</head>
<body>
    <nav>
        <a href="{{ route('home') }}">DSJ Jewelry</a>
        @auth('admin')
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a href="{{ route('admin.categoria.index') }}">Categorías</a>
            <a href="{{ route('admin.producto.index') }}">Productos</a>
            <form method="POST" action="{{ route('admin.logout') }}" style="display:inline;padding:0;background:none">
                @csrf <button type="submit">Cerrar sesión</button>
            </form>
        @else
            <a href="{{ route('admin.login') }}">Administración</a>
        @endauth
        @if (!auth('admin')->check())
            @auth('cliente')
                <form method="POST" action="{{ route('cliente.logout') }}" style="display:inline;padding:0;background:none">
                    @csrf <button type="submit">Cerrar sesión</button>
                </form>
            @else
                <a href="{{ route('cliente.login') }}">Iniciar sesión</a>
                <a href="{{ route('cliente.registro') }}">Registrarse</a>
            @endauth
        @endif
    </nav>
    <main>
        @if (session('mensaje')) <p class="success">{{ session('mensaje') }}</p> @endif
        @if ($errors->any())
            <div class="error"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        @yield('content')
    </main>
</body>
</html>
