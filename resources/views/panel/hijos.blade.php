<div class="grid two">
    @forelse ($miembros as $miembro)
        <article class="card child-card">
            <span class="tag">
                {{ $miembro->rama?->nombre }}
            </span>
            <h3>
                {{ $miembro->nombre }}
                {{ $miembro->apellido }}
            </h3>
            <p>
                {{ $miembro->unidad?->nombre ?? 'Unidad por asignar' }}
            </p>
            <p>
                Código
                {{ $miembro->codigo }}
            </p>
            <p>
                Logros:
                {{ $progresiones->where('miembro_id', $miembro->id)->count() }}
                · Encuentros registrados:
                {{ $asistencias->where('miembro_id', $miembro->id)->count() }}
            </p>
            <button data-abrir-dialogo="ficha-{{ $miembro->id }}">
                Ver ficha
            </button>
        </article>
        <dialog id="ficha-{{ $miembro->id }}">
            <h2>
                {{ $miembro->nombre }}
                {{ $miembro->apellido }}
            </h2>
            @php($ficha = \App\Models\FichaMedica::find($miembro->id))
            <form method="POST" action="{{ route('familia.operacion', ['accion' => 'medica']) }}">
                @csrf
                <input type="hidden" name="miembro_id" value="{{ $miembro->id }}">
                <x-campo nombre="alergias" etiqueta="Alergias o indicaciones médicas" tipo="textarea"
                    :valor="$ficha?->alergias" />
                <x-campo nombre="contacto_emergencia" etiqueta="Contacto de emergencia"
                    :valor="$ficha?->contacto_emergencia" requerido />
                <button>
                    Guardar ficha médica
                </button>
            </form>
            <h3>
                Progresión
            </h3>
            @foreach ($progresiones->where('miembro_id', $miembro->id) as $progresion)
                <p>
                    {{ $progresion->requisito?->nombre }}
                    ·
                    {{ $progresion->fecha->format('d/m/Y') }}
                </p>
            @endforeach
            <h3>
                Asistencia
            </h3>
            @foreach ($asistencias->where('miembro_id', $miembro->id) as $asistencia)
                <p>
                    {{ $asistencia->fecha->format('d/m/Y') }}
                    ·
                    {{ $asistencia->estado }}
                </p>
            @endforeach
            <button class="btn secondary" data-cerrar-dialogo>
                Cerrar
            </button>
        </dialog>
    @empty
        <p class="empty">
            Tu familia comienza aquí. Vincula o inscribe a tu primer hijo/a.
        </p>
    @endforelse
</div>
@if ($pagina === 'hijos')
    <section class="panel">
        <h2>
            Vincular hijo/a
        </h2>
        <form method="POST" action="{{ route('familia.operacion', ['accion' => 'vincular']) }}">
            @csrf
            <x-campo nombre="nombre" etiqueta="Nombre completo del miembro" requerido />
            <x-campo nombre="codigo" etiqueta="Código de miembro" requerido />
            <button>
                Vincular hijo/a
            </button>
        </form>
    </section>
@endif
