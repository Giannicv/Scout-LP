<header class="site-header" id="site-header">
    <div class="container nav-inner">
        <x-marca />
        <button
            class="menu-toggle"
            aria-expanded="false"
            aria-controls="public-menu"
            aria-label="Abrir menú">
            <x-icono nombre="menu" />
        </button>
        <nav class="nav-links" id="public-menu" aria-label="Navegación principal">
            @foreach (['inicio' => 'Inicio', 'ramas' => 'Ramas educativas', 'noticias.index' => 'Historias y noticias',
                'nosotros' => 'Nosotros'] as $rutaMenu => $textoMenu)
                <a href="{{ route($rutaMenu) }}" @class(['active' => request()->routeIs($rutaMenu)])>
                    {{ $textoMenu }}
                </a>
            @endforeach
            <x-tema />
            @auth
                <a href="{{ route('panel') }}">
                    Mi panel
                </a>
                <form method="POST" action="{{ route('salir') }}">
                    @csrf
                    <button class="btn secondary small">
                        Cerrar sesión
                    </button>
                </form>
            @else
                <a class="admin-link" href="{{ route('login') }}">
                    Ingresar
                </a>
            @endauth
        </nav>
    </div>
</header>
