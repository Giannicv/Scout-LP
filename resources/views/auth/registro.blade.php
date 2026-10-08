@extends('layouts.acceso')
@section('contenido')
    <h1>
        Tu familia, parte de la aventura.
    </h1>
    <form method="POST" action="{{ route('registro.enviar') }}">
        @csrf
        <x-campo nombre="nombre" etiqueta="Nombre" requerido />
        <x-campo nombre="apellido" etiqueta="Apellidos" requerido />
        <x-campo nombre="email" etiqueta="Correo electrónico" tipo="email" requerido />
        <x-campo nombre="telefono" etiqueta="Teléfono" tipo="tel" requerido />
        <x-campo nombre="password" etiqueta="Contraseña" tipo="password" requerido />
        <x-campo nombre="password_confirmation" etiqueta="Confirmar contraseña" tipo="password" requerido />
        <x-campo nombre="terminos" etiqueta="Acepto los términos y el tratamiento de mis datos" tipo="checkbox" requerido />
        <button class="btn">
            Crear cuenta
        </button>
    </form>
    <a href="{{ route('login') }}">
        Ya tengo cuenta
    </a>
@endsection
