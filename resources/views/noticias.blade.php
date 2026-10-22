@extends('layouts.publico')
@section('titulo', 'Historias y noticias | Scouts de Bolivia')
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
            Historias, novedades y proyectos que reflejan la vida del movimiento.
        </p>
    </section>
    <section class="container news-catalog" data-noticias-url="{{ route('noticias.index') }}">
        <div class="filters" aria-label="Filtrar noticias">
            @foreach ($categorias->prepend('Todo') as $categoria)
                <a
                    @class(['btn', 'secondary', 'active' => request('categoria', '') === ($categoria === 'Todo' ? '' : $categoria)])
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
@endsection
