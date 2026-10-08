<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>
            @yield('titulo', 'Acceso | Scouts de Bolivia')
        </title>
        @foreach (['variables', 'base', 'componentes', 'admin', 'panel'] as $hojaEstilo)
            <link rel="stylesheet" href="{{ asset('css/'.$hojaEstilo.'.css') }}">
        @endforeach
        <script src="{{ asset('js/interacciones.js') }}" defer>
        </script>
    </head>
    <body class="login-page">
        <main class="login-card">
            <x-marca />
            @include('partials.avisos')
            @yield('contenido')
            <p class="center">
                <a href="{{ route('inicio') }}">
                    Volver al sitio
                </a>
            </p>
        </main>
    </body>
</html>
