@extends('layouts.publico')
@section('titulo', 'Ramas educativas | Scouts de Bolivia')
@section('contenido')
    <section class="page-hero container">
        <p class="eyebrow">
            CUATRO RAMAS. INFINITAS POSIBILIDADES.
        </p>
        <h1>
            Una aventura para cada edad.
        </h1>
        <p class="lead">
            Un camino de aprendizaje para cada etapa.
        </p>
    </section>
    <nav class="anchor-bar" aria-label="Ramas educativas">
        <div class="container">
            @foreach ($ramasPublicas as $rama)
                <a href="#{{ $rama->slug }}" @class(['active' => $loop->first])>
                    {{ $rama->nombre }}
                </a>
            @endforeach
        </div>
    </nav>
    @foreach ($ramasPublicas as $rama)
        <section @class(['section', 'branch-section', 'alternate' => $loop->even]) id="{{ $rama->slug }}">
            <div class="container branch-layout" style="--rama-accent:{{ $rama->color_hex }}">
                <x-imagen :ruta="$rama->imagen_url" :descripcion="$rama->nombre.' al aire libre'" />
                <div class="reveal">
                    <p class="eyebrow">
                        {{ $rama->edad_minima }}
                        –
                        {{ $rama->edad_maxima }}
                        años
                    </p>
                    <h2>
                        {{ $rama->nombre }}
                    </h2>
                    <h3>
                        {{ $rama->lema }}
                    </h3>
                    <p class="muted">
                        {{ $rama->descripcion }}
                    </p>
                    <p>Actividades educativas, servicio y vida al aire libre adaptadas a esta etapa.</p>
                </div>
            </div>
        </section>
    @endforeach
@endsection
