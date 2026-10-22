@extends('layouts.panel', ['titulo' => 'Notificaciones'])
@section('contenido')
@forelse($notificaciones as $notificacion)<article class="card"><h2>{{ $notificacion->data['titulo'] ?? 'Notificación' }}</h2><p>{{ $notificacion->data['mensaje'] ?? '' }}</p><small>{{ $notificacion->created_at->diffForHumans() }}</small>
@if(!empty($notificacion->data['url']))<p><a class="text-link" href="{{ $notificacion->data['url'] }}">Ver detalle →</a></p>@endif
@unless($notificacion->read_at)<form method="POST" action="{{ route('notificaciones.leer', $notificacion->id) }}">@csrf<button class="btn secondary">Marcar leída</button></form>@endunless</article>
@empty<p>No tienes notificaciones.</p>@endforelse
{{ $notificaciones->links() }}
@endsection
