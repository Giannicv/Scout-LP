@extends('layouts.panel', ['titulo' => 'Notificaciones'])
@section('contenido')
    <header class="notifications-heading">
        <div>
            <h2>Tu actividad reciente</h2>
            <p>Consulta solicitudes, decisiones y avisos relacionados con tu cuenta.</p>
        </div>
        @if ($sinLeer > 0)
            <form method="POST" action="{{ route('notificaciones.leer-todas') }}"
                data-confirmar="{{ $sinLeer === 1 ? 'La notificación pendiente pasará a leída.' : 'Las '.$sinLeer.' notificaciones pendientes pasarán a leídas.' }} Podrás seguir consultándolas en la pestaña Leídas.">
                @csrf
                <button class="btn secondary" type="submit">Marcar todas como leídas</button>
            </form>
        @endif
    </header>

    <nav class="notifications-filters" aria-label="Filtrar notificaciones">
        @foreach (['todas' => ['Todas', $total], 'sin_leer' => ['Sin leer', $sinLeer], 'leidas' => ['Leídas', $total - $sinLeer]] as $clave => [$etiqueta, $cantidad])
            <a href="{{ route('notificaciones.index', $clave === 'todas' ? [] : ['estado' => $clave]) }}"
                @class(['active' => $filtro === $clave]) @if($filtro === $clave) aria-current="page" @endif>
                {{ $etiqueta }} <span>{{ $cantidad }}</span>
            </a>
        @endforeach
    </nav>

    @if ($notificaciones->isNotEmpty())
        <div class="notifications-list">
            @foreach ($notificaciones as $notificacion)
                @php
                    $sinLeerEsta = $notificacion->read_at === null;
                    $tipo = str_contains($notificacion->type, 'Solicitud') ? 'Solicitud' : (str_contains($notificacion->type, 'Evento') ? 'Evento' : 'Aviso');
                    $destino = $notificacion->data['url'] ?? null;
                @endphp
                <article @class(['notification-item', 'unread' => $sinLeerEsta]) aria-labelledby="notificacion-{{ $notificacion->id }}">
                    <span class="notification-symbol" aria-hidden="true"><x-icono nombre="bell" /></span>
                    <div class="notification-content">
                        <div class="notification-topline">
                            <span class="notification-kind">{{ $tipo }}</span>
                            <time datetime="{{ $notificacion->created_at->toIso8601String() }}" title="{{ $notificacion->created_at->translatedFormat('d M Y, H:i') }}">{{ $notificacion->created_at->diffForHumans() }}</time>
                        </div>
                        <h3 id="notificacion-{{ $notificacion->id }}">{{ $notificacion->data['titulo'] ?? 'Notificación' }}</h3>
                        @if (! empty($notificacion->data['mensaje'])) <p>{{ $notificacion->data['mensaje'] }}</p> @endif
                        <div class="notification-actions">
                            @if (is_string($destino) && $destino !== '')
                                <form method="POST" action="{{ route('notificaciones.abrir', $notificacion->id) }}">
                                    @csrf <button type="submit" class="notification-open">Ver detalle</button>
                                </form>
                            @endif
                            @if ($sinLeerEsta)
                                <form method="POST" action="{{ route('notificaciones.leer', $notificacion->id) }}">
                                    @csrf <button type="submit" class="notification-mark">Marcar como leída</button>
                                </form>
                            @else
                                <span class="notification-read">Leída</span>
                            @endif
                        </div>
                    </div>
                    @if ($sinLeerEsta) <span class="notification-unread-dot" aria-label="Sin leer"></span> @endif
                </article>
            @endforeach
        </div>
        <div class="pagination">{{ $notificaciones->links() }}</div>
    @else
        <div class="notifications-empty">
            <x-icono nombre="bell" />
            <h3>{{ match ($filtro) { 'sin_leer' => 'Estás al día', 'leidas' => 'Todavía no hay notificaciones leídas', default => 'Todavía no hay notificaciones' } }}</h3>
            <p>{{ match ($filtro) { 'sin_leer' => 'Cuando llegue un aviso nuevo, aparecerá aquí.', 'leidas' => 'Las notificaciones que abras o marques como leídas aparecerán aquí.', default => 'Aquí verás las novedades de tus solicitudes y actividades.' } }}</p>
            @if ($filtro !== 'todas') <a class="btn secondary" href="{{ route('notificaciones.index') }}">Ver todas</a> @endif
        </div>
    @endif
@endsection
