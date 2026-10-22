@extends('layouts.acceso')
@section('contenido')
    <h1>
        Siempre listos.
    </h1>
    <p class="muted">
        Ingresa para continuar tu aventura.
    </p>
    <form method="POST" action="{{ route('login.enviar') }}">
        @csrf
        <x-campo nombre="email" etiqueta="Correo electrónico" tipo="email" requerido />
        <x-campo nombre="password" etiqueta="Contraseña" tipo="password" requerido />
        <button class="btn">
            Ingresar
        </button>
    </form>
    <p>
        <a href="{{ route('recuperar') }}">
            Olvidé mi contraseña
        </a>
    </p>
    <p>
        ¿Primera vez aquí?
        <a href="{{ route('registro') }}">
            Solicitar una cuenta
        </a>
    </p>
    @if (app()->environment('local'))
        <details class="demo">
            <summary>
                Cuentas de demostración
            </summary>
            @foreach (['distrito@scoutlapaz.bo', 'dirigente1@scoutlapaz.bo', 'miembro1@scoutlapaz.bo'] as $cuenta)
                <p>
                    {{ $cuenta }} / Scout2026!
                </p>
            @endforeach
        </details>
    @endif
@endsection
