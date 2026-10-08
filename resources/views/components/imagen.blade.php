@props(['ruta' => '', 'descripcion' => '', 'clase' => ''])
<div class="media {{ $clase }}">
    @if ($ruta && preg_match('~^(https?://|img/)~i', $ruta))
        <img src="{{ str_starts_with($ruta, 'img/') ? asset($ruta) : $ruta }}"
            alt="{{ $descripcion }}" loading="lazy">
    @endif
    <div class="placeholder-label" role="img" aria-label="{{ $descripcion }}">
        <x-icono nombre="mountain" />
        <span>
            {{ $descripcion }}
        </span>
    </div>
</div>
