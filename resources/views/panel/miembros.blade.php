@extends('layouts.panel', ['titulo' => 'Miembros'])
@section('contenido')
<form method="GET" class="panel-toolbar">
    <div class="field"><label for="buscar">Buscar</label><input id="buscar" name="buscar" value="{{ request('buscar') }}" placeholder="Nombre o apellido"></div>
    <div class="field"><label for="rama">Rama</label><select id="rama" name="rama"><option value="">Todas</option>@foreach($ramas as $rama)<option value="{{ $rama->id }}" @selected(request('rama') == $rama->id)>{{ $rama->nombre }}</option>@endforeach</select></div>
    <button class="btn secondary">Filtrar</button>
</form>
<div class="table-wrap"><table><thead><tr><th>Miembro</th><th>Grupo</th><th>Rama</th><th>Cuenta</th><th>Contactos</th><th>Acción</th></tr></thead><tbody>
@forelse($miembros as $miembro)<tr><td><strong>{{ $miembro->nombre }} {{ $miembro->apellido }}</strong></td><td>{{ $miembro->grupo->nombre }}</td><td><span class="branch-chip">{{ $miembro->rama->nombre }}</span></td>
<td>@if($miembro->usuario)<x-estado :valor="$miembro->usuario->estado" />@else<span class="muted">Sin cuenta</span>@endif</td><td>{{ $miembro->contactos->pluck('nombre')->join(', ') ?: '—' }}</td><td>
@if($miembro->usuario)<form method="POST" action="{{ route('usuarios.estado', $miembro->usuario) }}" data-confirmar="¿Cambiar el estado de esta cuenta?">@csrf
<input type="hidden" name="estado" value="{{ $miembro->usuario->estado === 'activo' ? 'suspendido' : 'activo' }}">
<button class="btn secondary small">{{ $miembro->usuario->estado === 'activo' ? 'Suspender' : 'Activar' }}</button></form>@endif
</td></tr>@empty<tr><td colspan="6" class="empty">No hay miembros para estos filtros.</td></tr>@endforelse</tbody></table></div>
{{ $miembros->links() }}
@endsection
