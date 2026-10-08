<h2>
    Unidades de
    {{ $usuario->departamento }}
    .
</h2>
<div class="grid two">
    @forelse ($unidades as $unidad)
        <section class="panel">
            <h3>
                {{ $unidad->nombre }}
            </h3>
            <p>
                {{ $unidad->rama->nombre }}
            </p>
            <p>
                Dirigente:
                {{ $unidad->dirigente?->nombre ?? 'Por asignar' }}
            </p>
            <form method="POST" action="{{ route('coordinador.operacion', ['accion' => 'asignar']) }}">
                @csrf
                <input type="hidden" name="unidad_id" value="{{ $unidad->id }}">
                <x-campo nombre="dirigente_id" etiqueta="Dirigente" tipo="select"
                    :valor="$unidad->dirigente_id"
                    :opciones="$dirigentes->mapWithKeys(fn ($dirigente) =>
                    [$dirigente->id => $dirigente->nombre.' '.$dirigente->apellido])" />
                <button>
                    Guardar asignación
                </button>
            </form>
        </section>
    @empty
        <p>
            No hay unidades en este departamento.
        </p>
    @endforelse
</div>
