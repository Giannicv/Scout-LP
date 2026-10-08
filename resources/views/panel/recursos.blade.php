<div class="section-head admin-intro">
    <div>
        <p class="eyebrow">
            SCOUTS DE BOLIVIA
        </p>
        <h2>
            {{ $configuracion['titulo'] }}
        </h2>
    </div>
    <div class="actions">
        @unless (in_array($pagina, ['ramas', 'contenido', 'mensajes']))
            <a class="btn" href="{{ route($rolRuta.'.gestion.registros.create', ['recurso' => $pagina]) }}">
                + Agregar
                {{ $pagina === 'noticias' ? 'noticia' : 'registro' }}
            </a>
        @endunless
        @if ($pagina === 'miembros')
            <a class="btn secondary" href="{{ route($rolRuta.'.exportar', request()->query()) }}">
                Exportar CSV
            </a>
        @endif
    </div>
</div>
@if ($pagina === 'miembros')
    @include('panel.filtros-miembros')
@endif
<div class="table-scroll">
    <table>
        <thead>
            <tr>
                <th>
                    Registro
                </th>
                <th>
                    Detalle
                </th>
                <th>
                    Estado
                </th>
                <th>
                    Acciones
                </th>
            </tr>
        </thead>
        <tbody>
            @forelse ($registros as $registro)
                <tr>
                    <td>
                        <strong>
                            {{ $registro->titulo ?? $registro->nombre ?? $registro->clave }}
                        </strong>
                        @if ($registro->apellido)
                            <span>
                                {{ $registro->apellido }}
                            </span>
                        @endif
                        @if ($pagina === 'noticias')
                            <x-imagen :ruta="$registro->imagen_url" :descripcion="$registro->titulo" clase="thumb" />
                        @endif
                    </td>
                    <td>
                        @if ($pagina === 'miembros')
                            {{ $registro->rama?->nombre }}
                            ·
                            {{ $registro->grupo?->nombre }}
                            <br>
                            {{ $registro->fecha_nacimiento?->age }}
                            años ·
                            {{ $registro->grupo?->distrito?->departamento }}
                        @elseif ($pagina === 'usuarios')
                            {{ $registro->email }}
                            ·
                            {{ $registro->rol?->nombre }}
                        @elseif ($pagina === 'eventos')
                            {{ $registro->fecha_inicio?->format('d/m/Y H:i') }}
                            ·
                            {{ $registro->lugar }}
                        @else
                            {{ Str::limit($registro->resumen ?? $registro->descripcion ?? $registro->contenido
                                ?? $registro->asunto ?? $registro->lema ?? '', 160) }}
                        @endif
                    </td>
                    <td>
                        <span class="tag">
                            {{ $registro->estado ?? (isset($registro->activo)
                                ? ($registro->activo ? 'Activo' : 'Inactivo') : '—') }}
                        </span>
                    </td>
                    <td>
                        <div class="actions">
                            <a
                                class="btn secondary small"
                                href="{{ route($rolRuta.'.gestion.registros.edit',
                        ['recurso' => $pagina, 'registro' => $registro->id]) }}">
                                Editar
                            </a>
                            @unless (in_array($pagina, ['ramas', 'contenido']))
                                <form method="POST"
                                    data-confirmar="¿Eliminar este registro y sus datos relacionados?"
                                    action="{{ route($rolRuta.'.gestion.registros.destroy',
                                ['recurso' => $pagina, 'registro' => $registro->id]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="danger small">
                                        Eliminar
                                    </button>
                                </form>
                            @endunless
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="empty">
                        No hay registros para mostrar.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<nav class="pagination" aria-label="Paginación">
    @if ($registros->previousPageUrl())
        <a class="btn secondary" href="{{ $registros->previousPageUrl() }}">
            Anterior
        </a>
    @endif
    <span>
        {{ $registros->currentPage() }}
        de
        {{ $registros->lastPage() }}
        ·
        {{ $registros->total() }}
        registros
    </span>
    @if ($registros->nextPageUrl())
        <a class="btn secondary" href="{{ $registros->nextPageUrl() }}">
            Siguiente
        </a>
    @endif
</nav>
