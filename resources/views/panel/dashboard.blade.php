@extends('layouts.panel', ['titulo' => 'Resumen'])
@section('contenido')
<section class="panel-grid">
    <article class="card"><h2>Eventos activos</h2><strong class="metric">{{ $datos['eventos'] }}</strong></article>
    <article class="card"><h2>Biblioteca</h2><strong class="metric">{{ $datos['recursos'] }}</strong><span>recursos</span></article>
    <article class="card"><h2>Notificaciones</h2><strong class="metric">{{ $datos['notificaciones'] }}</strong><span>sin leer</span></article>
    @if(!$usuario->es('miembro'))<article class="card"><h2>Solicitudes</h2><strong class="metric">{{ $datos['solicitudes'] }}</strong><span>pendientes</span></article>@endif
</section>
<section class="card"><h2>Tu cuenta</h2><p>{{ $usuario->nombre }} {{ $usuario->apellido }}</p>
@if($usuario->grupo)<p>Grupo: {{ $usuario->grupo->nombre }}</p>@endif
@if($usuario->miembro?->rama)<p>Rama: {{ $usuario->miembro->rama->nombre }}</p>@endif</section>
@endsection
