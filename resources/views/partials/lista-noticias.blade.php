@if ($destacada = $noticias->firstWhere('destacada', true))
    <article class="featured">
        <x-imagen :ruta="$destacada->imagen_url" :descripcion="$destacada->titulo" />
        <div class="featured-content">
            <p class="eyebrow">
                HISTORIA DESTACADA
            </p>
            <span class="meta">
                {{ $destacada->publicado_en?->format('d/m/Y') }}
            </span>
            <h2>
                {{ $destacada->titulo }}
            </h2>
            <p class="muted">
                {{ $destacada->resumen }}
            </p>
            <a class="btn small" href="{{ route('noticias.show', $destacada) }}">
                Leer historia
            </a>
        </div>
    </article>
@endif
<div class="grid three">
    @forelse ($noticias as $noticia)
        <x-noticia :noticia="$noticia" />
    @empty
        <p class="empty">
            No hay noticias en esta categoría.
        </p>
    @endforelse
</div>
