@php
    $idFormulario = $noticia?->exists ? 'editar-'.$noticia->id : 'crear';
    $recuperar = old('formulario') === $idFormulario;
    $valor = fn (string $campo, mixed $base = '') => $recuperar ? old($campo, $base) : $base;
    $estadoActual = $valor('estado', $noticia?->estado ?? 'borrador');
    $categoriaActual = $valor('categoria_id', $noticia?->categoria_id ?? '');
    $destacadaActual = (bool) $valor('destacada', $noticia?->destacada ?? false);
@endphp
<form method="POST"
    action="{{ $noticia?->exists ? route('publicaciones.noticias.update', $noticia) : route('publicaciones.noticias.store') }}"
    enctype="multipart/form-data" class="publication-form" data-publicacion-form
    data-publicacion-nueva="{{ $noticia?->exists ? '0' : '1' }}">
    @csrf
    @if ($noticia?->exists) @method('PUT') @endif
    <input type="hidden" name="formulario" value="{{ $idFormulario }}">

    <div class="publication-form-grid">
        <div class="field publication-form-wide">
            <label for="titulo-{{ $idFormulario }}">Título de la publicación</label>
            <input id="titulo-{{ $idFormulario }}" name="titulo" maxlength="180" required
                value="{{ $valor('titulo', $noticia?->titulo ?? '') }}" placeholder="Un título claro para la comunidad">
            @if ($recuperar) @error('titulo') <span class="error">{{ $message }}</span> @enderror @endif
        </div>
        <div class="field publication-form-wide">
            <label for="resumen-{{ $idFormulario }}">Resumen</label>
            <textarea id="resumen-{{ $idFormulario }}" name="resumen" rows="3" maxlength="1000" required
                placeholder="Qué ocurrió y por qué importa">{{ $valor('resumen', $noticia?->resumen ?? '') }}</textarea>
            @if ($recuperar) @error('resumen') <span class="error">{{ $message }}</span> @enderror @endif
        </div>
        <div class="field publication-form-wide">
            <label for="contenido-{{ $idFormulario }}">Texto completo</label>
            <textarea id="contenido-{{ $idFormulario }}" name="contenido" rows="9" maxlength="20000" required
                placeholder="Cuenta la historia con los detalles que debe conocer quien la lea">{{ $valor('contenido', $noticia?->contenido ?? '') }}</textarea>
            @if ($recuperar) @error('contenido') <span class="error">{{ $message }}</span> @enderror @endif
        </div>
        <div class="field">
            <label for="categoria-{{ $idFormulario }}">Categoría</label>
            <select id="categoria-{{ $idFormulario }}" name="categoria_id">
                <option value="">Sin categoría</option>
                @foreach ($categorias as $categoria)
                    <option value="{{ $categoria->id }}" @selected((string) $categoriaActual === (string) $categoria->id)>{{ $categoria->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="estado-{{ $idFormulario }}">Visibilidad</label>
            <select id="estado-{{ $idFormulario }}" name="estado" data-publicacion-estado>
                <option value="borrador" @selected($estadoActual === 'borrador')>Borrador · solo en el panel</option>
                <option value="publicado" @selected($estadoActual === 'publicado')>Publicada · visible en el sitio</option>
            </select>
        </div>
        <div class="field">
            <label for="imagen-{{ $idFormulario }}">Subir imagen</label>
            <input id="imagen-{{ $idFormulario }}" type="file" name="imagen" accept="image/jpeg,image/png,image/webp" data-publicacion-imagen>
            <small>JPG, PNG o WebP. Máximo 5 MB.</small>
            @if ($recuperar) @error('imagen') <span class="error">{{ $message }}</span> @enderror @endif
        </div>
        <div class="field">
            <label for="imagen-url-{{ $idFormulario }}">O usar una URL de imagen</label>
            <input id="imagen-url-{{ $idFormulario }}" type="url" name="imagen_url" maxlength="255"
                value="{{ $recuperar ? old('imagen_url', '') : '' }}" placeholder="https://..." data-publicacion-imagen-url>
            <small>Si no eliges otra imagen, se conserva la actual.</small>
            @if ($recuperar) @error('imagen_url') <span class="error">{{ $message }}</span> @enderror @endif
        </div>
        @if ($noticia?->imagen_url)
            <div class="publication-current-image publication-form-wide">
                <x-imagen :ruta="$noticia->imagen_url" :descripcion="$noticia->titulo" />
                <div>
                    <strong>Imagen actual</strong>
                    <p>Sube un archivo o pega otra URL para reemplazarla.</p>
                    <label class="publication-check"><input type="hidden" name="quitar_imagen" value="0"><input type="checkbox" name="quitar_imagen" value="1" data-publicacion-quitar @checked($valor('quitar_imagen', false))> Quitar imagen actual</label>
                </div>
            </div>
        @endif
        <div class="publication-featured publication-form-wide">
            <input type="hidden" name="destacada" value="0">
            <label class="publication-check">
                <input type="checkbox" name="destacada" value="1" data-publicacion-destacada @checked($destacadaActual)>
                Mostrar en “Historias que inspiran” del inicio
            </label>
            <small>Disponible cuando la publicación está visible en el sitio.</small>
        </div>
    </div>
    <div class="publication-form-actions">
        <button class="btn" type="submit" data-publicacion-guardar>{{ $noticia?->exists ? 'Guardar cambios' : 'Guardar publicación' }}</button>
    </div>
</form>
