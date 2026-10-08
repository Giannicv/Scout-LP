<h2>
    Cada paso deja huella.
</h2>
<p>
    Tu dirigente registra los logros; aquí puedes seguir tu avance.
</p>
<section class="panel">
    <h3>
        Progresión
    </h3>
    <progress
        class="progress" max="{{ max(1, $requisitos->count()) }}"
        value="{{ $progresiones->count() }}"
        aria-label="Progreso general">
    </progress>
    @forelse ($progresiones as $progresion)
        <article class="list-item">
            <strong>
                {{ $progresion->requisito?->nombre }}
            </strong>
            <p>
                {{ $progresion->fecha->format('d/m/Y') }}
                ·
                {{ $progresion->observacion }}
            </p>
        </article>
    @empty
        <p>
            Tu primera insignia te espera.
        </p>
    @endforelse
</section>
<section class="panel">
    <h2>
        Mi asistencia
    </h2>
    @foreach ($asistencias as $asistencia)
        <p>
            {{ $asistencia->fecha->format('d/m/Y') }}
            ·
            {{ $asistencia->actividad->titulo }}
            ·
            {{ $asistencia->estado }}
        </p>
    @endforeach
</section>
