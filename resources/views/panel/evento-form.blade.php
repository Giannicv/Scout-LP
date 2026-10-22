@extends('layouts.panel', ['titulo' => $evento->exists ? 'Editar evento' : 'Nuevo evento'])
@section('contenido')
<form method="POST" action="{{ $evento->exists ? route('eventos.update', $evento) : route('eventos.store') }}" class="card form-grid">@csrf @if($evento->exists) @method('PUT') @endif
    <x-campo nombre="titulo" etiqueta="Título" :valor="$evento->titulo" requerido />
    <label>Tipo<select name="tipo" required>@foreach(['actividad'=>'Actividad','evento'=>'Evento','proyecto_rover'=>'Proyecto Rover'] as $v=>$t)<option value="{{ $v }}" @selected(old('tipo', $evento->tipo) === $v)>{{ $t }}</option>@endforeach</select></label>
    <label class="wide">Descripción<textarea name="descripcion" required>{{ old('descripcion', $evento->descripcion) }}</textarea></label>
    <x-campo nombre="fecha_inicio" etiqueta="Inicio" tipo="datetime-local" :valor="old('fecha_inicio', $evento->fecha_inicio?->format('Y-m-d\TH:i'))" requerido />
    <x-campo nombre="fecha_fin" etiqueta="Fin" tipo="datetime-local" :valor="old('fecha_fin', $evento->fecha_fin?->format('Y-m-d\TH:i'))" requerido />
    <x-campo nombre="lugar" etiqueta="Lugar" :valor="$evento->lugar" requerido />
    <x-campo nombre="mapa_url" etiqueta="Enlace de mapa" tipo="url" :valor="$evento->mapa_url" />
    <x-campo nombre="costo" etiqueta="Costo (Bs)" tipo="number" :valor="$evento->costo" />
    <x-campo nombre="cupo" etiqueta="Cupo (vacío = sin límite)" tipo="number" :valor="$evento->cupo" />
    <fieldset><legend>Ramas</legend>@foreach($ramas as $rama)<label><input type="checkbox" name="ramas[]" value="{{ $rama->id }}" @checked(in_array($rama->id, old('ramas', $evento->ramas?->pluck('id')->all() ?? [])))> {{ $rama->nombre }}</label>@endforeach</fieldset>
    <label class="wide">Qué llevar<textarea name="que_llevar">{{ old('que_llevar', $evento->que_llevar) }}</textarea></label>
    <label class="wide">Indicaciones<textarea name="indicaciones">{{ old('indicaciones', $evento->indicaciones) }}</textarea></label>
    <label><input type="checkbox" name="requiere_autorizacion" value="1" @checked(old('requiere_autorizacion', $evento->requiere_autorizacion))> Requiere autorización física</label>
    <label class="wide">Instrucciones de autorización<textarea name="instrucciones_autorizacion">{{ old('instrucciones_autorizacion', $evento->instrucciones_autorizacion) }}</textarea></label>
    <label><input type="checkbox" name="inscripcion_habilitada" value="1" @checked(old('inscripcion_habilitada', $evento->exists ? $evento->inscripcion_habilitada : true))> Inscripción habilitada</label>
    <x-campo nombre="inscripcion_desde" etiqueta="Inscripción desde" tipo="datetime-local" :valor="old('inscripcion_desde', $evento->inscripcion_desde?->format('Y-m-d\TH:i'))" />
    <x-campo nombre="inscripcion_hasta" etiqueta="Inscripción hasta" tipo="datetime-local" :valor="old('inscripcion_hasta', $evento->inscripcion_hasta?->format('Y-m-d\TH:i'))" />
    @unless(auth()->user()->es('miembro'))<label>Estado<select name="estado"><option value="borrador">Borrador</option><option value="publicado" @selected(old('estado', $evento->estado) === 'publicado')>Publicado</option></select></label>@endunless
    <div class="form-actions"><button class="btn">Guardar</button><a class="btn secondary" href="{{ route('eventos.index') }}">Cancelar</a></div>
</form>
@endsection
