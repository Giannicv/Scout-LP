@extends('layouts.panel', ['titulo' => 'Miembros'])
@section('contenido')
    <section class="members-heading" aria-labelledby="members-title">
        <div>
            <h2 id="members-title">Directorio de miembros</h2>
            <p>{{ $usuario->es('dirigente_distrital')
                ? 'Consulta los miembros de los grupos del distrito y el estado de sus cuentas.'
                : 'Consulta los miembros de tu grupo y sus contactos registrados.' }}</p>
        </div>
        <p class="members-result-count"><strong>{{ $miembros->total() }}</strong> de {{ $totalMiembros }} miembros</p>
    </section>

    <form method="GET" action="{{ route('miembros.index') }}" class="members-filter" aria-label="Filtrar miembros">
        <div class="field members-search">
            <label for="buscar-miembro">Buscar miembro</label>
            <input id="buscar-miembro" type="search" name="buscar" value="{{ $buscar }}"
                placeholder="Nombre completo o correo" maxlength="100">
        </div>
        <div class="field">
            <label for="rama-miembro">Rama</label>
            <select id="rama-miembro" name="rama">
                <option value="">Todas las ramas</option>
                @foreach ($ramas as $rama)
                    <option value="{{ $rama->id }}" @selected(request('rama') == $rama->id)>{{ $rama->nombre }}</option>
                @endforeach
            </select>
        </div>
        @if ($usuario->es('dirigente_distrital'))
            <div class="field">
                <label for="grupo-miembro">Grupo</label>
                <select id="grupo-miembro" name="grupo">
                    <option value="">Todos los grupos</option>
                    @foreach ($grupos as $grupo)
                        <option value="{{ $grupo->id }}" @selected(request('grupo') == $grupo->id)>{{ $grupo->nombre }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <div class="field">
            <label for="estado-miembro">Cuenta</label>
            <select id="estado-miembro" name="estado">
                <option value="">Todos los estados</option>
                @foreach (['activo' => 'Activa', 'suspendido' => 'Suspendida', 'pendiente' => 'Pendiente', 'rechazado' => 'Rechazada', 'sin_cuenta' => 'Sin cuenta'] as $valor => $etiqueta)
                    <option value="{{ $valor }}" @selected(request('estado') === $valor)>{{ $etiqueta }}</option>
                @endforeach
            </select>
        </div>
        <div class="members-filter-actions">
            <button class="btn secondary" type="submit">Aplicar filtros</button>
            @if ($buscar !== '' || request()->filled('rama') || request()->filled('grupo') || request()->filled('estado'))
                <a href="{{ route('miembros.index') }}">Limpiar</a>
            @endif
        </div>
    </form>

    @if ($miembros->isNotEmpty())
        <div class="members-list" aria-label="Listado de miembros">
            @foreach ($miembros as $miembro)
                <details class="member-entry">
                    <summary class="member-summary">
                        <span class="member-identity">
                            <strong>{{ $miembro->nombre }} {{ $miembro->apellido }}</strong>
                            <small>{{ $miembro->usuario?->email ?? $miembro->email ?? 'Sin correo registrado' }}</small>
                        </span>
                        <span class="member-summary-field"><small>Grupo</small><span>{{ $miembro->grupo?->nombre ?? 'Sin grupo' }}</span></span>
                        <span class="member-summary-field"><small>Rama</small><span>{{ $miembro->rama?->nombre ?? 'Sin rama' }}</span></span>
                        <span class="member-summary-field"><small>Cuenta</small>
                            @if ($miembro->usuario)
                                <x-estado :valor="$miembro->usuario->estado" />
                            @else
                                <span class="member-no-account">Sin cuenta</span>
                            @endif
                        </span>
                        <span class="member-expand"><span class="member-expand-open">Ver ficha</span><span class="member-expand-close">Cerrar ficha</span></span>
                    </summary>
                    <div class="member-detail">
                        <section>
                            <h3>Datos del miembro</h3>
                            <dl class="member-facts">
                                <div><dt>Correo</dt><dd>{{ $miembro->email ?? $miembro->usuario?->email ?? 'Sin dato' }}</dd></div>
                                <div><dt>Teléfono</dt><dd>{{ $miembro->telefono ?: 'Sin dato' }}</dd></div>
                                <div><dt>Fecha de nacimiento</dt><dd>{{ $miembro->fecha_nacimiento?->translatedFormat('d M Y') ?? 'Sin dato' }}</dd></div>
                            </dl>
                        </section>
                        <section>
                            <h3>Contactos registrados</h3>
                            @forelse ($miembro->contactos as $contacto)
                                <div class="member-contact">
                                    <strong>{{ $contacto->nombre }}</strong>
                                    <span>{{ $contacto->parentesco ?: 'Contacto' }}{{ $contacto->telefono ? ' · '.$contacto->telefono : '' }}</span>
                                </div>
                            @empty
                                <p class="muted">No hay contactos registrados.</p>
                            @endforelse
                        </section>
                        <section class="member-account">
                            <h3>Acceso al panel</h3>
                            @if ($miembro->usuario)
                                <p>Estado actual: <strong>{{ ucfirst($miembro->usuario->estado) }}</strong></p>
                                @can('updateStatus', $miembro->usuario)
                                    @php($suspender = $miembro->usuario->estado === 'activo')
                                    <form method="POST" action="{{ route('usuarios.estado', $miembro->usuario) }}"
                                        data-confirmar="{{ $suspender
                                            ? 'La cuenta de '.$miembro->nombre.' '.$miembro->apellido.' perderá acceso al panel. Podrás reactivarla más adelante.'
                                            : $miembro->nombre.' '.$miembro->apellido.' recuperará el acceso al panel.' }}"
                                        data-confirmar-variante="{{ $suspender ? 'peligro' : 'normal' }}">
                                        @csrf
                                        <input type="hidden" name="estado" value="{{ $suspender ? 'suspendido' : 'activo' }}">
                                        <button class="btn {{ $suspender ? 'danger' : '' }}" type="submit">{{ $suspender ? 'Suspender cuenta' : 'Reactivar cuenta' }}</button>
                                    </form>
                                @else
                                    @if ($miembro->usuario->estado === 'pendiente')
                                        <p class="muted">{{ $usuario->es('dirigente_grupo')
                                            ? 'Revisa esta cuenta en Solicitudes.'
                                            : 'La aprobación corresponde al dirigente del grupo.' }}</p>
                                    @elseif ($miembro->usuario->estado === 'rechazado')
                                        <p class="muted">Esta solicitud fue rechazada.</p>
                                    @endif
                                @endcan
                            @else
                                <p class="muted">Este miembro no tiene una cuenta vinculada.</p>
                            @endif
                        </section>
                    </div>
                </details>
            @endforeach
        </div>
        <div class="pagination">{{ $miembros->links() }}</div>
    @else
        <div class="members-empty">
            @if ($totalMiembros === 0)
                <h3>Aún no hay miembros registrados</h3>
                <p>Los miembros aparecerán aquí cuando soliciten acceso a un grupo.</p>
            @else
                <h3>No hay miembros para estos filtros</h3>
                <p>Prueba con otro nombre, rama o estado de cuenta.</p>
                <a class="btn secondary" href="{{ route('miembros.index') }}">Ver todos los miembros</a>
            @endif
        </div>
    @endif
@endsection
