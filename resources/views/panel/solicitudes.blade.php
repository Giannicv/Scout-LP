@extends('layouts.panel', ['titulo' => 'Solicitudes de acceso'])
@section('contenido')
    <section class="requests-heading" aria-labelledby="requests-title">
        <div>
            <h2 id="requests-title">Cuentas por revisar</h2>
            <p>{{ $usuario->es('dirigente_distrital')
                ? 'Revisa las cuentas de dirigentes de grupo y miembros que solicitan acceso al distrito.'
                : 'Revisa a los miembros que solicitan acceso a tu grupo.' }}</p>
        </div>
        <div class="requests-count" aria-label="{{ $totalPendientes }} {{ $totalPendientes === 1 ? 'solicitud pendiente' : 'solicitudes pendientes' }}">
            <strong>{{ $totalPendientes }}</strong>
            <span>{{ $totalPendientes === 1 ? 'solicitud pendiente' : 'solicitudes pendientes' }}</span>
        </div>
    </section>

    <form method="GET" action="{{ route('solicitudes.index') }}" class="requests-filter" role="search">
        <label for="buscar-solicitud">Buscar solicitud</label>
        <div class="requests-filter-controls">
            <input id="buscar-solicitud" name="buscar" type="search" value="{{ $buscar }}"
                placeholder="Nombre, apellido o correo" maxlength="100" autocomplete="off">
            <button class="btn secondary" type="submit">Buscar</button>
            @if ($buscar !== '')
                <a class="requests-clear" href="{{ route('solicitudes.index') }}">Limpiar búsqueda</a>
            @endif
        </div>
    </form>

    @if ($solicitudes->isNotEmpty())
        <div class="requests-list">
            @foreach ($solicitudes as $solicitud)
                <article class="request-card" aria-labelledby="solicitud-{{ $solicitud->id }}">
                    <div class="request-main">
                        <div class="request-title-line">
                            <h3 id="solicitud-{{ $solicitud->id }}">{{ $solicitud->nombre }} {{ $solicitud->apellido }}</h3>
                            <x-estado :valor="$solicitud->estado" />
                        </div>
                        <p class="request-role">{{ $solicitud->es('dirigente_grupo') ? 'Dirigente de grupo' : 'Miembro' }}</p>
                        <dl class="request-facts">
                            <div>
                                <dt>Correo</dt>
                                <dd><a href="mailto:{{ $solicitud->email }}">{{ $solicitud->email }}</a></dd>
                            </div>
                            <div>
                                <dt>Grupo</dt>
                                <dd>{{ $solicitud->grupo?->nombre ?? 'Sin grupo asignado' }}</dd>
                            </div>
                            @if ($solicitud->miembro?->rama)
                                <div>
                                    <dt>Rama</dt>
                                    <dd>{{ $solicitud->miembro->rama->nombre }}</dd>
                                </div>
                            @endif
                            @if ($solicitud->miembro?->fecha_nacimiento)
                                <div>
                                    <dt>Fecha de nacimiento</dt>
                                    <dd>{{ $solicitud->miembro->fecha_nacimiento->translatedFormat('d M Y') }}</dd>
                                </div>
                            @endif
                            @if ($solicitud->miembro?->telefono)
                                <div>
                                    <dt>Teléfono</dt>
                                    <dd>{{ $solicitud->miembro->telefono }}</dd>
                                </div>
                            @endif
                            <div>
                                <dt>Recibida</dt>
                                <dd><time datetime="{{ $solicitud->created_at->toIso8601String() }}">{{ $solicitud->created_at->translatedFormat('d M Y, H:i') }}</time></dd>
                            </div>
                        </dl>
                    </div>
                    <div class="request-actions">
                        <form method="POST" action="{{ route('solicitudes.resolver', $solicitud) }}"
                            data-confirmar="La cuenta de {{ $solicitud->nombre }} {{ $solicitud->apellido }} tendrá acceso al panel.">
                            @csrf
                            <input type="hidden" name="decision" value="aprobar">
                            <button class="btn request-approve" type="submit"><x-icono nombre="check" /> Aprobar acceso</button>
                        </form>
                        <details class="request-reject" @if(old('solicitud_id') == $solicitud->id && $errors->any()) open @endif>
                            <summary>Rechazar solicitud</summary>
                            <form method="POST" action="{{ route('solicitudes.resolver', $solicitud) }}"
                                data-confirmar="La solicitud de {{ $solicitud->nombre }} {{ $solicitud->apellido }} se rechazará con el motivo que escribiste."
                                data-confirmar-variante="peligro">
                                @csrf
                                <input type="hidden" name="decision" value="rechazar">
                                <input type="hidden" name="solicitud_id" value="{{ $solicitud->id }}">
                                <label for="motivo-{{ $solicitud->id }}">Motivo del rechazo</label>
                                <textarea id="motivo-{{ $solicitud->id }}" name="motivo" rows="3" maxlength="500"
                                    placeholder="Explica qué debe corregir la persona" required>{{ old('solicitud_id') == $solicitud->id ? old('motivo') : '' }}</textarea>
                                @if (old('solicitud_id') == $solicitud->id)
                                    @error('motivo') <span class="request-field-error" role="alert">{{ $message }}</span> @enderror
                                @endif
                                <button class="btn danger" type="submit">Confirmar rechazo</button>
                            </form>
                        </details>
                    </div>
                </article>
            @endforeach
        </div>
        <div class="pagination">{{ $solicitudes->links() }}</div>
    @else
        <div class="requests-empty">
            @if ($buscar !== '')
                <h3>No hay coincidencias</h3>
                <p>No encontramos solicitudes pendientes para “{{ $buscar }}”. Prueba con otro nombre o correo.</p>
                <a class="btn secondary" href="{{ route('solicitudes.index') }}">Ver todas las solicitudes</a>
            @else
                <h3>No hay solicitudes pendientes</h3>
                <p>Cuando alguien solicite acceso, aparecerá aquí para que puedas revisarlo.</p>
            @endif
        </div>
    @endif
@endsection
