@extends('layouts.acceso')
@section('contenido')
<h1>Solicita tu cuenta Scout</h1>
<p>La cuenta quedará pendiente hasta que el dirigente correspondiente la apruebe.</p>
<form method="POST" action="{{ route('registro.enviar') }}" data-registro-form>@csrf
    <div class="field"><label for="tipo_cuenta">Tipo de cuenta</label><select id="tipo_cuenta" name="tipo_cuenta" required>
        <option value="miembro" @selected(old('tipo_cuenta') === 'miembro')>Miembro</option>
        <option value="dirigente_grupo" @selected(old('tipo_cuenta') === 'dirigente_grupo')>Dirigente de grupo</option>
    </select></div>
    <div class="field"><label for="grupo_id">Grupo Scout</label><select id="grupo_id" name="grupo_id" required><option value="">Selecciona…</option>
        @foreach($grupos as $grupo)<option value="{{ $grupo->id }}" @selected(old('grupo_id') == $grupo->id)>{{ $grupo->nombre }}</option>@endforeach
    </select></div>
    <div class="field" data-solo-miembro><label for="rama_id">Rama</label><select id="rama_id" name="rama_id"><option value="">Selecciona…</option>
        @foreach($ramas as $rama)<option value="{{ $rama->id }}" @selected(old('rama_id') == $rama->id)>{{ $rama->nombre }}</option>@endforeach
    </select>@error('rama_id')<span class="error">{{ $message }}</span>@enderror</div>
    <div class="field" data-solo-miembro><label for="fecha_nacimiento">Fecha de nacimiento</label><input id="fecha_nacimiento" name="fecha_nacimiento" type="date" value="{{ old('fecha_nacimiento') }}">@error('fecha_nacimiento')<span class="error">{{ $message }}</span>@enderror</div>
    <x-campo nombre="nombre" etiqueta="Nombre" requerido />
    <x-campo nombre="apellido" etiqueta="Apellidos" requerido />
    <x-campo nombre="email" etiqueta="Correo electrónico" tipo="email" requerido />
    <x-campo nombre="telefono" etiqueta="Teléfono" tipo="tel" />
    <x-campo nombre="password" etiqueta="Contraseña" tipo="password" requerido />
    <ul class="password-rules" data-password-rules aria-label="Requisitos de la contraseña">
        <li data-password-rule="length">Al menos 6 caracteres</li>
        <li data-password-rule="letters">Al menos una letra</li>
        <li data-password-rule="numbers">Al menos un número</li>
    </ul>
    <x-campo nombre="password_confirmation" etiqueta="Confirmar contraseña" tipo="password" requerido />
    <p class="password-match" data-password-match aria-live="polite">Repite la contraseña para confirmarla.</p>
    <x-campo nombre="terminos" etiqueta="Acepto los términos y el tratamiento de mis datos" tipo="checkbox" requerido />
    <button class="btn">Enviar solicitud</button>
</form>
<a href="{{ route('login') }}">Ya tengo cuenta</a>
@endsection
