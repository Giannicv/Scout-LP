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
            Registrar mi familia
        </a>
    </p>
    @if (app()->environment('local'))
        <details class="demo">
            <summary>
                Cuentas de demostración
            </summary>
            @foreach (['admin' => 'admin123', 'coordinador' => 'coord123', 'dirigente' => 'jefe123',
                'familia' => 'familia123', 'miembro' => 'scout123'] as $cuenta => $passwordDemo)
                <p>
                    {{ $cuenta }}
                    @scouts.bo /
                    {{ $passwordDemo }}
                </p>
            @endforeach
        </details>
    @endif
@endsection
