@extends('layouts.acceso')
@section('contenido')
    <h1>
        Esta página no está aquí.
    </h1>
    <p>
        Puede que el enlace haya cambiado.
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
