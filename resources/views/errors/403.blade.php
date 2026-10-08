@extends('layouts.acceso')
@section('contenido')
    <h1>
        Acceso denegado
    </h1>
    <p>
        Tu rol no permite acceder a esta sección.
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
