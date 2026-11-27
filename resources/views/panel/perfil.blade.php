@extends('layouts.panel', ['titulo' => 'Mi perfil'])
@section('contenido')
    @php
        $iniciales = mb_strtoupper(mb_substr($miembro->nombre, 0, 1).mb_substr($miembro->apellido, 0, 1));
    @endphp
    <header class="profile-hero">
        <span class="profile-avatar" aria-hidden="true">{{ $iniciales }}</span>
        <div class="profile-hero-main">
            <p>Mi perfil scout</p>
            <h2>{{ $miembro->nombre }} {{ $miembro->apellido }}</h2>
            <div class="profile-identity"><span>{{ $miembro->grupo?->nombre ?? 'Sin grupo asignado' }}</span><span>{{ $miembro->rama?->nombre ?? 'Sin rama asignada' }}</span></div>
        </div>
        <span @class(['profile-membership', 'inactive' => ! $miembro->activo])>{{ $miembro->activo ? 'Membresía activa' : 'Membresía inactiva' }}</span>
    </header>

    <nav class="profile-jump" aria-label="Secciones de Mi perfil">
        <a href="#mis-contactos">Contactos de confianza</a>
        <a href="#mis-datos">Mis datos</a>
        <a href="#seguridad">Seguridad</a>
    </nav>

    <section class="profile-section" id="mis-contactos" aria-labelledby="contactos-titulo">
        <div class="profile-section-heading">
            <div><h2 id="contactos-titulo">Contactos de confianza</h2><p>Registra a quienes se puede contactar como tutor o en una emergencia.</p></div>
            <span class="profile-count">{{ $miembro->contactos->count() }} {{ $miembro->contactos->count() === 1 ? 'contacto' : 'contactos' }}</span>
        </div>
        <details class="profile-create" @if(old('formulario') === 'crear-contacto' && $errors->any()) open @endif>
            <summary>Agregar contacto</summary>
            @include('panel.partials.form-contacto', ['contacto' => null])
        </details>
        @if ($miembro->contactos->isNotEmpty())
            <div class="profile-contact-grid">
                @foreach ($miembro->contactos as $contacto)
                    <article class="profile-contact-card" aria-labelledby="contacto-{{ $contacto->id }}">
                        <div class="profile-contact-top"><span class="profile-contact-icon" aria-hidden="true"><x-icono nombre="phone" /></span>
                            <span class="profile-contact-type">{{ match ($contacto->tipo) { 'tutor' => 'Tutor', 'ambos' => 'Tutor y emergencia', default => 'Emergencia' } }}</span></div>
                        <h3 id="contacto-{{ $contacto->id }}">{{ $contacto->nombre }}</h3>
                        <p>{{ $contacto->parentesco ?: 'Parentesco no indicado' }}</p>
                        <strong class="profile-contact-phone">{{ $contacto->telefono }}</strong>
                        <div class="profile-contact-actions">
                            <details @if(old('formulario') === 'editar-'.$contacto->id && $errors->any()) open @endif>
                                <summary>Editar</summary>
                                @include('panel.partials.form-contacto', ['contacto' => $contacto])
                            </details>
                            <form method="POST" action="{{ route('perfil.contactos.destroy', $contacto) }}"
                                data-confirmar="El contacto {{ $contacto->nombre }} se eliminará de tu perfil." data-confirmar-variante="peligro">
                                @csrf @method('DELETE') <button type="submit">Eliminar</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="profile-empty-contact"><x-icono nombre="users" /><p>Aún no registraste contactos. Agrega al menos uno para tener estos datos a mano.</p></div>
        @endif
    </section>

    <div class="profile-bottom-grid">
        <section class="profile-section" id="mis-datos" aria-labelledby="datos-titulo">
            <div class="profile-section-heading"><div><h2 id="datos-titulo">Mis datos</h2><p>Información registrada en tu cuenta.</p></div></div>
            <dl class="profile-facts">
                <div><dt>Nombre completo</dt><dd>{{ $miembro->nombre }} {{ $miembro->apellido }}</dd></div>
                <div><dt>Fecha de nacimiento</dt><dd>{{ $miembro->fecha_nacimiento?->translatedFormat('d M Y') }}</dd></div>
                <div><dt>Correo de acceso</dt><dd>{{ $usuario->email }}</dd></div>
                <div><dt>Teléfono</dt><dd>{{ $miembro->telefono ?: 'No registrado' }}</dd></div>
            </dl>
            <details class="profile-inline-editor" @if(old('formulario') === 'telefono' && $errors->any()) open @endif>
                <summary>Actualizar teléfono</summary>
                <form method="POST" action="{{ route('perfil.telefono.update') }}">
                    @csrf @method('PUT') <input type="hidden" name="formulario" value="telefono">
                    <div class="field"><label for="perfil-telefono">Número de teléfono</label><input id="perfil-telefono" type="tel" name="telefono" maxlength="30" autocomplete="tel" value="{{ old('formulario') === 'telefono' ? old('telefono') : $miembro->telefono }}" placeholder="Tu número de contacto"></div>
                    <button class="btn secondary" type="submit">Guardar teléfono</button>
                </form>
            </details>
        </section>

        <section class="profile-section" id="seguridad" aria-labelledby="seguridad-titulo">
            <div class="profile-section-heading"><div><h2 id="seguridad-titulo">Seguridad de la cuenta</h2><p>Actualiza tu contraseña cuando lo necesites.</p></div><x-icono nombre="lock" /></div>
            <p class="profile-security-note">Usa al menos 6 caracteres, con letras y números.</p>
            <details class="profile-inline-editor" @if(old('formulario') === 'password' && $errors->any()) open @endif>
                <summary>Cambiar contraseña</summary>
                <form method="POST" action="{{ route('password.cambiar') }}" data-registro-form>
                    @csrf <input type="hidden" name="formulario" value="password">
                    <x-campo nombre="actual" etiqueta="Contraseña actual" tipo="password" requerido />
                    <x-campo nombre="password" etiqueta="Nueva contraseña" tipo="password" requerido />
                    <ul class="password-rules" data-password-rules aria-label="Requisitos de la contraseña">
                        <li data-password-rule="length">Al menos 6 caracteres</li><li data-password-rule="letters">Al menos una letra</li><li data-password-rule="numbers">Al menos un número</li>
                    </ul>
                    <x-campo nombre="password_confirmation" etiqueta="Confirmar contraseña" tipo="password" requerido />
                    <p class="password-match" data-password-match aria-live="polite">Repite la contraseña para confirmarla.</p>
                    <button class="btn secondary" type="submit">Actualizar contraseña</button>
                </form>
            </details>
        </section>
    </div>
@endsection
