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
    <section class="section alternate">
        <div class="container">
            <p class="eyebrow">
                DE LOS ANDES A LA AMAZONÍA
            </p>
            <h2>
                Cerca de ti.
            </h2>
            <p class="muted">
                Encuentra tu próxima aventura en tu departamento.
            </p>
            <div class="department-grid">
                @foreach ($departamentos as $departamento)
                    <a href="#contacto" data-departamento="{{ $departamento }}">
                        {{ $departamento }}
                        <span>
                            ↗
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    <section class="section" id="contacto">
        <div class="container">
            <p class="eyebrow">
                EL PRIMER PASO ES CONVERSAR
            </p>
            <h2>
                Hablemos.
            </h2>
            <div class="contact-layout">
                <div>
                    <p class="muted">
                        Queremos conocerte. Cuéntanos de dónde eres y qué te gustaría descubrir con los scouts.
                    </p>
                    <div class="map-placeholder" role="img" aria-label="Referencia de ubicación: La Paz, Bolivia">
                        <x-icono nombre="pin" />
                        <strong>
                            La Paz, Bolivia
                        </strong>
                        <span>
                            Ubicación referencial
                        </span>
                    </div>
                    <h3>
                        Asociación de Scouts de Bolivia
                    </h3>
                    <p class="muted">
                        Dirección, teléfono y correo institucional:
                        <br>
                        pendientes de confirmación.
                    </p>
                    <p class="legal-demo">
                        Tu consulta quedará registrada para el equipo administrador.
                    </p>
                </div>
                <form id="contact-form" class="form-box" method="POST" action="{{ route('contacto.enviar') }}">
                    @csrf
                    <div class="grid two">
                        <x-campo nombre="nombre" etiqueta="Nombre completo" requerido />
                        <x-campo nombre="email" etiqueta="Correo electrónico" tipo="email" requerido />
                    </div>
                    <x-campo nombre="telefono" etiqueta="Teléfono" tipo="tel" />
                    <div class="grid two">
                        <x-campo nombre="asunto" etiqueta="Asunto" tipo="select"
                            :valor="request('asunto', 'Consulta general')"
                            :opciones="array_combine(['Consulta general', 'Inscripción', 'Voluntariado', 'Otro'],
                    ['Consulta general', 'Inscripción', 'Voluntariado', 'Otro'])" requerido />
                        <x-campo nombre="rama_id" etiqueta="Rama de interés" tipo="ramas" :valor="request('rama_id')" />
                    </div>
                    <x-campo nombre="mensaje" etiqueta="Mensaje" tipo="textarea" requerido />
                    <x-campo nombre="aceptacion" etiqueta="Acepto el tratamiento de mis datos para gestionar esta consulta."
            tipo="checkbox" requerido />
                    <button type="submit">
                        Enviar mensaje
                        <x-icono nombre="mail" />
                    </button>
                </form>
            </div>
        </div>
    </section>
@endsection
