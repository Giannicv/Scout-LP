<div class="section-head">
    <div>
        <p class="eyebrow">
            TU COMUNIDAD SCOUT
        </p>
        <h2>
            Un nuevo camino comienza.
        </h2>
        <p class="muted">
            Completa los cuatro pasos. Tu coordinador revisará la solicitud.
        </p>
    </div>
</div>
<div class="stepper">
    @foreach (['Datos del joven', 'Rama sugerida', 'Ficha médica', 'Confirmación'] as $paso)
        <span data-indicador-paso="{{ $loop->index }}">
            {{ $loop->iteration }}
            .
            {{ $paso }}
        </span>
    @endforeach
</div>
<section class="panel">
    <form data-inscripcion method="POST"
        action="{{ route('familia.operacion', ['accion' => 'inscribir']) }}">
        @csrf
        <section data-paso>
            <x-campo nombre="nombre" etiqueta="Nombre del joven" requerido />
            <x-campo nombre="apellido" etiqueta="Apellidos del joven" requerido />
            <x-campo nombre="edad" etiqueta="Edad (7–21 años)" tipo="number" requerido />
            <x-campo nombre="departamento" etiqueta="Departamento" tipo="departamentos" requerido />
        </section>
        <section data-paso>
            <h3>
                Rama sugerida
            </h3>
            <p data-rama-sugerida>
                La rama se determina según la edad.
            </p>
            <p>
                La unidad se asignará al aprobar la inscripción.
            </p>
        </section>
        <section data-paso>
            <x-campo nombre="alergias" etiqueta="Alergias o indicaciones médicas" tipo="textarea" />
            <x-campo nombre="emergencia" etiqueta="Nombre y teléfono del contacto de emergencia" requerido />
        </section>
        <section data-paso>
            <h3>
                Confirma tu inscripción
            </h3>
            <p data-resumen-inscripcion>
            </p>
            <x-campo nombre="confirmacion" etiqueta="Confirmo que los datos son correctos" tipo="checkbox" requerido />
        </section>
        <div class="actions">
            <button type="button" class="btn secondary" data-paso-anterior hidden>
                Anterior
            </button>
            <button type="button" data-paso-siguiente hidden>
                Continuar
            </button>
            <button type="submit" data-enviar-inscripcion>
                Enviar inscripción
            </button>
        </div>
    </form>
</section>
