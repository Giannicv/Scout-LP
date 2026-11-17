@php
    $idFormulario = $contacto?->exists ? 'editar-'.$contacto->id : 'crear-contacto';
    $recuperar = old('formulario') === $idFormulario;
    $valor = fn (string $campo, mixed $base = '') => $recuperar ? old($campo, $base) : $base;
@endphp
<form method="POST" action="{{ $contacto?->exists ? route('perfil.contactos.update', $contacto) : route('perfil.contactos.store') }}" class="profile-contact-form">
    @csrf
    @if ($contacto?->exists) @method('PUT') @endif
    <input type="hidden" name="formulario" value="{{ $idFormulario }}">
    <div class="field"><label for="nombre-{{ $idFormulario }}">Nombre completo</label>
        <input id="nombre-{{ $idFormulario }}" name="nombre" maxlength="150" required value="{{ $valor('nombre', $contacto?->nombre ?? '') }}"></div>
    <div class="field"><label for="parentesco-{{ $idFormulario }}">Parentesco</label>
        <input id="parentesco-{{ $idFormulario }}" name="parentesco" maxlength="80" value="{{ $valor('parentesco', $contacto?->parentesco ?? '') }}" placeholder="Madre, padre, familiar..."></div>
    <div class="field"><label for="telefono-{{ $idFormulario }}">Teléfono</label>
        <input id="telefono-{{ $idFormulario }}" type="tel" name="telefono" maxlength="30" required value="{{ $valor('telefono', $contacto?->telefono ?? '') }}"></div>
    <div class="field"><label for="tipo-{{ $idFormulario }}">Tipo de contacto</label>
        <select id="tipo-{{ $idFormulario }}" name="tipo" required>
            @foreach (['emergencia' => 'Emergencia', 'tutor' => 'Tutor', 'ambos' => 'Tutor y emergencia'] as $clave => $etiqueta)
                <option value="{{ $clave }}" @selected($valor('tipo', $contacto?->tipo ?? 'emergencia') === $clave)>{{ $etiqueta }}</option>
            @endforeach
        </select></div>
    <button class="btn secondary" type="submit">{{ $contacto?->exists ? 'Guardar cambios' : 'Guardar contacto' }}</button>
</form>
