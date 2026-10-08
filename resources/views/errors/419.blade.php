@extends('layouts.acceso')
@section('contenido')
    <h1>
        La sesión del formulario venció.
    </h1>
    <p>
        Vuelve a abrir la página e inténtalo nuevamente.
    </p>
    @auth
        <a class="btn" href="{{ route('panel') }}">
            Volver a mi panel
        </a>
    @else
        <a class="btn" href="{{ route('login') }}">
            Ingresar
        </a>
    @endauth
@endsection
