@props(['noticia'])
<article class="card reveal">
    <a class="cover-link" href="{{ route('noticias.show', $noticia) }}">
        <x-imagen :ruta="$noticia->imagen_url" :descripcion="$noticia->titulo" />
        <div class="card-body">
            <div class="actions">
                <span class="tag">
                    {{ $noticia->categoria?->nombre }}
                </span>
                <time class="meta">
                    {{ $noticia->publicado_en?->translatedFormat('j F Y') }}
                </time>
            </div>
            <h3>
                {{ $noticia->titulo }}
            </h3>
            <p>
                {{ $noticia->resumen }}
            </p>
            <span class="text-link">
                Leer historia ↗
            </span>
        </div>
    </a>
</article>
