@extends('layouts.panel', ['titulo' => 'Detalle del evento'])
@section('contenido')
    @php($operativo = $evento->estadoOperativo())
    <div class="detail-nav">
        <a class="text-link" href="{{ route('eventos.index') }}">← Volver a eventos</a>
        <div class="inline-actions">
            @can('update', $evento)
                <a class="btn secondary" href="{{ route('eventos.edit', $evento) }}">Editar evento</a>
                <form method="POST" action="{{ route('eventos.cancelar-evento', $evento) }}"
                    data-confirmar="¿Cancelar este evento? Las inscripciones quedarán cerradas.">
                    @csrf
                    <button class="btn danger">Cancelar evento</button>
                </form>
            @endcan
        </div>
    </div>

    <section class="event-detail-hero card">
        <x-imagen :ruta="$evento->imagen_url" :descripcion="$evento->titulo" clase="event-detail-image" />
        <div class="event-detail-heading">
            <div class="event-badges">
                <x-estado :valor="$evento->estado" />
                <span class="status {{ $operativo['clave'] }}">{{ $operativo['etiqueta'] }}</span>
            </div>
            <p class="eyebrow">{{ strtoupper(str_replace('_', ' ', $evento->tipo)) }}</p>
            <h2>{{ $evento->titulo }}</h2>
            <p>{{ $evento->descripcion }}</p>
            <div class="event-branches">
                @foreach ($evento->ramas as $rama)
                    <span class="branch-chip">{{ $rama->nombre }}</span>
                @endforeach
            </div>
        </div>
    </section>

    <div class="event-stats">
        <article class="card"><span>Inicio</span><strong>{{ $evento->fecha_inicio->translatedFormat('d M Y') }}</strong><small>{{ $evento->fecha_inicio->format('H:i') }}</small></article>
        <article class="card"><span>Participantes</span><strong>{{ $evento->cantidadInscritos() }}{{ $evento->cupo ? '/'.$evento->cupo : '' }}</strong><small>{{ $evento->cupo ? 'cupos ocupados' : 'sin límite de cupo' }}</small></article>
        <article class="card"><span>Costo</span><strong>{{ (float) $evento->costo > 0 ? 'Bs '.number_format((float) $evento->costo, 2) : 'Gratuito' }}</strong><small>por participante</small></article>
        <article class="card"><span>Inscripción</span><strong>{{ $operativo['etiqueta'] }}</strong><small>{{ $evento->cierreInscripcion()->translatedFormat('d M · H:i') }}</small></article>
    </div>

    <div class="event-detail-layout">
        <div>
            <article class="card event-section">
                <h3>Fecha y lugar</h3>
                <dl class="event-data-list">
                    <div><dt>Comienza</dt><dd>{{ $evento->fecha_inicio->translatedFormat('l d \d\e F \d\e Y, H:i') }}</dd></div>
                    <div><dt>Finaliza</dt><dd>{{ $evento->fecha_fin->translatedFormat('l d \d\e F \d\e Y, H:i') }}</dd></div>
                    <div><dt>Lugar</dt><dd>{{ $evento->lugar }}</dd></div>
                    @if ($evento->mapa_url)<div><dt>Mapa</dt><dd><a href="{{ $evento->mapa_url }}" target="_blank" rel="noopener">Abrir ubicación ↗</a></dd></div>@endif
                </dl>
            </article>

            @if ($evento->que_llevar || $evento->indicaciones)
                <article class="card event-section">
                    <h3>Antes de asistir</h3>
                    @if ($evento->que_llevar)<h4>Qué llevar</h4><p class="pre-line">{{ $evento->que_llevar }}</p>@endif
                    @if ($evento->indicaciones)<h4>Indicaciones</h4><p class="pre-line">{{ $evento->indicaciones }}</p>@endif
                </article>
            @endif

            @if ($evento->requiere_autorizacion)
                <article class="card event-section authorization-note">
                    <h3>Autorización requerida</h3>
                    <p class="pre-line">{{ $evento->instrucciones_autorizacion }}</p>
                </article>
            @endif
        </div>

        <aside>
            <article class="card registration-card">
                <h3>Inscripción</h3>
                <p class="event-availability {{ $operativo['clave'] }}">{{ $operativo['mensaje'] }}</p>
                <dl class="event-data-list compact">
                    <div><dt>Apertura</dt><dd>{{ $evento->inscripcion_desde?->translatedFormat('d M Y · H:i') ?? 'Al publicarse' }}</dd></div>
                    <div><dt>Cierre</dt><dd>{{ $evento->cierreInscripcion()->translatedFormat('d M Y · H:i') }}</dd></div>
                </dl>

                @if ($usuario->es('miembro'))
                    @if ($inscripcion?->estado === 'inscrito')
                        <p class="registration-confirmed">✓ Tu inscripción está confirmada.</p>
                        @if ($evento->fecha_inicio->isFuture())
                            <form method="POST" action="{{ route('eventos.cancelar', $evento) }}"
                                data-confirmar="¿Cancelar tu inscripción?">
                                @csrf @method('DELETE')
                                <button class="btn secondary full">Cancelar inscripción</button>
                            </form>
                        @endif
                    @elseif ($motivoNoInscripcion)
                        <p class="event-reason">{{ $motivoNoInscripcion }}</p>
                        <button class="btn full" disabled>No disponible</button>
                    @else
                        <form method="POST" action="{{ route('eventos.inscribir', $evento) }}">
                            @csrf
                            <button class="btn full">Confirmar inscripción</button>
                        </form>
                    @endif
                @else
                    <p><strong>{{ $evento->cantidadInscritos() }}</strong> miembros inscritos hasta el momento.</p>
                @endif
            </article>

            <article class="card event-section organizer-card">
                <h3>Organización</h3>
                <p><strong>{{ $evento->creador->nombre }} {{ $evento->creador->apellido }}</strong></p>
                <p class="muted">{{ $evento->creador->grupo?->nombre ?? 'Distrito Scout La Paz' }}</p>
                @if ($evento->motivo_rechazo)<p class="event-reason"><strong>Motivo:</strong> {{ $evento->motivo_rechazo }}</p>@endif
            </article>

            @if ($evento->estado === 'propuesto' && ! $usuario->es('miembro'))
                <article class="card event-section review-card">
                    <h3>Revisar propuesta</h3>
                    <form method="POST" action="{{ route('eventos.aprobar', $evento) }}">
                        @csrf
                        <button class="btn full">Aprobar y publicar</button>
                    </form>
                    <form method="POST" action="{{ route('eventos.rechazar', $evento) }}">
                        @csrf
                        <label>Motivo del rechazo<textarea name="motivo" required></textarea></label>
                        <button class="btn secondary full">Rechazar propuesta</button>
                    </form>
                </article>
            @endif
        </aside>
    </div>

    @if ($puedeGestionar)
        <section class="event-attendees">
            <div class="section-head">
                <div><p class="eyebrow">CONTROL DE PARTICIPACIÓN</p><h2>Miembros inscritos</h2></div>
                <span class="status inscrito">{{ $evento->cantidadInscritos() }} confirmados</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Miembro</th><th>Grupo</th><th>Rama</th><th>Contacto</th><th>Fecha de inscripción</th></tr></thead>
                    <tbody>
                        @forelse ($evento->inscripciones->where('estado', 'inscrito') as $registro)
                            <tr>
                                <td><strong>{{ $registro->miembro->nombre }} {{ $registro->miembro->apellido }}</strong></td>
                                <td>{{ $registro->miembro->grupo?->nombre ?? '—' }}</td>
                                <td>{{ $registro->miembro->rama?->nombre ?? '—' }}</td>
                                <td>{{ $registro->miembro->telefono ?: ($registro->miembro->email ?: 'Sin dato') }}</td>
                                <td>{{ $registro->inscrito_en->translatedFormat('d M Y · H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5">Todavía no hay miembros inscritos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif
@endsection
