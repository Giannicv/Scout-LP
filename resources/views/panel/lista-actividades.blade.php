@forelse ($listaActividades as $actividad)
    <article class="list-item">
        <strong>
            {{ $actividad->titulo }}
        </strong>
        <span>
            {{ $actividad->fecha->format('d/m/Y') }}
            ·
            {{ substr($actividad->hora, 0, 5) }}
            ·
            {{ $actividad->lugar }}
        </span>
        <p>
            {{ $actividad->descripcion }}
        </p>
        @if ($actividad->requiere_autorizacion)
            <span class="tag">
                Requiere autorización
            </span>
        @endif
    </article>
@empty
    <p class="empty">
        Pronto compartiremos nuevas actividades.
    </p>
@endforelse
