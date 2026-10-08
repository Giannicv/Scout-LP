<h2>
    Reconoce cada nuevo paso.
</h2>
<form class="toolbar" method="GET">
    <x-campo nombre="miembro_id" etiqueta="Miembro" tipo="select"
        :valor="request('miembro_id')"
        :opciones="$miembros->mapWithKeys(fn ($miembro) => [$miembro->id => $miembro->nombre.' '.$miembro->apellido])" requerido />
    <button>
        Ver progresión
    </button>
</form>
@php($miembroSeleccionado = $miembros->firstWhere('id', request('miembro_id')) ?? $miembros->first())
@if ($miembroSeleccionado)
    <section class="panel">
        <h3>
            {{ $miembroSeleccionado->nombre }}
        </h3>
        <form method="POST" action="{{ route('dirigente.operacion', ['accion' => 'progresiones']) }}">
            @csrf
            <input type="hidden" name="miembro_id" value="{{ $miembroSeleccionado->id }}">
            @foreach ($requisitos->filter(fn ($requisito) => !$requisito->rama_id
                || $requisito->rama_id === $miembroSeleccionado->rama_id)->groupBy('etapa') as $etapa => $requisitosEtapa)
                <h3>
                    {{ $etapa }}
                </h3>
                @foreach ($requisitosEtapa as $requisito)
                    <label class="check">
                        <input
                            type="checkbox"
                            name="requisitos[]"
                            value="{{ $requisito->id }}"
                    @checked($progresiones->where('miembro_id', $miembroSeleccionado->id)->contains('requisito_id', $requisito->id))>
                        {{ $requisito->nombre }}
                        ·
                        {{ $requisito->tipo }}
                    </label>
                @endforeach
            @endforeach
            <x-campo nombre="fecha" etiqueta="Fecha" tipo="date" :valor="today()" requerido />
            <x-campo nombre="observacion" etiqueta="Observación" tipo="textarea" />
            <button>
                Guardar progresión
            </button>
        </form>
    </section>
@else
    <p>
        No hay miembros asignados.
    </p>
@endif
