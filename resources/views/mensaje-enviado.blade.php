@extends('layouts.publico')
@section('contenido')
    <section class="success-page">
        <div class="success-icon">
            <x-icono nombre="check" />
        </div>
        <h1>
            Gracias por escribirnos.
        </h1>
        <p class="lead">
            Nuestro equipo te responderá a la brevedad.
        </p>
        <p class="muted">
            Tu mensaje quedó registrado en nuestra base de datos.
        </p>
        <a class="btn" href="{{ route('inicio') }}">
            Volver al inicio
        </a>
    </section>
@endsection
