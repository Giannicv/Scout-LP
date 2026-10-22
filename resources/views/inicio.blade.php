@extends('layouts.publico')
@section('titulo', 'Inicio | Scouts de Bolivia')
@section('contenido')
    <section class="hero home-hero container">
        <div class="hero-copy">
            <p class="eyebrow">UNA VIDA DE AVENTURAS</p>
            <h1>
                Crecer, servir
                <br>
                y dejar huella.
            </h1>
            <p class="lead">
                El movimiento juvenil que acompaña a niñas, niños y jóvenes a descubrir su potencial mediante valores, aventura y servicio.
            </p>
            <div class="actions">
                <a class="btn" href="{{ route('ramas') }}">
                    Explora las ramas
                    <span aria-hidden="true">→</span>
                </a>
                <a class="btn secondary" href="{{ route('noticias.index') }}">
                    Historias y noticias
                </a>
            </div>
            <div class="hero-proof" aria-label="Trayectoria del movimiento scout">
                <strong>Desde 1911</strong>
                <span>Formando jóvenes que construyen un mundo mejor.</span>
            </div>
        </div>
        <div class="hero-visual">
            <x-imagen
                ruta="img/scout-salar.jpg"
                descripcion="Scouts contemplando el salar y las montañas del altiplano boliviano"
                clase="hero-media"
                :prioritaria="true" />
            <div class="hero-float-card">
                <x-icono nombre="compass" />
                <span><strong>Siempre listos</strong> para la próxima aventura</span>
            </div>
            <p class="photo-caption">Bolivia · Un país por descubrir</p>
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
            <div class="grid four branch-card-grid">
                @foreach ($ramasPublicas as $rama)
                    <article class="card reveal branch-card" style="--rama-accent:{{ $rama->color_hex }}">
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
        <div class="container grid four stats-grid">
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
    <section class="section alternate" id="historias">
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
            <div class="grid three story-grid">
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
