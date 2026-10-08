<section class="panel-hero">
    <h2>
        {{ $rol === 'familia' ? 'Hola, ' : 'Siempre listos, ' }}
        {{ $usuario->nombre }}
        .
    </h2>
    <p>
        {{ $rol === 'familia' ? 'Acompañar también es parte de la aventura.' : 'Una mirada a tu comunidad scout.' }}
    </p>
</section>
<div class="grid four kpis">
    <article class="kpi">
        <strong>
            {{ $miembros->count() }}
        </strong>
        <span>
            Miembros
        </span>
    </article>
    <article class="kpi">
        <strong>
            {{ $miembros->where('activo', true)->count() }}
        </strong>
        <span>
            Miembros activos
        </span>
    </article>
    <article class="kpi">
        <strong>
            {{ $asistencias->count()
                ? round($asistencias->where('estado', 'presente')->count() / $asistencias->count() * 100) : 0 }}
            %
        </strong>
        <span>
            Asistencia registrada
        </span>
    </article>
    <article class="kpi">
        <strong>
            {{ $autorizaciones->where('estado', 'pendiente')->count() }}
        </strong>
        <span>
            Autorizaciones pendientes
        </span>
    </article>
</div>
@if (in_array($rol, ['administrador', 'coordinador']))
    <div class="grid two">
        <section class="panel">
            <h2>
                Miembros por rama
            </h2>
            @foreach ($ramas as $rama)
                <div class="chart-row">
                    <span>
                        {{ $rama->nombre }}
                    </span>
                    <div class="chart-track">
                        <div
                            class="chart-bar"
                        style="width:{{ $miembros->where('rama_id', $rama->id)->count() / max(1, $miembros->count()) * 100 }}%">
                        </div>
                    </div>
                    <strong>
                        {{ $miembros->where('rama_id', $rama->id)->count() }}
                    </strong>
                </div>
            @endforeach
        </section>
        <section class="panel">
            <h2>
                Accesos rápidos
            </h2>
            <div class="stack">
                @if ($rol === 'administrador')
                    <a class="btn" href="{{ route('admin.gestion.registros.create', ['recurso' => 'noticias']) }}">
                        Nueva noticia
                    </a>
                    <a
                        class="btn secondary"
                        href="{{ route('admin.gestion.registros.create', ['recurso' => 'miembros']) }}">
                        Nuevo miembro
                    </a>
                    <a class="btn secondary" href="{{ route('admin.usuarios') }}">
                        Gestionar usuarios y roles
                    </a>
                @else
                    <a class="btn" href="{{ route('coordinador.inscripciones') }}">
                        Revisar inscripciones
                    </a>
                    <a class="btn secondary" href="{{ route('coordinador.reportes') }}">
                        Ver reportes
                    </a>
                @endif
            </div>
        </section>
    </div>
@elseif ($rol === 'familia')
    <div class="actions">
        <a class="btn" href="{{ route('familia.inscripcion') }}">
            Inscribir a un hijo/a
        </a>
        <a class="btn secondary" href="{{ route('familia.hijos') }}">
            Vincular hijo/a
        </a>
    </div>
    @include('panel.hijos')
@endif
<div class="grid two">
    <section class="panel">
        <h2>
            Próximas actividades
        </h2>
        @include('panel.lista-actividades', ['listaActividades' => $actividades->filter(fn ($actividad) =>
            $actividad->fecha->endOfDay()->isFuture())->take(4)])
    </section>
    <section class="panel">
        <h2>
            Noticias recientes
        </h2>
        @foreach ($noticias as $noticia)
            <a class="list-item" href="{{ route('noticias.show', $noticia) }}">
                <strong>
                    {{ $noticia->titulo }}
                </strong>
            </a>
        @endforeach
    </section>
</div>
@if (in_array($rol, ['familia', 'coordinador']))
    <section class="panel">
        <h2>
            Solicitudes de inscripción
        </h2>
        @foreach ($inscripciones as $inscripcion)
            <article class="list-item">
                <strong>
                    {{ $inscripcion->nombre_aspirante }}
                    {{ $inscripcion->apellido_aspirante }}
                </strong>
                <span>
                    {{ $inscripcion->estado }}
                </span>
                <p>
                    {{ $inscripcion->motivo_rechazo }}
                </p>
            </article>
        @endforeach
    </section>
@endif
