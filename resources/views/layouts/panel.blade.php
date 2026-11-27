<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#52307f">
    <title>{{ $titulo ?? 'Panel Scout' }} | Distrito La Paz</title>
    <script src="{{ asset('js/tema.js').'?v='.filemtime(public_path('js/tema.js')) }}"></script>
    @foreach (['variables', 'base', 'componentes', 'admin', 'panel'] as $hoja)<link rel="stylesheet" href="{{ asset('css/'.$hoja.'.css').'?v='.filemtime(public_path('css/'.$hoja.'.css')) }}">@endforeach
</head>
<body data-privado="true">
<a class="skip" href="#contenido">Saltar al contenido</a>
<aside class="admin-sidebar" id="admin-sidebar">
    <x-marca />
    <nav aria-label="Menú del panel">
        <a href="{{ route('panel') }}" @class(['active' => request()->routeIs('panel')])>Resumen</a>
        <a href="{{ route('eventos.index') }}" @class(['active' => request()->routeIs('eventos.*')])>Eventos</a>
        @if(in_array(auth()->user()->rol->nombre, ['dirigente_distrital', 'dirigente_grupo']))
            <a href="{{ route('solicitudes.index') }}" @class(['active' => request()->routeIs('solicitudes.*')])>Solicitudes</a>
            <a href="{{ route('miembros.index') }}" @class(['active' => request()->routeIs('miembros.*')])>Miembros</a>
        @endif
        @if(auth()->user()->es('dirigente_distrital'))<a href="{{ route('dirigentes.index') }}" @class(['active' => request()->routeIs('dirigentes.*')])>Dirigentes</a>@endif
        @if(auth()->user()->es('miembro'))<a href="{{ route('perfil') }}" @class(['active' => request()->routeIs('perfil*')])>Mi perfil</a>@else<a href="{{ route('cuenta') }}" @class(['active' => request()->routeIs('cuenta')])>Mi cuenta</a>@endif
        @if(auth()->user()->es('dirigente_distrital'))<a href="{{ route('publicaciones.index') }}" @class(['active' => request()->routeIs('publicaciones.*')])>Noticias y contenido</a>@endif
        <a href="{{ route('biblioteca.index') }}" @class(['active' => request()->routeIs('biblioteca.*')])>Biblioteca</a>
        @php($notificacionesPendientes = auth()->user()->unreadNotifications()->count())
        <a href="{{ route('notificaciones.index') }}" @class(['active' => request()->routeIs('notificaciones.*')])>Notificaciones @if($notificacionesPendientes > 0)<span class="notification-dot" aria-label="{{ $notificacionesPendientes }} sin leer">{{ $notificacionesPendientes }}</span>@endif</a>
    </nav>
    <a class="back-site" href="{{ route('inicio') }}">Ver sitio público</a>
</aside>
<div class="admin-wrap">
    <header class="admin-topbar"><div class="actions"><button class="admin-menu" type="button" aria-controls="admin-sidebar" aria-expanded="false" aria-label="Abrir navegación"><x-icono nombre="menu" /></button><h1>{{ $titulo ?? 'Panel Scout' }}</h1></div><div class="actions">
        <span class="user-label">{{ auth()->user()->nombre }} · {{ str_replace('_', ' ', auth()->user()->rol->nombre) }}</span>
        <x-tema />
        <form method="POST" action="{{ route('salir') }}">@csrf<button class="btn secondary small">Cerrar sesión</button></form>
    </div></header>
    <main class="admin-main" id="contenido">@include('partials.avisos') @yield('contenido')</main>
</div>
@include('partials.confirmacion')
<script src="{{ asset('js/interacciones.js').'?v='.filemtime(public_path('js/interacciones.js')) }}" defer></script>
</body></html>
