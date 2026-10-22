@extends('layouts.publico')
@section('titulo', 'Nosotros | Scouts de Bolivia')
@section('contenido')
    <section class="page-hero container">
        <p class="eyebrow">
            NUESTRA HISTORIA
        </p>
        <h1>
            Más de un siglo de
            <br>
            escultismo boliviano.
        </h1>
        <p class="lead">
            Creemos en el potencial de cada joven para construir un mundo mejor.
        </p>
    </section>
    <div class="container">
        <x-imagen ruta="img/nosotros.jpg" descripcion="Amistad y servicio entre scouts de Bolivia"
    clase="hero-media" />
    </div>
    <section class="section">
        <div class="container">
            <p class="eyebrow">
                UN CAMINO COMPARTIDO
            </p>
            <h2>
                Una historia que sigue creciendo.
            </h2>
            <div class="grid four timeline">
                @foreach ([['1911', 'El inicio', 'Fundación de la institución.'],
                    ['1950', 'Nuevos caminos', 'Hito de ejemplo: expansión de grupos y actividades.'],
                    ['1969', 'Más unidos', 'Hito de ejemplo: encuentro y formación de líderes.'],
                    ['Hoy', 'Miramos adelante', 'Jóvenes que aprenden, comparten y sirven.']] as $hito)
                    <article class="card">
                        <strong>
                            {{ $hito[0] }}
                        </strong>
                        <h3>
                            {{ $hito[1] }}
                        </h3>
                        <p>
                            {{ $hito[2] }}
                        </p>
                    </article>
                @endforeach
            </div>
            <p class="legal-demo">
                {{ $contenidos->get('historia')?->contenido }}
            </p>
        </div>
    </section>
    <section class="section alternate">
        <div class="container grid two">
            <article>
                <p class="eyebrow">
                    LO QUE NOS GUÍA
                </p>
                <h2>
                    Nuestra misión.
                </h2>
                <p>
                    {{ $contenidos->get('mision')?->contenido }}
                </p>
            </article>
            <article>
                <p class="eyebrow">
                    HACIA DÓNDE VAMOS
                </p>
                <h2>
                    Nuestra visión.
                </h2>
                <p>
                    {{ $contenidos->get('vision')?->contenido }}
                </p>
            </article>
        </div>
    </section>
    <section class="section">
        <div class="container">
            <h2>
                Una red que acompaña.
            </h2>
            <div class="grid three">
                @foreach ([['users', 'Voluntarios', 'Personas que comparten su tiempo y acompañan cada aprendizaje.'],
                    ['heart', 'Familias', 'La confianza y el apoyo que nos animan a llegar más lejos.'],
                    ['compass', 'Instituciones', 'Alianzas para abrir oportunidades y servir a nuestra comunidad.']] as $apoyo)
                    <article class="card card-body">
                        <x-icono :nombre="$apoyo[0]" />
                        <h3>
                            {{ $apoyo[1] }}
                        </h3>
                        <p>
                            {{ $apoyo[2] }}
                        </p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endsection
