@extends('layouts.panel', ['titulo' => $dirigente ? 'Editar dirigente' : 'Crear dirigente'])
@section('contenido')
    <a class="leaders-back" href="{{ route('dirigentes.index') }}">Volver a dirigentes</a>
    <header class="leaders-form-heading"><h2>{{ $dirigente ? 'Editar dirigente de grupo' : 'Crear dirigente de grupo' }}</h2>
        <p>{{ $dirigente ? 'Actualiza sus datos y el grupo donde tiene acceso.' : 'Asigna el grupo y define una contraseña inicial para la cuenta.' }}</p></header>
    <form class="leaders-form" method="POST" action="{{ $dirigente ? route('dirigentes.update', $dirigente) : route('dirigentes.store') }}"
        @if (! $dirigente) data-registro-form @endif>
        @csrf @if ($dirigente) @method('PUT') @endif
        <div class="leaders-form-grid">
            <x-campo nombre="nombre" etiqueta="Nombre" :valor="$dirigente?->nombre ?? ''" requerido />
            <x-campo nombre="apellido" etiqueta="Apellidos" :valor="$dirigente?->apellido ?? ''" requerido />
            <x-campo nombre="email" etiqueta="Correo electrónico" tipo="email" :valor="$dirigente?->email ?? ''" requerido />
            <div class="field"><label for="grupo-dirigente-form">Grupo Scout *</label>
                <select id="grupo-dirigente-form" name="grupo_id" required><option value="">Selecciona un grupo</option>
                    @foreach ($grupos as $grupo)<option value="{{ $grupo->id }}" @selected(old('grupo_id', $dirigente?->grupo_id) == $grupo->id)>{{ $grupo->nombre }}{{ $grupo->activo ? '' : ' (inactivo)' }}</option>@endforeach
                </select>@error('grupo_id')<span class="error" role="alert">{{ $message }}</span>@enderror</div>
            @unless ($dirigente)
                <div><x-campo nombre="password" etiqueta="Contraseña inicial" tipo="password" requerido />
                    <ul class="password-rules" data-password-rules aria-label="Requisitos de la contraseña">
                        <li data-password-rule="length">Al menos 6 caracteres</li><li data-password-rule="letters">Al menos una letra</li><li data-password-rule="numbers">Al menos un número</li>
                    </ul></div>
                <div><x-campo nombre="password_confirmation" etiqueta="Confirmar contraseña" tipo="password" requerido />
                    <p class="password-match" data-password-match aria-live="polite">Repite la contraseña para confirmarla.</p></div>
            @endunless
        </div>
        @unless ($dirigente)<p class="leaders-form-note">La cuenta quedará activa al crearla. Comparte la contraseña inicial de forma segura; el dirigente podrá cambiarla en Mi cuenta.</p>@endunless
        @if ($dirigente)<p class="leaders-form-note">Si cambias el grupo, el dirigente deberá volver a iniciar sesión para acceder al nuevo grupo.</p>@endif
        <div class="leaders-form-actions"><button class="btn" type="submit">{{ $dirigente ? 'Guardar cambios' : 'Crear dirigente' }}</button>
            <a class="btn secondary" href="{{ route('dirigentes.index') }}">Cancelar</a></div>
    </form>
@endsection
