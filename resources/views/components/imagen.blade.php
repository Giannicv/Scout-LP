@props(['ruta' => '', 'descripcion' => '', 'clase' => '', 'prioritaria' => false])
<div class="media {{ $clase }}">
    @if ($ruta && preg_match('~^(https?://|img/)~i', $ruta))
        @php
            $esImagenLocal = str_starts_with($ruta, 'img/');
            $version = $esImagenLocal && is_file(public_path($ruta)) ? filemtime(public_path($ruta)) : null;
            $origen = $esImagenLocal ? asset($ruta).($version ? '?v='.$version : '') : $ruta;
        @endphp
        <img src="{{ $origen }}"
            alt="{{ $descripcion }}"
            loading="{{ $prioritaria ? 'eager' : 'lazy' }}"
            decoding="async"
            @if ($prioritaria) fetchpriority="high" @endif>
    @endif
    <div class="placeholder-label" role="img" aria-label="{{ $descripcion }}">
        <x-icono nombre="mountain" />
        <span>
            {{ $descripcion }}
        </span>
    </div>
</div>
