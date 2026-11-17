@extends('layouts.panel', ['titulo' => 'Resumen'])
@section('contenido')
    @php
        $esMiembro = $usuario->es('miembro');
        $esDistrital = $usuario->es('dirigente_distrital');
        $accionPrincipal = ! $esMiembro && $datos['solicitudes'] > 0
            ? ['ruta' => route('solicitudes.index'), 'texto' => 'Revisar solicitudes']
            : (! $esMiembro && $datos['propuestas'] > 0
                ? ['ruta' => route('eventos.index', ['estado' => 'propuesto']), 'texto' => 'Revisar propuestas']
                : ['ruta' => route('eventos.index'), 'texto' => 'Ver eventos']);
    @endphp

    <header class="overview-hero">
        <div class="overview-hero-content">
            <p class="overview-context">{{ $esDistrital ? 'Distrito Scout La Paz' : ($esMiembro ? ($usuario->miembro?->rama?->nombre ?? 'Tu espacio scout') : ($usuario->grupo?->nombre ?? 'Tu grupo scout')) }}</p>
            <h2>Hola, {{ $usuario->nombre }}.</h2>
            <p>{{ $esDistrital
                ? 'Revisa lo que necesita el distrito y sigue las próximas actividades.'
                : ($esMiembro
                    ? 'Encuentra las actividades de tu rama y los recursos para participar.'
                    : 'Consulta las solicitudes de tu grupo y las actividades del distrito.') }}</p>
            <a class="overview-hero-action" href="{{ $accionPrincipal['ruta'] }}">{{ $accionPrincipal['texto'] }}</a>
        </div>
        <div class="overview-date" aria-label="Fecha de hoy">
            <span>{{ now()->translatedFormat('l') }}</span>
            <strong>{{ now()->format('d') }}</strong>
            <span>{{ now()->translatedFormat('F Y') }}</span>
        </div>
    </header>

    <section class="overview-stats" aria-label="Cifras de tu espacio">
        @if ($esMiembro)
            <a href="{{ route('eventos.index') }}"><strong>{{ $datos['eventos'] }}</strong><span>eventos para ti</span></a>
            <a href="{{ route('biblioteca.index') }}"><strong>{{ $datos['recursos'] }}</strong><span>recursos en biblioteca</span></a>
            <a href="{{ route('notificaciones.index', ['estado' => 'sin_leer']) }}"><strong>{{ $datos['notificaciones'] }}</strong><span>avisos sin leer</span></a>
        @else
            <a href="{{ route('solicitudes.index') }}"><strong>{{ $datos['solicitudes'] }}</strong><span>solicitudes pendientes</span></a>
            <a href="{{ route('eventos.index', ['estado' => 'propuesto']) }}"><strong>{{ $datos['propuestas'] }}</strong><span>propuestas por revisar</span></a>
            <a href="{{ route('eventos.index', ['vigencia' => 'proximos', 'estado' => 'publicado']) }}"><strong>{{ $datos['eventos'] }}</strong><span>eventos en agenda</span></a>
            <a href="{{ route('miembros.index', ['estado' => 'activo']) }}"><strong>{{ $datos['miembros'] }}</strong><span>miembros activos</span></a>
        @endif
    </section>

    <div class="overview-layout">
        <div class="overview-main-column">
            @unless ($esMiembro)
                <section class="overview-section" aria-labelledby="pendientes-titulo">
                    <div class="overview-section-heading"><h2 id="pendientes-titulo">Por atender</h2><p>Lo que requiere una decisión</p></div>
                    @if ($datos['solicitudes'] > 0 || $datos['propuestas'] > 0)
                        <div class="overview-task-list">
                            @if ($datos['solicitudes'] > 0)
                                <a href="{{ route('solicitudes.index') }}"><span class="overview-task-icon"><x-icono nombre="users" /></span><span><strong>{{ $datos['solicitudes'] }} {{ $datos['solicitudes'] === 1 ? 'solicitud pendiente' : 'solicitudes pendientes' }}</strong><small>{{ $esDistrital ? 'Dirigentes que esperan revisión' : 'Miembros de tu grupo que esperan revisión' }}</small></span><span class="overview-task-link">Revisar</span></a>
                            @endif
                            @if ($datos['propuestas'] > 0)
                                <a href="{{ route('eventos.index', ['estado' => 'propuesto']) }}"><span class="overview-task-icon"><x-icono nombre="calendar" /></span><span><strong>{{ $datos['propuestas'] }} {{ $datos['propuestas'] === 1 ? 'propuesta Rover' : 'propuestas Rover' }}</strong><small>Actividades por aprobar o rechazar</small></span><span class="overview-task-link">Revisar</span></a>
                            @endif
                        </div>
                    @else
                        <p class="overview-no-tasks">No hay solicitudes ni propuestas pendientes.</p>
                    @endif
                </section>
            @endunless

            <section class="overview-section" aria-labelledby="agenda-titulo">
                <div class="overview-section-heading"><h2 id="agenda-titulo">Próximas actividades</h2><a href="{{ route('eventos.index') }}">Ver agenda completa</a></div>
                @forelse ($eventosProximos as $evento)
                    @php
                        $operativo = $evento->estadoOperativo();
                        $inscrito = $esMiembro && $evento->inscripciones->isNotEmpty();
                    @endphp
                    <article class="overview-event">
                        <div class="overview-event-date"><strong>{{ $evento->fecha_inicio->format('d') }}</strong><span>{{ $evento->fecha_inicio->translatedFormat('M') }}</span></div>
                        <div class="overview-event-content"><h3><a href="{{ route('eventos.show', $evento) }}">{{ $evento->titulo }}</a></h3><p>{{ $evento->lugar }} · {{ $evento->fecha_inicio->format('H:i') }}</p></div>
                        <span @class(['overview-event-status', 'registered' => $inscrito])>{{ $inscrito ? 'Inscrito' : $operativo['etiqueta'] }}</span>
                    </article>
                @empty
                    <div class="overview-empty-event"><x-icono nombre="calendar" /><p>{{ $esMiembro ? 'Aún no hay actividades próximas para tu rama.' : 'No hay actividades publicadas en la agenda.' }}</p></div>
                @endforelse
            </section>
        </div>

        <aside class="overview-side" aria-label="Información y accesos">
            <section class="overview-section" aria-labelledby="avisos-titulo">
                <div class="overview-section-heading"><h2 id="avisos-titulo">Avisos sin leer</h2><a href="{{ route('notificaciones.index') }}">Ver todos</a></div>
                @forelse ($avisosRecientes as $aviso)
                    <a class="overview-notice" href="{{ route('notificaciones.index', ['estado' => 'sin_leer']) }}"><strong>{{ $aviso->data['titulo'] ?? 'Notificación' }}</strong><small>{{ $aviso->created_at->diffForHumans() }}</small></a>
                @empty
                    <p class="overview-no-notices">No tienes avisos pendientes.</p>
                @endforelse
            </section>
            <section class="overview-section overview-shortcuts" aria-labelledby="accesos-titulo">
                <div class="overview-section-heading"><h2 id="accesos-titulo">Accesos directos</h2></div>
                <a href="{{ route('eventos.index') }}"><x-icono nombre="calendar" /> Eventos</a>
                <a href="{{ route('biblioteca.index') }}"><x-icono nombre="book" /> Biblioteca digital</a>
                @unless ($esMiembro)
                    <a href="{{ route('solicitudes.index') }}"><x-icono nombre="users" /> Solicitudes</a>
                    <a href="{{ route('miembros.index') }}"><x-icono nombre="users" /> Miembros</a>
                @endunless
                @if ($esDistrital) <a href="{{ route('publicaciones.index') }}"><x-icono nombre="book" /> Noticias y contenido</a> @endif
                @if ($esMiembro) <a href="{{ route('perfil') }}"><x-icono nombre="users" /> Mi perfil</a> @endif
            </section>
            <section class="overview-account" aria-labelledby="cuenta-titulo"><h2 id="cuenta-titulo">Tu cuenta</h2>
                <strong>{{ $usuario->nombre }} {{ $usuario->apellido }}</strong>
                <span>{{ $esDistrital ? 'Dirigente distrital' : ($esMiembro ? 'Miembro' : 'Dirigente de grupo') }}</span>
                @if ($usuario->grupo) <p>{{ $usuario->grupo->nombre }}</p> @endif
                @if ($usuario->miembro?->rama) <p>Rama {{ $usuario->miembro->rama->nombre }}</p> @endif
            </section>
        </aside>
    </div>
@endsection
