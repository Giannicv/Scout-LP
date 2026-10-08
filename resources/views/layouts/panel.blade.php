<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>
            {{ $titulo ?? $configuracion['titulo'] ?? 'Mi panel' }}
            | Scouts de Bolivia
        </title>
        @foreach (['variables', 'base', 'componentes', 'admin', 'panel'] as $hojaEstilo)
            <link rel="stylesheet" href="{{ asset('css/'.$hojaEstilo.'.css') }}">
        @endforeach
        <script src="{{ asset('js/interacciones.js') }}" defer>
        </script>
    </head>
    <body data-privado="true">
        <a class="skip" href="#contenido">
            Saltar al contenido
        </a>
        <aside class="admin-sidebar" id="admin-sidebar">
            <x-marca />
            <nav aria-label="Menú del panel">
                @foreach (config('scouts.menus')[$rolRuta] as $rutaPagina => $textoPagina)
                    <a
                        href="{{ route($rolRuta.'.'.$rutaPagina) }}"
                    @class(['active' => request()->routeIs($rolRuta.'.'.$rutaPagina)])>
                        <x-icono :nombre="$rutaPagina === 'dashboard' ? 'compass' : 'lis'" />
                        {{ $textoPagina }}
                    </a>
                @endforeach
            </nav>
            <a class="back-site" href="{{ route('inicio') }}">
                Ver sitio público
            </a>
        </aside>
        <div class="admin-wrap">
            <header class="admin-topbar">
                <div class="actions">
                    <button
                        class="admin-menu"
                        aria-expanded="false"
                        aria-controls="admin-sidebar"
                        aria-label="Abrir menú">
                        <x-icono nombre="menu" />
                    </button>
                    <h1>
                        {{ $titulo ?? $configuracion['titulo'] ?? 'Mi panel' }}
                    </h1>
                </div>
                <div class="actions">
                    <span>
                        {{ auth()->user()->nombre }}
                    </span>
                    <span class="role-badge">
                        {{ auth()->user()->rol->nombre }}
                    </span>
                    <form method="POST" action="{{ route('salir') }}">
                        @csrf
                        <button class="btn secondary small">
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            </header>
            <main class="admin-main" id="contenido">
                @include('partials.avisos')
                @yield('contenido')
            </main>
        </div>
    </body>
</html>
