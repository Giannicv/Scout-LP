@extends('layouts.panel', ['titulo' => 'Mi cuenta'])
@section('contenido')
    <header class="leaders-form-heading"><h2>Mi cuenta</h2><p>Consulta tu acceso y cambia tu contraseña cuando lo necesites.</p></header>
    <div class="account-layout">
        <section class="account-summary" aria-labelledby="account-data-title"><h3 id="account-data-title">Datos de acceso</h3>
            <dl><div><dt>Nombre</dt><dd>{{ $usuario->nombre }} {{ $usuario->apellido }}</dd></div>
                <div><dt>Correo</dt><dd>{{ $usuario->email }}</dd></div>
                <div><dt>Rol</dt><dd>{{ $usuario->es('dirigente_distrital') ? 'Dirigente distrital' : 'Dirigente de grupo' }}</dd></div>
                @if ($usuario->grupo)<div><dt>Grupo</dt><dd>{{ $usuario->grupo->nombre }}</dd></div>@endif
            </dl>
        </section>
        <section class="account-security" aria-labelledby="account-password-title"><h3 id="account-password-title">Cambiar contraseña</h3>
            <p>Usa al menos 6 caracteres, una letra y un número.</p>
            <form method="POST" action="{{ route('password.cambiar') }}" data-registro-form>
                @csrf
                <x-campo nombre="actual" etiqueta="Contraseña actual" tipo="password" requerido />
                <x-campo nombre="password" etiqueta="Nueva contraseña" tipo="password" requerido />
                <ul class="password-rules" data-password-rules aria-label="Requisitos de la contraseña">
                    <li data-password-rule="length">Al menos 6 caracteres</li><li data-password-rule="letters">Al menos una letra</li><li data-password-rule="numbers">Al menos un número</li>
                </ul>
                <x-campo nombre="password_confirmation" etiqueta="Confirmar contraseña" tipo="password" requerido />
                <p class="password-match" data-password-match aria-live="polite">Repite la contraseña para confirmarla.</p>
                <button class="btn" type="submit">Actualizar contraseña</button>
            </form>
        </section>
    </div>
@endsection
