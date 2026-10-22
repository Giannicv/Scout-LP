@extends('layouts.panel', ['titulo' => 'Solicitudes de acceso'])
@section('contenido')
@forelse($solicitudes as $solicitud)
<article class="card"><h2>{{ $solicitud->nombre }} {{ $solicitud->apellido }}</h2><x-estado :valor="$solicitud->estado" />
<p>{{ $solicitud->email }} · {{ str_replace('_', ' ', $solicitud->rol->nombre) }} · {{ $solicitud->grupo?->nombre }}</p>
@if($solicitud->miembro?->rama)<p>Rama: {{ $solicitud->miembro->rama->nombre }}</p>@endif
<div class="inline-actions"><form method="POST" action="{{ route('solicitudes.resolver', $solicitud) }}">@csrf<input type="hidden" name="decision" value="aprobar"><button class="btn">Aprobar</button></form>
<form method="POST" action="{{ route('solicitudes.resolver', $solicitud) }}" class="inline-reject">@csrf<input type="hidden" name="decision" value="rechazar"><input name="motivo" aria-label="Motivo del rechazo" placeholder="Motivo del rechazo" required><button class="btn secondary">Rechazar</button></form></div>
</article>
@empty<p>No hay solicitudes pendientes en tu ámbito.</p>@endforelse
{{ $solicitudes->links() }}
@endsection
