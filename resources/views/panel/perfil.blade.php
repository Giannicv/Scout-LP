@extends('layouts.panel', ['titulo' => 'Mi perfil'])
@section('contenido')
<section class="card"><h2>{{ $miembro->nombre }} {{ $miembro->apellido }}</h2><p>{{ $miembro->grupo->nombre }} · {{ $miembro->rama->nombre }}</p></section>
<section><h2>Contactos de tutor o emergencia</h2>
@foreach($miembro->contactos as $contacto)<article class="card"><strong>{{ $contacto->nombre }}</strong><p>{{ $contacto->parentesco }} · {{ $contacto->telefono }} · {{ ucfirst($contacto->tipo) }}</p>
<form method="POST" action="{{ route('perfil.contactos.destroy', $contacto) }}">@csrf @method('DELETE')<button class="btn secondary small">Eliminar</button></form></article>@endforeach
<form method="POST" action="{{ route('perfil.contactos.store') }}" class="card">@csrf<h3>Agregar contacto</h3>
<x-campo nombre="nombre" etiqueta="Nombre completo" requerido /><x-campo nombre="parentesco" etiqueta="Parentesco" /><x-campo nombre="telefono" etiqueta="Teléfono" requerido />
<label>Tipo<select name="tipo"><option value="emergencia">Emergencia</option><option value="tutor">Tutor</option><option value="ambos">Tutor y emergencia</option></select></label><button class="btn">Guardar</button></form>
</section>
@endsection
