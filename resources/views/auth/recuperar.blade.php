@extends('layouts.acceso')
@section('contenido')
    <h1>
        Recupera tu acceso.
    </h1>
    <p class="muted">
        Recuperación simulada: no se envía correo.
    </p>
    <form method="POST" action="{{ route('recuperar.enviar') }}">
        @csrf
        <x-campo nombre="email" etiqueta="Correo electrónico" tipo="email" requerido />
        <button>
            Generar enlace de recuperación
        </button>
    </form>
    @if (session('enlace_recuperacion'))
        <p>
            <a href="{{ session('enlace_recuperacion') }}">
                Restablecer contraseña (vence en diez minutos)
            </a>
        </p>
    @endif
@endsection
