@extends('layouts.panel', ['titulo' => 'Noticias y contenido'])
@section('contenido')
    <header class="publishing-heading">
        <div>
            <h2>Publicaciones del distrito</h2>
            <p>Prepara noticias para el sitio, elige las historias de portada y mantén actualizada la página Nosotros.</p>
        </div>
        <a class="btn secondary" href="{{ route('noticias.index') }}" target="_blank" rel="noopener">Ver noticias públicas</a>
    </header>

    <nav class="publishing-jump" aria-label="Secciones de contenido">
        <a href="#publicaciones">Publicaciones</a>
        <a href="#contenido-institucional">Contenido institucional</a>
        <a href="#categorias">Categorías</a>
    </nav>

    <details class="publication-composer" id="nueva-publicacion" @if(old('formulario') === 'crear' && $errors->any()) open @endif>
        <summary>
            <span><strong>Nueva publicación</strong><small>Escribe una noticia y guárdala como borrador o publícala.</small></span>
            <span class="publication-composer-action">Crear publicación</span>
        </summary>
        <div class="publication-composer-body">
            @include('panel.partials.form-noticia', ['noticia' => null])
        </div>
    </details>

    <section id="publicaciones" class="publication-section" aria-labelledby="publicaciones-titulo">
        <div class="publication-section-heading">
            <div>
                <h2 id="publicaciones-titulo">Publicaciones</h2>
                <p>{{ $noticias->total() }} {{ $noticias->total() === 1 ? 'resultado' : 'resultados' }} en esta vista</p>
            </div>
        </div>
        <nav class="publication-status-filters" aria-label="Filtrar por estado">
            @php($parametrosBase = array_filter(['buscar' => $buscar, 'categoria' => $categoriaFiltro], fn ($valor) => $valor !== null && $valor !== ''))
            @foreach (['' => 'Todas', 'publicado' => 'Publicadas', 'borrador' => 'Borradores', 'archivado' => 'Archivadas'] as $clave => $etiqueta)
                @php($cantidad = $clave === '' ? $conteos->sum() : (int) ($conteos[$clave] ?? 0))
                <a href="{{ route('publicaciones.index', $parametrosBase + ($clave !== '' ? ['estado' => $clave] : [])) }}"
                    @class(['active' => $estadoFiltro === $clave])>
                    {{ $etiqueta }} <span>{{ $cantidad }}</span>
                </a>
            @endforeach
        </nav>

        <form method="GET" action="{{ route('publicaciones.index') }}" class="publication-filters">
            @if ($estadoFiltro !== '') <input type="hidden" name="estado" value="{{ $estadoFiltro }}"> @endif
            <div class="field">
                <label for="buscar-publicacion">Buscar por título o resumen</label>
                <input id="buscar-publicacion" name="buscar" type="search" maxlength="100" value="{{ $buscar }}" placeholder="Buscar publicación">
            </div>
            <div class="field">
                <label for="categoria-publicacion">Categoría</label>
                <select id="categoria-publicacion" name="categoria">
                    <option value="">Todas las categorías</option>
                    @foreach ($categorias as $categoria)
                        <option value="{{ $categoria->id }}" @selected($categoriaFiltro === (string) $categoria->id)>{{ $categoria->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn secondary" type="submit">Aplicar filtros</button>
            @if ($buscar !== '' || $categoriaFiltro !== '')
                <a class="publication-clear" href="{{ route('publicaciones.index', $estadoFiltro !== '' ? ['estado' => $estadoFiltro] : []) }}">Limpiar</a>
            @endif
        </form>

        @if ($noticias->isNotEmpty())
            <div class="publication-list">
                @foreach ($noticias as $noticia)
                    <details class="publication-entry" @if(old('formulario') === 'editar-'.$noticia->id && $errors->any()) open @endif>
                        <summary>
                            <span class="publication-initial" aria-hidden="true">{{ mb_strtoupper(mb_substr($noticia->titulo, 0, 1)) }}</span>
                            <span class="publication-entry-main">
                                <strong>{{ $noticia->titulo }}</strong>
                                <small>{{ $noticia->categoria?->nombre ?? 'Sin categoría' }} · Actualizada {{ $noticia->updated_at->translatedFormat('d M Y') }}</small>
                            </span>
                            <span class="publication-entry-status">
                                <x-estado :valor="$noticia->estado" />
                                @if ($noticia->estado === 'publicado' && $noticia->destacada)
                                    <span class="publication-featured-tag">En inicio</span>
                                @endif
                            </span>
                            <span class="publication-entry-toggle">{{ $noticia->estado === 'archivado' ? 'Ver archivo' : 'Editar' }}</span>
                        </summary>
                        <div class="publication-entry-body">
                            @if ($noticia->estado === 'archivado')
                                <p class="publication-archive-note">Esta publicación no aparece en el sitio. Restáurala como borrador para volver a editarla.</p>
                                <form method="POST" action="{{ route('publicaciones.noticias.restaurar', $noticia) }}"
                                    data-confirmar="La publicación volverá a borradores y podrás editarla antes de publicarla.">
                                    @csrf
                                    <button class="btn secondary" type="submit">Restaurar como borrador</button>
                                </form>
                            @else
                                @if ($noticia->estado === 'publicado')
                                    <a class="publication-preview-link" href="{{ route('noticias.show', $noticia) }}" target="_blank" rel="noopener">Ver publicación en el sitio</a>
                                @endif
                                @include('panel.partials.form-noticia', ['noticia' => $noticia])
                                <form method="POST" action="{{ route('publicaciones.noticias.destroy', $noticia) }}"
                                    class="publication-archive-action" data-confirmar="La noticia dejará de aparecer en el sitio público. Podrás restaurarla como borrador."
                                    data-confirmar-variante="peligro">
                                    @csrf @method('DELETE')
                                    <button class="btn danger" type="submit">Archivar publicación</button>
                                </form>
                            @endif
                        </div>
                    </details>
                @endforeach
            </div>
            <div class="pagination">{{ $noticias->links() }}</div>
        @else
            <div class="publication-empty">
                <h3>{{ $totalPublicaciones ? 'No hay publicaciones para estos filtros' : 'Todavía no hay publicaciones' }}</h3>
                <p>{{ $totalPublicaciones ? 'Prueba con otra búsqueda o cambia el estado.' : 'Abre “Nueva publicación” para preparar la primera noticia del distrito.' }}</p>
                @if ($totalPublicaciones) <a class="btn secondary" href="{{ route('publicaciones.index') }}">Ver todas</a> @endif
            </div>
        @endif
    </section>

    <section id="contenido-institucional" class="publication-section" aria-labelledby="institucional-titulo">
        <div class="publication-section-heading">
            <div>
                <h2 id="institucional-titulo">Contenido institucional</h2>
                <p>Estos textos aparecen en la página Nosotros.</p>
            </div>
            <a class="text-link" href="{{ route('nosotros') }}" target="_blank" rel="noopener">Ver página Nosotros</a>
        </div>
        <div class="institutional-grid">
            @foreach ($contenidos as $contenido)
                @php($recuperarContenido = old('formulario') === 'contenido-'.$contenido->id)
                <form method="POST" action="{{ route('publicaciones.contenido.update', $contenido) }}" class="institutional-editor">
                    @csrf @method('PUT')
                    <input type="hidden" name="formulario" value="contenido-{{ $contenido->id }}">
                    <p class="institutional-key">{{ match ($contenido->clave) { 'mision' => 'Misión', 'vision' => 'Visión', default => ucfirst($contenido->clave) } }}</p>
                    <div class="field">
                        <label for="titulo-contenido-{{ $contenido->id }}">Título</label>
                        <input id="titulo-contenido-{{ $contenido->id }}" name="titulo" maxlength="180" required
                            value="{{ $recuperarContenido ? old('titulo', $contenido->titulo) : $contenido->titulo }}">
                    </div>
                    <div class="field">
                        <label for="texto-contenido-{{ $contenido->id }}">Texto</label>
                        <textarea id="texto-contenido-{{ $contenido->id }}" name="contenido" rows="7" maxlength="20000" required>{{ $recuperarContenido ? old('contenido', $contenido->contenido) : $contenido->contenido }}</textarea>
                    </div>
                    <div class="institutional-footer">
                        <small>Última actualización: {{ $contenido->updated_at->translatedFormat('d M Y') }}</small>
                        <button class="btn secondary" type="submit">Guardar texto</button>
                    </div>
                </form>
            @endforeach
        </div>
    </section>

    <section id="categorias" class="publication-section" aria-labelledby="categorias-titulo">
        <div class="publication-section-heading">
            <div>
                <h2 id="categorias-titulo">Categorías</h2>
                <p>Organiza las noticias para que la comunidad pueda encontrarlas.</p>
            </div>
        </div>
        <form method="POST" action="{{ route('publicaciones.categorias.store') }}" class="publication-category-form">
            @csrf
            <input type="hidden" name="formulario" value="categoria">
            <div class="field">
                <label for="nombre-categoria">Nombre de la categoría</label>
                <input id="nombre-categoria" name="nombre" maxlength="80" required
                    value="{{ old('formulario') === 'categoria' ? old('nombre') : '' }}" placeholder="Por ejemplo, servicio comunitario">
            </div>
            <button class="btn secondary" type="submit">Crear categoría</button>
        </form>
        <div class="publication-categories">
            @foreach ($categorias as $categoria)
                <span>{{ $categoria->nombre }} <small>{{ $categoria->noticias_por_categoria_id_count }} noticias</small></span>
            @endforeach
        </div>
    </section>
@endsection
