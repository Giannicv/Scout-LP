<h2>
    Cada encuentro cuenta.
</h2>
<p>
    Elige una actividad, marca a todos y guarda la asistencia.
</p>
<form class="toolbar" method="GET">
    <x-campo nombre="actividad_id" etiqueta="Actividad" tipo="select"
        :valor="request('actividad_id')"
        :opciones="$actividades->pluck('titulo', 'id')" requerido />
    <button>
        Seleccionar actividad
    </button>
</form>
@php($actividadSeleccionada = $actividades->firstWhere('id', request('actividad_id')) ?? $actividades->first())
@if ($actividadSeleccionada)
    <section class="panel">
        <h3>
            {{ $actividadSeleccionada->titulo }}
        </h3>
        <form method="POST" action="{{ route('dirigente.operacion', ['accion' => 'asistencia']) }}">
            @csrf
            <input type="hidden" name="actividad_id" value="{{ $actividadSeleccionada->id }}">
            <x-campo nombre="fecha" etiqueta="Fecha" tipo="date"
                :valor="$actividadSeleccionada->fecha" requerido />
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>
                                Miembro
                            </th>
                            <th>
                                Asistencia
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($miembros->where('unidad_id', $actividadSeleccionada->unidad_id) as $miembro)
                            @php($registroAsistencia = $asistencias->where('actividad_id', $actividadSeleccionada->id)
                                ->firstWhere('miembro_id', $miembro->id))
                            <tr>
                                <td>
                                    {{ $miembro->nombre }}
                                    {{ $miembro->apellido }}
                                </td>
                                <td>
                                    <select
                                        name="asistencias[{{ $miembro->id }}]"
                                        aria-label="Asistencia de {{ $miembro->nombre }}" required>
                                        <option value="">
                                            Seleccionar
                                        </option>
                                        @foreach (['presente', 'ausente', 'justificado'] as $estadoAsistencia)
                                            <option
                                                value="{{ $estadoAsistencia }}" @selected($registroAsistencia?->estado === $estadoAsistencia)>
                                                {{ ucfirst($estadoAsistencia) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <button>
                Guardar asistencia
            </button>
        </form>
    </section>
@endif
<section class="panel">
    <h2>
        Historial por fecha
    </h2>
    @foreach ($asistencias->groupBy('actividad_id') as $registrosActividad)
        <p>
            {{ $registrosActividad->first()->actividad->titulo }}
            ·
            {{ $registrosActividad->where('estado', 'presente')->count() }}
            presentes ·
            {{ $registrosActividad->where('estado', 'ausente')->count() }}
            ausentes ·
            {{ $registrosActividad->where('estado', 'justificado')->count() }}
            justificados
        </p>
    @endforeach
</section>
