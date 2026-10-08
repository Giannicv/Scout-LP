@extends('layouts.panel')
@section('contenido')
    <div class="section-head admin-intro">
        <div>
            <p class="eyebrow">
                SCOUTS DE BOLIVIA
            </p>
            <h2>
                Siempre listos para acompañar.
            </h2>
            <p class="muted">
                Una mirada a tu comunidad scout.
            </p>
        </div>
    </div>
    <div class="grid four kpis">
        @foreach (['total' => 'Total de miembros', 'activos' => 'Miembros activos',
            'noticias' => 'Noticias publicadas', 'mensajes' => 'Mensajes nuevos'] as $clave => $etiqueta)
            <article class="kpi">
                <strong>
                    {{ $estadisticas[$clave] }}
                </strong>
                <span>
                    {{ $etiqueta }}
                </span>
            </article>
        @endforeach
    </div>
    <div class="grid two">
        <section class="panel">
            <h2>
                Miembros por rama
            </h2>
            @php($maximoRama = max(1, $miembros->groupBy('rama_id')->map->count()->max()))
            @foreach ($ramas as $rama)
                <div class="chart-row">
                    <span>
                        {{ $rama->nombre }}
                    </span>
                    <div class="chart-track">
                        <div
                            class="chart-bar" style="width:{{ $miembros->where('rama_id', $rama->id)->count() / $maximoRama * 100 }}%">
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
            <p class="muted">
                Comparte una historia o da la bienvenida a alguien nuevo.
            </p>
            <div class="actions">
                @foreach (['noticias' => 'Nueva noticia', 'eventos' => 'Nuevo evento', 'miembros' => 'Nuevo miembro'] as $recurso => $etiqueta)
                    <a
                        class="btn secondary small"
                        href="{{ route('admin.gestion.registros.create', ['recurso' => $recurso]) }}">
                        {{ $etiqueta }}
                    </a>
                @endforeach
            </div>
        </section>
        <section class="panel">
            <h2>
                Últimos mensajes recibidos
            </h2>
            @forelse ($estadisticas['ultimos_mensajes'] as $mensaje)
                <a
                    class="list-item"
                    href="{{ route('admin.gestion.registros.edit', ['recurso' => 'mensajes', 'registro' => $mensaje->id]) }}">
                    <strong>
                        {{ $mensaje->nombre }}
                        <span class="tag">
                            {{ $mensaje->estado }}
                        </span>
                    </strong>
                    <span>
                        {{ $mensaje->asunto }}
                        ·
                        {{ $mensaje->creado_en->format('d/m/Y') }}
                    </span>
                </a>
            @empty
                <p class="empty">
                    No hay mensajes todavía.
                </p>
            @endforelse
        </section>
        <section class="panel">
            <h2>
                Próximos eventos
            </h2>
            @forelse ($estadisticas['eventos'] as $evento)
                <a class="list-item" href="{{ route('admin.eventos') }}">
                    <strong>
                        {{ $evento->titulo }}
                    </strong>
                    <span>
                        {{ $evento->fecha_inicio->format('d/m/Y') }}
                        ·
                        {{ $evento->lugar }}
                    </span>
                </a>
            @empty
                <p class="empty">
                    No hay próximos eventos.
                </p>
            @endforelse
        </section>
    </div>
    <section class="panel">
        <h2>
            Usuarios por rol
        </h2>
        <div class="grid four">
            @foreach ($estadisticas['roles'] as $estadisticaRol)
                <div class="kpi">
                    <strong>
                        {{ $estadisticaRol->total }}
                    </strong>
                    <span>
                        {{ ucfirst($estadisticaRol->nombre) }}
                    </span>
                </div>
            @endforeach
        </div>
        <p>
            <a href="{{ route('admin.usuarios') }}">
                Gestionar usuarios y roles
            </a>
        </p>
    </section>
@endsection
