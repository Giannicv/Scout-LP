@extends('layouts.acceso')
@section('contenido')
    <h1>
        Nueva contraseña.
    </h1>
    <form method="POST" action="{{ route('restablecer.enviar') }}">
        @csrf
        <input type="hidden" name="token" value="{{ request()->route('token') }}">
        <input type="hidden" name="email" value="{{ request('email') }}">
        <x-campo nombre="password" etiqueta="Nueva contraseña" tipo="password" requerido />
        <x-campo nombre="password_confirmation" etiqueta="Confirmar contraseña" tipo="password" requerido />
        <button>
            Guardar contraseña
        </button>
    </form>
@endsection
