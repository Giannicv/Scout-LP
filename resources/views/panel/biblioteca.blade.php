@extends('layouts.panel', ['titulo' => 'Biblioteca digital'])
@section('contenido')
    <header class="library-heading">
        <div>
            <h2>Biblioteca del distrito</h2>
            <p>Explora guías, documentos y materiales para tu camino scout. Abre un recurso para conocerlo antes de descargarlo.</p>
        </div>
        <span class="library-heading-mark" aria-hidden="true"><x-icono nombre="book" /></span>
    </header>

    @if ($puedePublicar)
        <details class="library-composer" @if($errors->any()) open @endif>
            <summary><span><strong>Publicar un recurso</strong><small>Adjunta un documento y, si quieres, una imagen de portada.</small></span><span class="library-composer-action">Nuevo recurso</span></summary>
            <form method="POST" action="{{ route('biblioteca.store') }}" enctype="multipart/form-data" class="library-form">
                @csrf
                <div class="field library-form-wide">
                    <label for="recurso-titulo">Título</label>
                    <input id="recurso-titulo" name="titulo" maxlength="180" required value="{{ old('titulo') }}" placeholder="Nombre del material">
                </div>
                <div class="field library-form-wide">
                    <label for="recurso-descripcion">Descripción</label>
                    <textarea id="recurso-descripcion" name="descripcion" rows="4" maxlength="2000" placeholder="Explica brevemente qué encontrará quien lo abra">{{ old('descripcion') }}</textarea>
                </div>
                <div class="field">
                    <label for="recurso-categoria">Categoría</label>
                    <select id="recurso-categoria" name="categoria_recurso_id" required>
                        <option value="">Selecciona una categoría</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}" @selected((string) old('categoria_recurso_id') === (string) $categoria->id)>{{ $categoria->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="recurso-archivo">Documento</label>
                    <input id="recurso-archivo" type="file" name="archivo" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt" required>
                    <small>PDF, Word, Excel, PowerPoint o TXT. Máximo 15 MB.</small>
                </div>
                <div class="field library-form-wide">
                    <label for="recurso-imagen">Imagen de portada (opcional)</label>
                    <input id="recurso-imagen" type="file" name="imagen" accept="image/jpeg,image/png,image/webp">
                    <small>JPG, PNG o WebP. Máximo 5 MB. Si no adjuntas una, se mostrará una portada sencilla.</small>
                </div>
                <div class="library-form-actions"><button class="btn" type="submit">Publicar recurso</button></div>
            </form>
        </details>
    @endif

    <section class="library-catalog" aria-labelledby="catalogo-titulo">
        <div class="library-section-title">
            <div><h2 id="catalogo-titulo">{{ $estadoFiltro === 'archivado' ? 'Recursos archivados' : 'Explorar materiales' }}</h2>
                <p>{{ $recursos->total() }} {{ $recursos->total() === 1 ? 'recurso' : 'recursos' }} en esta vista</p></div>
            @if ($puedePublicar)
                <nav class="library-status" aria-label="Estado de los recursos">
                    <a href="{{ route('biblioteca.index') }}" @class(['active' => $estadoFiltro === 'publicado'])>Publicados</a>
                    <a href="{{ route('biblioteca.index', ['estado' => 'archivado']) }}" @class(['active' => $estadoFiltro === 'archivado'])>Archivados</a>
                </nav>
            @endif
        </div>

        <form method="GET" action="{{ route('biblioteca.index') }}" class="library-search" role="search">
            @if ($estadoFiltro === 'archivado') <input type="hidden" name="estado" value="archivado"> @endif
            @if ($categoriaFiltro !== '') <input type="hidden" name="categoria" value="{{ $categoriaFiltro }}"> @endif
            <label for="biblioteca-buscar">Buscar en la biblioteca</label>
            <div><input id="biblioteca-buscar" type="search" name="buscar" maxlength="100" value="{{ $buscar }}" placeholder="Título o descripción">
                <button class="btn secondary" type="submit">Buscar</button></div>
        </form>

        <nav class="library-categories" aria-label="Filtrar por categoría">
            @php($parametros = array_filter(['buscar' => $buscar, 'estado' => $estadoFiltro === 'archivado' ? 'archivado' : ''], fn ($valor) => $valor !== ''))
            <a href="{{ route('biblioteca.index', $parametros) }}" @class(['active' => $categoriaFiltro === ''])>Todas <span>{{ $categorias->sum('visibles_count') }}</span></a>
            @foreach ($categorias as $categoria)
                <a href="{{ route('biblioteca.index', $parametros + ['categoria' => $categoria->id]) }}"
                    @class(['active' => $categoriaFiltro === (string) $categoria->id])>{{ $categoria->nombre }} <span>{{ $categoria->visibles_count }}</span></a>
            @endforeach
        </nav>

        @if ($recursos->isNotEmpty())
            <div class="library-grid">
                @foreach ($recursos as $recurso)
                    <article class="library-card" aria-labelledby="recurso-{{ $recurso->id }}">
                        <a class="library-card-cover" href="{{ route('biblioteca.show', $recurso) }}" aria-label="Ver detalles de {{ $recurso->titulo }}">
                            @include('panel.partials.recurso-portada', ['recurso' => $recurso])
                        </a>
                        <div class="library-card-content">
                            <div class="library-card-meta"><span>{{ $recurso->categoria?->nombre }}</span><span>{{ $recurso->formato() }}</span></div>
                            <h3 id="recurso-{{ $recurso->id }}"><a href="{{ route('biblioteca.show', $recurso) }}">{{ $recurso->titulo }}</a></h3>
                            <p>{{ $recurso->descripcion ?: 'Abre la ficha para consultar este material.' }}</p>
                            <a class="library-card-link" href="{{ route('biblioteca.show', $recurso) }}">Ver recurso</a>
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="pagination">{{ $recursos->links() }}</div>
        @else
            <div class="library-empty">
                <x-icono nombre="book" />
                <h3>{{ $buscar !== '' || $categoriaFiltro !== '' ? 'No encontramos materiales con esos filtros' : ($estadoFiltro === 'archivado' ? 'No hay recursos archivados' : 'La biblioteca todavía está vacía') }}</h3>
                <p>{{ $buscar !== '' || $categoriaFiltro !== '' ? 'Prueba otro término o revisa todas las categorías.' : ($puedePublicar && $estadoFiltro !== 'archivado' ? 'Publica el primer material para compartirlo con el distrito.' : 'Vuelve más adelante para consultar nuevos materiales.') }}</p>
                @if ($buscar !== '' || $categoriaFiltro !== '') <a class="btn secondary" href="{{ route('biblioteca.index', $estadoFiltro === 'archivado' ? ['estado' => 'archivado'] : []) }}">Ver todos</a> @endif
            </div>
        @endif
    </section>
@endsection
