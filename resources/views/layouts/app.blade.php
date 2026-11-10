{{-- Autor: Samuel Correa Velasquez (Desarrollador) --}}
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'DSJ Jewelry' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { margin: 0; }
        main { max-width: 1180px; margin: 0 auto; padding: 0 1.25rem 4rem; }
        .card, form { background: var(--surface); padding: 1.25rem; border: 1px solid var(--line); border-radius: 8px; margin-bottom: 1rem; }
        label { display: block; margin-top: .75rem; font-weight: bold; color: var(--text); }
        input, textarea, select { width: 100%; box-sizing: border-box; padding: .65rem; margin-top: .3rem; border: 1px solid var(--line); border-radius: 4px; background: var(--surface-soft); color: var(--text); }
        button, .button { display: inline-block; background: var(--accent); color: var(--button-text); border: 0; padding: .65rem 1rem; border-radius: 4px; text-decoration: none; cursor: pointer; margin-top: 1rem; }
        .danger { background: #a94442; color: #fff; } .success { color: #8fd1a6; } .error { color: #ef9a9a; }
        table { width: 100%; border-collapse: collapse; background: var(--surface); color: var(--text); } th, td { padding: .75rem; border-bottom: 1px solid var(--line); text-align: left; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; }
        .paginacion { display: flex; flex-wrap: wrap; align-items: center; gap: .35rem; margin-top: 1rem; }
        .paginacion .pagina { padding: .4rem .7rem; border: 1px solid var(--line); border-radius: 4px; background: var(--surface); color: var(--text); text-decoration: none; }
        .paginacion .pagina.actual { background: var(--accent); border-color: var(--accent); color: var(--button-text); }
        .paginacion .pagina.inactiva { color: var(--muted); }
        .paginacion-resumen { margin-left: auto; color: var(--muted); font-size: .9rem; }
    </style>
</head>
<body>
    <nav class="site-nav">
        <a href="{{ route('home') }}" aria-label="{{ __('messages.brand') }}">{{ __('messages.brand') }}</a>
        <a href="{{ route('home') }}">{{ __('messages.home') }}</a>
        <a href="{{ route('carniceria.index') }}">Carnicería aliada</a>
        @auth('admin')
            <a href="{{ route('admin.dashboard') }}">{{ __('messages.admin_panel') }}</a>
            <a href="{{ route('admin.categoria.index') }}">{{ __('messages.categories') }}</a>
            <a href="{{ route('admin.producto.index') }}">{{ __('messages.products') }}</a>
            <form method="POST" action="{{ route('admin.logout') }}" style="display:inline;padding:0;background:none">
                @csrf <button type="submit">{{ __('messages.logout') }}</button>
            </form>
        @else
            <a href="{{ route('admin.login') }}">{{ __('messages.admin') }}</a>
        @endauth
        @if (!auth('admin')->check())
            <a href="{{ route('carrito.index') }}">{{ __('messages.cart') }}</a>
            @auth('cliente')
                <form method="POST" action="{{ route('cliente.logout') }}" style="display:inline;padding:0;background:none">
                    @csrf <button type="submit">{{ __('messages.logout') }}</button>
                </form>
            @else
                <a href="{{ route('cliente.login') }}">{{ __('messages.login') }}</a>
                <a href="{{ route('cliente.registro') }}">{{ __('messages.register') }}</a>
            @endauth
        @endif
        <div class="language-switcher" aria-label="{{ __('messages.language') }}">
            <span>{{ __('messages.language') }}:</span>
            <a href="{{ route('locale.update', 'es') }}" hreflang="es">ES</a>
            <a href="{{ route('locale.update', 'en') }}" hreflang="en">EN</a>
        </div>
    </nav>
    <main class="site-main">
        @if (session('mensaje')) <p class="success">{{ session('mensaje') }}</p> @endif
        @if (session('error')) <p class="error">{{ session('error') }}</p> @endif
        @if ($errors->any())
            <div class="error"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        @yield('content')
    </main>
</body>
</html>
