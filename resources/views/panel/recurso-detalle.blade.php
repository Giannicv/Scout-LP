@extends('layouts.panel', ['titulo' => 'Biblioteca digital'])
@section('contenido')
    <a class="library-back" href="{{ route('biblioteca.index', $recurso->estado === 'archivado' ? ['estado' => 'archivado'] : []) }}">Volver a la biblioteca</a>

    <article class="library-detail" aria-labelledby="recurso-titulo">
        <div class="library-detail-cover">@include('panel.partials.recurso-portada', ['recurso' => $recurso])</div>
        <div class="library-detail-main">
            <div class="library-detail-kicker"><span>{{ $recurso->categoria?->nombre }}</span><span>{{ $recurso->formato() }}</span>@if($recurso->estado === 'archivado') <x-estado valor="archivado" /> @endif</div>
            <h2 id="recurso-titulo">{{ $recurso->titulo }}</h2>
            <p class="library-detail-description">{{ $recurso->descripcion ?: 'Material disponible para consultar y descargar.' }}</p>
            <dl class="library-detail-facts">
                <div><dt>Archivo</dt><dd>{{ $recurso->nombre_original }}</dd></div>
                <div><dt>Tamaño</dt><dd>{{ number_format($recurso->tamano_bytes / 1024 / 1024, 2, ',', '.') }} MB</dd></div>
                <div><dt>Publicado</dt><dd>{{ $recurso->publicado_en?->translatedFormat('d M Y') ?? 'Sin fecha' }}</dd></div>
                <div><dt>Compartido por</dt><dd>{{ $recurso->publicador?->nombre }} {{ $recurso->publicador?->apellido }}</dd></div>
            </dl>
            @if ($recurso->estado === 'publicado')
                <div class="library-detail-actions">
                    @if ($recurso->sePuedePrevisualizar()) <a class="btn secondary" href="#vista-recurso">Ver documento</a> @endif
                    <a class="btn" href="{{ route('biblioteca.download', $recurso) }}"><x-icono nombre="download" /> Descargar archivo</a>
                </div>
            @elseif ($puedeGestionar)
                <form method="POST" action="{{ route('biblioteca.restaurar', $recurso) }}" data-confirmar="El recurso volverá a estar disponible para los miembros.">
                    @csrf <button class="btn" type="submit">Restaurar recurso</button>
                </form>
            @endif
        </div>
    </article>

    @if ($recurso->estado === 'publicado')
        <section class="library-preview" id="vista-recurso" aria-labelledby="vista-titulo">
            <div class="library-preview-heading"><h2 id="vista-titulo">Vista del documento</h2><p>{{ $recurso->sePuedePrevisualizar() ? 'Puedes leerlo aquí o descargar una copia.' : 'Este formato se abre con una aplicación compatible. Descárgalo para consultarlo.' }}</p></div>
            @if ($recurso->sePuedePrevisualizar())
                <iframe src="{{ route('biblioteca.preview', $recurso) }}" title="Vista de {{ $recurso->titulo }}" loading="lazy"></iframe>
            @else
                <div class="library-preview-unavailable"><x-icono nombre="book" /><span>Vista previa disponible para archivos PDF y TXT.</span></div>
            @endif
        </section>
    @endif

    @if ($puedeGestionar && $recurso->estado === 'publicado')
        <details class="library-manage" @if($errors->any()) open @endif>
            <summary>Editar información y portada</summary>
            <form method="POST" action="{{ route('biblioteca.update', $recurso) }}" enctype="multipart/form-data" class="library-form">
                @csrf @method('PUT')
                <div class="field library-form-wide"><label for="editar-titulo">Título</label><input id="editar-titulo" name="titulo" maxlength="180" required value="{{ old('titulo', $recurso->titulo) }}"></div>
                <div class="field library-form-wide"><label for="editar-descripcion">Descripción</label><textarea id="editar-descripcion" name="descripcion" rows="4" maxlength="2000">{{ old('descripcion', $recurso->descripcion) }}</textarea></div>
                <div class="field"><label for="editar-categoria">Categoría</label><select id="editar-categoria" name="categoria_recurso_id" required>
                    @foreach ($categorias as $categoria) <option value="{{ $categoria->id }}" @selected((string) old('categoria_recurso_id', $recurso->categoria_recurso_id) === (string) $categoria->id)>{{ $categoria->nombre }}</option> @endforeach
                </select></div>
                <div class="field"><label for="editar-imagen">Reemplazar portada</label><input id="editar-imagen" name="imagen" type="file" accept="image/jpeg,image/png,image/webp"><small>JPG, PNG o WebP. Máximo 5 MB.</small></div>
                @if ($recurso->imagen_path)
                    <label class="library-remove-cover library-form-wide"><input type="checkbox" name="quitar_imagen" value="1"> Quitar la portada actual</label>
                @endif
                <div class="library-form-actions"><button class="btn" type="submit">Guardar cambios</button></div>
            </form>
        </details>
        <form method="POST" action="{{ route('biblioteca.destroy', $recurso) }}" class="library-archive" data-confirmar="El recurso dejará de aparecer en el catálogo. Podrás restaurarlo después." data-confirmar-variante="peligro">
            @csrf @method('DELETE') <button type="submit" class="btn danger">Archivar recurso</button>
        </form>
    @endif
@endsection
