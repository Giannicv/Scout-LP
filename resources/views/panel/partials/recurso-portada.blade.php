@if ($recurso->imagen_path)
    <x-imagen :ruta="$recurso->imagen_path" :descripcion="'Portada de '.$recurso->titulo" />
@else
    <div class="library-cover-placeholder" aria-hidden="true">
        <span class="library-cover-symbol"><x-icono nombre="book" /></span>
        <span class="library-cover-format">{{ $recurso->formato() }}</span>
    </div>
@endif
