@extends('layouts.publico')
@section('titulo', 'Noticias | Scouts de Bolivia')
@section('contenido')
    <section class="page-hero container">
        <p class="eyebrow">
            NUESTRAS HISTORIAS
        </p>
        <h1>
            Lo que vivimos.
            <br>
            Lo que viene.
        </h1>
        <p class="lead">
            Noticias, campañas y encuentros del movimiento.
        </p>
    </section>
    <section class="container" data-noticias-url="{{ route('noticias.index') }}">
        <div class="filters" aria-label="Filtrar noticias">
            @foreach (['Todo', 'Noticias', 'Campamentos', 'Proyectos', 'Eventos'] as $categoria)
                <a
                    class="btn secondary"
                    data-categoria="{{ $categoria === 'Todo' ? '' : $categoria }}"
                    href="{{ route('noticias.index', ['categoria' => $categoria === 'Todo' ? '' : $categoria]) }}">
                    {{ $categoria }}
                </a>
            @endforeach
        </div>
        <div id="lista-noticias" aria-live="polite">
            @include('partials.lista-noticias')
        </div>
        <div class="center section">
            <a id="cargar-noticias"
                class="btn secondary"
                data-limite="{{ $limite }}" @if ($limite >= $total) hidden @endif
                href="{{ route('noticias.index', ['categoria' => request('categoria'), 'limite' => $limite + 6]) }}">
                Cargar más
            </a>
        </div>
    </section>
    <section class="section alternate">
        <div class="container">
            <p class="eyebrow">
                AGENDA SCOUT
            </p>
            <h2>
                Nos encontramos pronto.
            </h2>
            @forelse ($eventos as $evento)
                <article class="event-row">
                    <time class="event-date">
                        <strong>
                            {{ $evento->fecha_inicio->format('d') }}
                        </strong>
                        <span>
                            {{ $evento->fecha_inicio->translatedFormat('M') }}
                        </span>
                    </time>
                    <div class="event-info">
                        <h3>
                            {{ $evento->titulo }}
                        </h3>
                        <p>
                            {{ $evento->lugar }}
                            ·
                            {{ $evento->fecha_inicio->format('H:i') }}
                        </p>
                    </div>
                    <button class="btn secondary small" data-abrir-dialogo="evento-{{ $evento->id }}">
                        Ver encuentro ↗
                    </button>
                </article>
                <dialog id="evento-{{ $evento->id }}">
                    <h2>
                        {{ $evento->titulo }}
                    </h2>
                    <p>
                        {{ $evento->descripcion }}
                    </p>
                    <p>
                        {{ $evento->lugar }}
                    </p>
                    <a class="btn" href="{{ route('contacto') }}#contacto">
                        Consultar participación
                    </a>
                    <button class="btn secondary" data-cerrar-dialogo>
                        Cerrar
                    </button>
                </dialog>
            @empty
                <p class="empty">
                    Pronto compartiremos nuevos encuentros.
                </p>
            @endforelse
        </div>
    </section>
@endsection
