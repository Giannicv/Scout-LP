<h2>
    Nuevas aventuras por comenzar.
</h2>
<p>
    Solicitudes de
    {{ $usuario->departamento }}
    .
</p>
@forelse ($inscripciones as $inscripcion)
    <section class="panel">
        <h3>
            {{ $inscripcion->nombre_aspirante }}
            {{ $inscripcion->apellido_aspirante }}
        </h3>
        <p>
            {{ $inscripcion->rama->nombre }}
            ·
            {{ $inscripcion->estado }}
        </p>
        <p>
            Tutor:
            {{ $inscripcion->nombre_tutor }}
            ·
            {{ $inscripcion->telefono_tutor }}
        </p>
        <p>
            {{ $inscripcion->ficha_medica['alergias'] ?? '' }}
        </p>
        <p>
            {{ $inscripcion->motivo_rechazo }}
        </p>
        @if ($inscripcion->estado === 'pendiente')
            <form method="POST"
                data-confirmar="¿Confirmas la resolución de esta inscripción?"
                action="{{ route('coordinador.operacion', ['accion' => 'resolver']) }}">
                @csrf
                <input type="hidden" name="inscripcion_id" value="{{ $inscripcion->id }}">
                <x-campo nombre="unidad_id" etiqueta="Unidad para aprobar" tipo="select"
                    :opciones="$unidades->where('rama_id', $inscripcion->rama_id)->pluck('nombre', 'id')" />
                <x-campo nombre="motivo" etiqueta="Motivo si rechazas" tipo="textarea" />
                <div class="actions">
                    <button name="estado" value="aprobada">
                        Aprobar inscripción
                    </button>
                    <button class="danger" name="estado" value="rechazada">
                        Rechazar
                    </button>
                </div>
            </form>
        @endif
    </section>
@empty
    <p class="empty">
        No hay solicitudes.
    </p>
@endforelse
