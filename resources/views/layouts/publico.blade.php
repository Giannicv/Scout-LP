<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta
            name="description" content="Formando líderes desde 1911. Descubre la aventura scout en Bolivia.">
        <meta name="theme-color" content="#52307F">
        <title>
            @yield('titulo', 'Scouts de Bolivia')
        </title>
        <link rel="icon" href="{{ asset('img/favicon.svg') }}" type="image/svg+xml">
        <script src="{{ asset('js/tema.js') }}"></script>
        @foreach (['variables', 'base', 'componentes', 'publico', 'panel'] as $hojaEstilo)
            <link rel="stylesheet" href="{{ asset('css/'.$hojaEstilo.'.css') }}">
        @endforeach
        <script src="{{ asset('js/interacciones.js') }}" defer>
        </script>
    </head>
    <body>
        <a class="skip" href="#contenido">
            Saltar al contenido
        </a>
        @include('partials.navegacion')
        <main id="contenido">
            @include('partials.avisos')
            @yield('contenido')
        </main>
        @include('partials.pie')
    </body>
</html>
