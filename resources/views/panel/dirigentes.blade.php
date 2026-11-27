@extends('layouts.panel', ['titulo' => 'Dirigentes'])
@section('contenido')
    <header class="leaders-heading">
        <div>
            <h2>Dirigentes de grupo</h2>
            <p>Consulta quién tiene acceso a cada grupo y administra sus cuentas.</p>
        </div>
        <a class="btn" href="{{ route('dirigentes.create') }}">Crear dirigente</a>
    </header>

    <section class="leaders-overview" aria-label="Resumen de dirigentes">
        <div><strong>{{ $total }}</strong><span>Dirigentes registrados</span></div>
        <div><strong>{{ $activos }}</strong><span>Con acceso activo</span></div>
        <div><strong>{{ $pendientes }}</strong><span>Por revisar</span></div>
    </section>

    <section class="leaders-groups" aria-labelledby="leaders-groups-title">
        <div class="leaders-section-heading"><h3 id="leaders-groups-title">Cobertura por grupo</h3><span>Dirigentes activos</span></div>
        <div class="leaders-group-list">
            @forelse ($grupos as $grupo)
                <a href="{{ route('dirigentes.index', ['grupo' => $grupo->id]) }}" @class(['leaders-group', 'is-empty' => $grupo->dirigentes_activos_count === 0])>
                    <span>{{ $grupo->nombre }}</span><strong>{{ $grupo->dirigentes_activos_count }}</strong>
                </a>
            @empty
                <p>Aún no hay grupos registrados.</p>
            @endforelse
        </div>
    </section>

    <form class="leaders-filter" method="GET" action="{{ route('dirigentes.index') }}" aria-label="Filtrar dirigentes">
        <div class="field leaders-search"><label for="buscar-dirigente">Buscar dirigente</label>
            <input id="buscar-dirigente" type="search" name="buscar" value="{{ $buscar }}" maxlength="100" placeholder="Nombre o correo"></div>
        <div class="field"><label for="grupo-dirigente">Grupo</label>
            <select id="grupo-dirigente" name="grupo"><option value="">Todos los grupos</option>
                @foreach ($grupos as $grupo)<option value="{{ $grupo->id }}" @selected(request('grupo') == $grupo->id)>{{ $grupo->nombre }}</option>@endforeach
            </select></div>
        <div class="field"><label for="estado-dirigente">Estado</label>
            <select id="estado-dirigente" name="estado"><option value="">Todos los estados</option>
                @foreach (['activo' => 'Activo', 'suspendido' => 'Suspendido', 'pendiente' => 'Pendiente', 'rechazado' => 'Rechazado'] as $valor => $etiqueta)
                    <option value="{{ $valor }}" @selected(request('estado') === $valor)>{{ $etiqueta }}</option>
                @endforeach
            </select></div>
        <div class="leaders-filter-actions"><button class="btn secondary" type="submit">Aplicar filtros</button>
            @if ($buscar !== '' || request()->filled('grupo') || request()->filled('estado'))<a href="{{ route('dirigentes.index') }}">Limpiar</a>@endif
        </div>
    </form>

    <div class="leaders-list-heading"><h3>Directorio</h3><span>{{ $dirigentes->total() }} {{ $dirigentes->total() === 1 ? 'resultado' : 'resultados' }}</span></div>
    @forelse ($dirigentes as $dirigente)
        <article class="leader-row">
            <div class="leader-person"><span class="leader-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($dirigente->nombre, 0, 1).mb_substr($dirigente->apellido, 0, 1)) }}</span>
                <div><h4>{{ $dirigente->nombre }} {{ $dirigente->apellido }}</h4><p>{{ $dirigente->email }}</p></div>
            </div>
            <div class="leader-group"><small>Grupo asignado</small><strong>{{ $dirigente->grupo?->nombre ?? 'Sin grupo' }}</strong></div>
            <div class="leader-state"><small>Cuenta</small><x-estado :valor="$dirigente->estado" /></div>
            <div class="leader-actions">
                @can('updateLeader', $dirigente)
                    <a class="btn secondary small" href="{{ route('dirigentes.edit', $dirigente) }}">Editar</a>
                    @php($suspender = $dirigente->estado === 'activo')
                    <form method="POST" action="{{ route('dirigentes.estado', $dirigente) }}"
                        data-confirmar="{{ $suspender
                            ? 'La cuenta de '.$dirigente->nombre.' '.$dirigente->apellido.' perderá acceso al panel. Podrás reactivarla más adelante.'
                            : $dirigente->nombre.' '.$dirigente->apellido.' recuperará el acceso al panel.' }}"
                        data-confirmar-variante="{{ $suspender ? 'peligro' : 'normal' }}">
                        @csrf<input type="hidden" name="estado" value="{{ $suspender ? 'suspendido' : 'activo' }}">
                        <button class="btn small {{ $suspender ? 'danger' : 'secondary' }}" type="submit">{{ $suspender ? 'Suspender' : 'Reactivar' }}</button>
                    </form>
                @elseif ($dirigente->estado === 'pendiente')
                    <a href="{{ route('solicitudes.index', ['buscar' => $dirigente->email]) }}">Revisar solicitud</a>
                @else
                    <span class="muted">Solicitud rechazada</span>
                @endcan
            </div>
        </article>
    @empty
        <div class="leaders-empty"><h3>{{ $total === 0 ? 'Aún no hay dirigentes de grupo' : 'No hay resultados para estos filtros' }}</h3>
            <p>{{ $total === 0 ? 'Crea una cuenta y asígnala a un grupo para empezar.' : 'Prueba con otro nombre, grupo o estado.' }}</p>
            <a class="btn secondary" href="{{ $total === 0 ? route('dirigentes.create') : route('dirigentes.index') }}">{{ $total === 0 ? 'Crear dirigente' : 'Ver todos' }}</a>
        </div>
    @endforelse
    <div class="pagination">{{ $dirigentes->links() }}</div>
@endsection
