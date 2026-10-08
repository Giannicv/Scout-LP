@extends('layouts.publico')
@section('titulo', $noticia->titulo.' | Scouts de Bolivia')
@section('contenido')
    <section class="section container">
        <article class="article">
            <nav class="meta">
                <a href="{{ route('noticias.index') }}">
                    Noticias
                </a>
                /
                {{ $noticia->titulo }}
            </nav>
            <header>
                <span class="tag">
                    {{ $noticia->categoria?->nombre }}
                </span>
                <h1>
                    {{ $noticia->titulo }}
                </h1>
                <p class="meta">
                    {{ $noticia->publicado_en?->translatedFormat('j F Y') }}
                    ·
                    {{ $noticia->autor ? $noticia->autor->nombre.' '.$noticia->autor->apellido : 'Autor eliminado' }}
                </p>
            </header>
            <x-imagen :ruta="$noticia->imagen_url" :descripcion="$noticia->titulo" />
            <div class="article-body">
                @foreach (preg_split('/\n\s*\n/', $noticia->contenido) as $parrafo)
                    @if (str_starts_with($parrafo, '## '))
                        <h2>
                            {{ strtok(substr($parrafo, 3), "\n") }}
                        </h2>
                        @if (str_contains($parrafo, "\n"))
                            <p>
                                {{ substr($parrafo, strpos($parrafo, "\n") + 1) }}
                            </p>
                        @endif
                    @elseif (str_starts_with($parrafo, '> '))
                        <blockquote>
                            {{ substr($parrafo, 2) }}
                        </blockquote>
                    @else
                        <p>
                            {{ $parrafo }}
                        </p>
                    @endif
                @endforeach
            </div>
            <div class="actions">
                <button class="btn secondary small" data-copiar-enlace>
                    Copiar enlace
                </button>
                <a
                    class="btn secondary small"
                    href="https://wa.me/?text={{ rawurlencode($noticia->titulo.' '.url()->current()) }}"
                target="_blank" rel="noopener noreferrer">
                    WhatsApp
                </a>
                <a
                    class="btn secondary small"
                    href="https://www.facebook.com/sharer/sharer.php?u={{ rawurlencode(url()->current()) }}"
                target="_blank" rel="noopener noreferrer">
                    Facebook
                </a>
            </div>
        </article>
    </section>
    <section class="section alternate">
        <div class="container">
            <h2>
                Sigue explorando.
            </h2>
            <div class="grid three">
                @foreach ($relacionadas as $relacionada)
                    <x-noticia :noticia="$relacionada" />
                @endforeach
            </div>
        </div>
    </section>
@endsection
