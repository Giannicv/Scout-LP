@extends('layouts.panel', ['titulo' => $evento->exists ? 'Editar evento' : 'Nuevo evento'])
@section('contenido')
    <div class="form-heading">
        <div>
            <p class="eyebrow">GESTIÓN DE EVENTOS</p>
            <h2>{{ $evento->exists ? 'Actualiza la información del evento' : 'Crea una experiencia bien informada' }}</h2>
            <p class="muted">Las fechas de inscripción deben quedar dentro del periodo anterior al inicio del evento.</p>
        </div>
        <a class="btn secondary" href="{{ $evento->exists ? route('eventos.show', $evento) : route('eventos.index') }}">Volver</a>
    </div>

    <form method="POST"
        action="{{ $evento->exists ? route('eventos.update', $evento) : route('eventos.store') }}"
        class="card form-grid event-form" enctype="multipart/form-data">
        @csrf
        @if ($evento->exists) @method('PUT') @endif

        <h3 class="form-section-title">Información general</h3>
        <x-campo nombre="titulo" etiqueta="Título" :valor="$evento->titulo" requerido />
        <label>Tipo
            <select name="tipo" required>
                @foreach (['actividad' => 'Actividad', 'evento' => 'Evento', 'proyecto_rover' => 'Proyecto Rover'] as $valor => $texto)
                    <option value="{{ $valor }}" @selected(old('tipo', $evento->tipo) === $valor)>{{ $texto }}</option>
                @endforeach
            </select>
        </label>
        <label class="wide">Descripción completa
            <textarea name="descripcion" rows="6" required>{{ old('descripcion', $evento->descripcion) }}</textarea>
        </label>
        <div class="field wide">
            <label for="imagen">Imagen del evento</label>
            <input id="imagen" type="file" name="imagen" accept="image/jpeg,image/png,image/webp">
            <small>JPG, PNG o WebP, máximo 5 MB. Se recomienda una imagen horizontal.</small>
            @error('imagen')<span class="error" role="alert">{{ $message }}</span>@enderror
        </div>
        @if ($evento->imagen_url)
            <div class="event-image-preview wide">
                <x-imagen :ruta="$evento->imagen_url" :descripcion="$evento->titulo" />
                <span>Imagen actual</span>
            </div>
        @endif

        <h3 class="form-section-title">Fecha y ubicación</h3>
        <x-campo nombre="fecha_inicio" etiqueta="Inicio" tipo="datetime-local" :valor="old('fecha_inicio', $evento->fecha_inicio?->format('Y-m-d\TH:i'))" requerido />
        <x-campo nombre="fecha_fin" etiqueta="Fin" tipo="datetime-local" :valor="old('fecha_fin', $evento->fecha_fin?->format('Y-m-d\TH:i'))" requerido />
        <x-campo nombre="lugar" etiqueta="Lugar" :valor="$evento->lugar" requerido />
        <x-campo nombre="mapa_url" etiqueta="Enlace de mapa" tipo="url" :valor="$evento->mapa_url" />
        <x-campo nombre="costo" etiqueta="Costo (Bs)" tipo="number" :valor="$evento->costo" />
        <x-campo nombre="cupo" etiqueta="Cupo (vacío = sin límite)" tipo="number" :valor="$evento->cupo" />

        <h3 class="form-section-title">Participantes y preparación</h3>
        <fieldset>
            <legend>Ramas convocadas</legend>
            @foreach ($ramas as $rama)
                <label>
                    <input type="checkbox" name="ramas[]" value="{{ $rama->id }}"
                        @checked(in_array($rama->id, old('ramas', $evento->ramas?->pluck('id')->all() ?? [])))>
                    {{ $rama->nombre }} · {{ $rama->edad_minima }} a {{ $rama->edad_maxima }} años
                </label>
            @endforeach
            @error('ramas')<span class="error" role="alert">{{ $message }}</span>@enderror
        </fieldset>
        <label class="wide">Qué llevar
            <textarea name="que_llevar" rows="4">{{ old('que_llevar', $evento->que_llevar) }}</textarea>
        </label>
        <label class="wide">Indicaciones adicionales
            <textarea name="indicaciones" rows="4">{{ old('indicaciones', $evento->indicaciones) }}</textarea>
        </label>
        <label>
            <input type="hidden" name="requiere_autorizacion" value="0">
            <input type="checkbox" name="requiere_autorizacion" value="1" @checked(old('requiere_autorizacion', $evento->requiere_autorizacion))>
            Requiere autorización física
        </label>
        <label class="wide">Instrucciones de autorización
            <textarea name="instrucciones_autorizacion" rows="3">{{ old('instrucciones_autorizacion', $evento->instrucciones_autorizacion) }}</textarea>
        </label>

        <h3 class="form-section-title">Ventana de inscripción</h3>
        <label>
            <input type="hidden" name="inscripcion_habilitada" value="0">
            <input type="checkbox" name="inscripcion_habilitada" value="1"
                @checked(old('inscripcion_habilitada', $evento->inscripcion_habilitada))>
            Permitir inscripciones
        </label>
        <div></div>
        <x-campo nombre="inscripcion_desde" etiqueta="Inscripción desde" tipo="datetime-local" :valor="old('inscripcion_desde', $evento->inscripcion_desde?->format('Y-m-d\TH:i'))" />
        <x-campo nombre="inscripcion_hasta" etiqueta="Inscripción hasta" tipo="datetime-local" :valor="old('inscripcion_hasta', $evento->inscripcion_hasta?->format('Y-m-d\TH:i'))" />
        <p class="form-hint wide">Si no defines una fecha de cierre, las inscripciones terminarán automáticamente al comenzar el evento.</p>

        @unless (auth()->user()->es('miembro'))
            <label>Estado de publicación
                <select name="estado">
                    <option value="borrador">Borrador</option>
                    <option value="publicado" @selected(old('estado', $evento->estado) === 'publicado')>Publicado</option>
                </select>
            </label>
        @endunless
        <div class="form-actions">
            <button class="btn">Guardar evento</button>
            <a class="btn secondary" href="{{ $evento->exists ? route('eventos.show', $evento) : route('eventos.index') }}">Cancelar</a>
        </div>
    </form>
@endsection
