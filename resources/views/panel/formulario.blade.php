@extends('layouts.panel')
@section('contenido')
    <section class="panel">
        <h2>
            {{ $registro->exists ? 'Editar registro' : 'Agregar registro' }}
        </h2>
        @if ($recurso === 'mensajes')
            <h3>
                {{ $registro->asunto }}
            </h3>
            <p>
                {{ $registro->nombre }}
                ·
                {{ $registro->email }}
            </p>
            <p class="message-text">
                {{ $registro->mensaje }}
            </p>
            <a href="mailto:{{ $registro->email }}">
                Responder por correo
            </a>
        @endif
        <form method="POST"
            action="{{ $registro->exists
        ? route($rolRuta.'.gestion.registros.update', ['recurso' => $recurso, 'registro' => $registro->id])
        : route($rolRuta.'.gestion.registros.store', ['recurso' => $recurso]) }}">
            @csrf
            @if ($registro->exists)
                @method('PUT')
            @endif
            @foreach ($configuracion['campos'] as $nombreCampo => $campo)
                @php
                                $valorCampo = $nombreCampo === 'actividades_texto'
                                    ? implode("\n", $registro->actividades ?? []) : $registro->getAttribute($nombreCampo);
                                if ($campo['tipo'] === 'checkbox' && !$registro->exists) {
                                    $valorCampo = $nombreCampo === 'activo';
                                }
                                if ($campo['tipo'] === 'time' && $valorCampo) {
                                    $valorCampo = substr($valorCampo, 0, 5);
                                }
                            @endphp
                <x-campo
                    :nombre="$nombreCampo"
                    :etiqueta="$campo['etiqueta']"
                    :tipo="$campo['tipo']"
                    :valor="$valorCampo"
                    :requerido="in_array('required', $campo['reglas'])" />
            @endforeach
            <div class="actions">
                <button>
                    Guardar cambios
                </button>
                <a class="btn secondary" href="{{ route($rolRuta.'.'.$recurso) }}">
                    Cancelar
                </a>
            </div>
        </form>
    </section>
@endsection
