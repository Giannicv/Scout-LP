@extends('layouts.panel', ['titulo' => 'Eventos'])
@section('contenido')
<form method="GET" class="panel-toolbar">
    <div class="field"><label for="buscar">Buscar</label><input id="buscar" name="buscar" value="{{ request('buscar') }}" placeholder="Título o lugar"></div>
    <div class="field"><label for="estado">Estado</label><select id="estado" name="estado"><option value="">Todos</option>@foreach(['borrador','propuesto','publicado','rechazado','cancelado','finalizado'] as $estado)<option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ ucfirst($estado) }}</option>@endforeach</select></div>
    <button class="btn secondary">Filtrar</button>
    @if(!$usuario->es('miembro') || $usuario->miembro?->rama?->slug === 'rovers')
        <a class="btn" href="{{ route('eventos.create') }}">{{ $usuario->es('miembro') ? 'Proponer proyecto Rover' : 'Crear evento' }}</a>
    @endif
</form>
@forelse($eventos as $evento)
<article class="card">
    <div class="section-head"><div><h2>{{ $evento->titulo }}</h2><x-estado :valor="$evento->estado" /></div><strong>{{ ucfirst(str_replace('_', ' ', $evento->tipo)) }}</strong></div>
    <div class="event-meta"><span>📅 {{ $evento->fecha_inicio->format('d/m/Y H:i') }}</span><span>📍 {{ $evento->lugar }}</span><span>👥 {{ $evento->inscripciones->where('estado', 'inscrito')->count() }}{{ $evento->cupo ? '/'.$evento->cupo : ' inscritos' }}</span></div>
    <div class="event-branches">@foreach($evento->ramas as $rama)<span class="branch-chip">{{ $rama->nombre }}</span>@endforeach</div>
    <p>{{ $evento->descripcion }}</p>
    <div class="inline-actions">
        @if(!$usuario->es('miembro') && ($usuario->es('dirigente_distrital') || $evento->creado_por === $usuario->id))
            <a class="btn secondary" href="{{ route('eventos.edit', $evento) }}">Editar</a>
            @unless(in_array($evento->estado, ['cancelado','finalizado']))<form method="POST" action="{{ route('eventos.cancelar-evento', $evento) }}" data-confirmar="¿Cancelar este evento? Las inscripciones quedarán cerradas.">@csrf<button class="btn danger">Cancelar evento</button></form>@endunless
        @endif
        @if($evento->estado === 'propuesto' && !$usuario->es('miembro'))
            <form method="POST" action="{{ route('eventos.aprobar', $evento) }}">@csrf<button class="btn">Aprobar y publicar</button></form>
            <form method="POST" action="{{ route('eventos.rechazar', $evento) }}" class="inline-reject">@csrf<input name="motivo" aria-label="Motivo del rechazo" placeholder="Motivo del rechazo" required><button class="btn secondary">Rechazar</button></form>
        @endif
        @if($usuario->es('miembro') && $evento->estado === 'publicado')
            @php($inscripcion = $evento->inscripciones->firstWhere('miembro_id', $usuario->miembro_id))
            @if($inscripcion?->estado === 'inscrito')<form method="POST" action="{{ route('eventos.cancelar', $evento) }}" data-confirmar="¿Cancelar tu inscripción?">@csrf @method('DELETE')<button class="btn secondary">Cancelar inscripción</button></form>
            @else<form method="POST" action="{{ route('eventos.inscribir', $evento) }}">@csrf<button class="btn">Inscribirme</button></form>@endif
        @endif
    </div>
</article>
@empty<div class="empty-state"><h2>No encontramos eventos</h2><p>Ajusta los filtros o crea la primera actividad.</p></div>@endforelse
{{ $eventos->links() }}
@endsection
