@extends('layouts.panel', ['titulo' => 'Eventos'])
@section('contenido')
    <div class="panel-intro">
        <div>
            <p class="eyebrow">AGENDA DEL DISTRITO</p>
            <h2>Actividades, encuentros y proyectos</h2>
            <p class="muted">Consulta la disponibilidad real, las ramas convocadas y todos los detalles antes de inscribirte.</p>
        </div>
        @if (! $usuario->es('miembro') || $usuario->miembro?->rama?->slug === 'rovers')
            <a class="btn" href="{{ route('eventos.create') }}">
                {{ $usuario->es('miembro') ? 'Proponer proyecto Rover' : 'Crear evento' }}
            </a>
        @endif
    </div>

    <form method="GET" class="panel-toolbar">
        <div class="field">
            <label for="buscar">Buscar</label>
            <input id="buscar" name="buscar" value="{{ request('buscar') }}" placeholder="Título o lugar">
        </div>
        <div class="field">
            <label for="estado">Publicación</label>
            <select id="estado" name="estado">
                <option value="">Todos los estados</option>
                @foreach (['borrador', 'propuesto', 'publicado', 'rechazado', 'cancelado', 'finalizado'] as $estado)
                    <option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ ucfirst($estado) }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="vigencia">Fecha</label>
            <select id="vigencia" name="vigencia">
                <option value="">Todos</option>
                <option value="proximos" @selected(request('vigencia') === 'proximos')>Próximos y en curso</option>
                <option value="finalizados" @selected(request('vigencia') === 'finalizados')>Finalizados</option>
            </select>
        </div>
        <button class="btn secondary">Aplicar filtros</button>
    </form>

    <div class="event-list">
        @forelse ($eventos as $evento)
            @php
                $operativo = $evento->estadoOperativo();
                $inscripcion = $usuario->es('miembro')
                    ? $evento->inscripciones->firstWhere('miembro_id', $usuario->miembro_id)
                    : null;
                $motivo = $usuario->es('miembro') && $inscripcion?->estado !== 'inscrito'
                    ? $evento->motivoNoInscripcion($usuario->miembro)
                    : null;
            @endphp
            <article class="event-card card">
                <a class="event-card-media" href="{{ route('eventos.show', $evento) }}" aria-label="Ver {{ $evento->titulo }}">
                    <x-imagen :ruta="$evento->imagen_url" :descripcion="$evento->titulo" />
                </a>
                <div class="event-card-content">
                    <div class="event-card-topline">
                        <div class="event-badges">
                            <x-estado :valor="$evento->estado" />
                            <span class="status {{ $operativo['clave'] }}">{{ $operativo['etiqueta'] }}</span>
                        </div>
                        <span class="event-type">{{ ucfirst(str_replace('_', ' ', $evento->tipo)) }}</span>
                    </div>
                    <h2><a href="{{ route('eventos.show', $evento) }}">{{ $evento->titulo }}</a></h2>
                    <div class="event-meta">
                        <span>📅 {{ $evento->fecha_inicio->translatedFormat('d M Y · H:i') }}</span>
                        <span>📍 {{ $evento->lugar }}</span>
                        <span>👥 {{ $evento->cantidadInscritos() }}{{ $evento->cupo ? '/'.$evento->cupo : ' inscritos' }}</span>
                        <span>💵 {{ (float) $evento->costo > 0 ? 'Bs '.number_format((float) $evento->costo, 2) : 'Sin costo' }}</span>
                    </div>
                    <div class="event-branches">
                        @foreach ($evento->ramas as $rama)
                            <span class="branch-chip">{{ $rama->nombre }}</span>
                        @endforeach
                    </div>
                    <p>{{ \Illuminate\Support\Str::limit($evento->descripcion, 190) }}</p>
                    <p class="event-availability {{ $operativo['clave'] }}">{{ $operativo['mensaje'] }}</p>
                    @if ($motivo)
                        <p class="event-reason">{{ $motivo }}</p>
                    @endif
                    <div class="inline-actions">
                        <a class="btn secondary" href="{{ route('eventos.show', $evento) }}">Ver detalles</a>
                        @if ($usuario->es('miembro') && $inscripcion?->estado === 'inscrito')
                            <span class="status inscrito">Ya estás inscrito</span>
                        @elseif ($usuario->es('miembro') && ! $motivo)
                            <form method="POST" action="{{ route('eventos.inscribir', $evento) }}">
                                @csrf
                                <button class="btn">Inscribirme</button>
                            </form>
                        @endif
                        @can('update', $evento)
                            <a class="btn secondary" href="{{ route('eventos.edit', $evento) }}">Editar</a>
                        @endcan
                    </div>
                </div>
            </article>
        @empty
            <div class="empty-state">
                <h2>No encontramos eventos</h2>
                <p>Ajusta los filtros o crea la primera actividad.</p>
            </div>
        @endforelse
    </div>
    {{ $eventos->links() }}
@endsection
