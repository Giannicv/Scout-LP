@extends('layouts.publico')
@section('titulo', 'Inicio | Scouts de Bolivia')
@section('contenido')
    <section class="hero container">
        <p class="eyebrow">
            UNA VIDA DE AVENTURAS. UN MUNDO MEJOR.
        </p>
        <h1>
            Formando líderes
            <br>
            desde
            <span style="color:var(--morado-700)">
                1911.
            </span>
        </h1>
        <p class="lead">
            Un movimiento juvenil que ayuda a niñas, niños y jóvenes a crecer con valores, aventura y servicio.
        </p>
        <div class="actions">
            <a class="btn" href="{{ route('nosotros', ['asunto' => 'Inscripción']) }}#contacto">
                Únete a la aventura
            </a>
            <a class="btn secondary" href="{{ route('ramas') }}">
                Conoce las ramas
            </a>
        </div>
        <x-imagen ruta="img/hero.jpg" descripcion="Jóvenes scouts caminando en la montaña al atardecer" clase="hero-media" />
        <div class="photo-caption">
            <span>
                Bolivia · Un país por descubrir
            </span>
            <span>
                Siempre listos para la próxima aventura
            </span>
        </div>
    </section>
    <section class="ideas">
        <div class="container grid three">
            @foreach ([['compass', 'Aprender haciendo', 'Cada experiencia nos ayuda a crecer.'],
                ['heart', 'Servir a la comunidad', 'Pequeñas acciones que cambian el mundo.'],
                ['mountain', 'Vivir al aire libre', 'La naturaleza es nuestra gran escuela.']] as $idea)
                <article class="idea">
                    <span class="icon">
                        <x-icono :nombre="$idea[0]" />
                    </span>
                    <div>
                        <h3>
                            {{ $idea[1] }}
                        </h3>
                        <p>
                            {{ $idea[2] }}
                        </p>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
    <section class="section">
        <div class="container">
            <div class="section-head">
                <div>
                    <p class="eyebrow">
                        CRECER, PASO A PASO
                    </p>
                    <h2>
                        Una aventura para cada edad.
                    </h2>
                    <p class="muted">
                        Cuatro ramas, un mismo camino.
                    </p>
                </div>
                <a class="text-link" href="{{ route('ramas') }}">
                    Encuentra tu rama ↗
                </a>
            </div>
            <div class="grid four">
                @foreach ($ramasPublicas as $rama)
                    <article class="card reveal">
                        <a class="cover-link" href="{{ route('ramas') }}#{{ $rama->slug }}">
                            <x-imagen :ruta="$rama->imagen_url" :descripcion="$rama->nombre.' · '.$rama->lema" />
                            <div class="card-body">
                                <span class="tag">
                                    {{ $rama->edad_minima }}
                                    –
                                    {{ $rama->edad_maxima }}
                                    años
                                </span>
                                <h3>
                                    {{ $rama->nombre }}
                                </h3>
                                <p>
                                    {{ $rama->lema }}
                                </p>
                                <span class="text-link">
                                    Conocer más ↗
                                </span>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
    <section class="stats">
        <div class="container grid four">
            @foreach ([[1911, 'Año de fundación'], [9, 'Departamentos'], [7900, 'Miembros activos'],
                [4, 'Ramas educativas']] as $estadistica)
                <div class="stat">
                    <strong data-count="{{ $estadistica[0] }}">
                        {{ $estadistica[0] === 7900 ? '7.900+' : $estadistica[0] }}
                    </strong>
                    <span>
                        {{ $estadistica[1] }}
                    </span>
                </div>
            @endforeach
        </div>
    </section>
    <section class="section alternate">
        <div class="container">
            <div class="section-head">
                <div>
                    <p class="eyebrow">
                        LO QUE NOS MUEVE
                    </p>
                    <h2>
                        Historias que inspiran.
                    </h2>
                    <p class="muted">
                        Vivencias que dejan huella, dentro y fuera del grupo.
                    </p>
                </div>
                <a class="text-link" href="{{ route('noticias.index') }}">
                    Ver todas las noticias ↗
                </a>
            </div>
            <div class="grid three">
                @forelse ($noticias as $noticia)
                    <x-noticia :noticia="$noticia" />
                @empty
                    <p>
                        No hay historias destacadas todavía.
                    </p>
                @endforelse
            </div>
        </div>
    </section>
    @include('partials.banner')
@endsection
