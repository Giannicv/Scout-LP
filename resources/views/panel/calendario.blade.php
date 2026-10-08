<h2>
    Encuentros por vivir.
</h2>
<p>
    Actividades y campamentos de tu unidad.
</p>
@php
    $mesSeleccionado = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', request('mes', ''))
        ? \Carbon\Carbon::createFromFormat('!Y-m', request('mes')) : today()->startOfMonth();
    $actividadesMes = $actividades->filter(fn ($actividad) =>
        $actividad->fecha->format('Y-m') === $mesSeleccionado->format('Y-m'));
@endphp
<div class="toolbar">
    <a
        class="btn secondary"
        href="{{ route('miembro.calendario', ['mes' => $mesSeleccionado->copy()->subMonth()->format('Y-m')]) }}">
        ‹
    </a>
    <strong>
        {{ $mesSeleccionado->translatedFormat('F Y') }}
    </strong>
    <a
        class="btn secondary"
        href="{{ route('miembro.calendario', ['mes' => $mesSeleccionado->copy()->addMonth()->format('Y-m')]) }}">
        ›
    </a>
    <button data-vista-calendario="lista">
        Lista
    </button>
    <button data-vista-calendario="mes">
        Vista mensual
    </button>
</div>
<section class="panel" id="calendario-lista">
    @include('panel.lista-actividades', ['listaActividades' => $actividadesMes])
</section>
<section class="panel" id="calendario-mes" hidden>
    <div class="calendar-grid">
        @foreach (['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] as $diaSemana)
            <div class="calendar-label">
                {{ $diaSemana }}
            </div>
        @endforeach
        @for ($espacioInicial = 1; $espacioInicial < $mesSeleccionado->dayOfWeekIso; $espacioInicial++)
            <div>
            </div>
        @endfor
        @for ($numeroDia = 1; $numeroDia <= $mesSeleccionado->daysInMonth; $numeroDia++)
            <div class="calendar-day">
                <strong>
                    {{ $numeroDia }}
                </strong>
                @foreach ($actividadesMes->filter(fn ($actividad) => $actividad->fecha->day === $numeroDia) as $actividad)
                    <div class="calendar-event">
                        {{ substr($actividad->hora, 0, 5) }}
                        {{ $actividad->titulo }}
                        <br>
                        {{ $actividad->lugar }}
                    </div>
                @endforeach
            </div>
        @endfor
    </div>
</section>
