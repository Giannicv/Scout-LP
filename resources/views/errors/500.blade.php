@extends('layouts.acceso')
@section('contenido')
    <h1>
        No pudimos completar la solicitud.
    </h1>
    <p>
        Revisa la conexión y el registro del servidor.
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
