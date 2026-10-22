@extends('layouts.panel', ['titulo' => 'Biblioteca digital'])
@section('contenido')
@if(auth()->user()->es('dirigente_distrital'))
<form method="POST" action="{{ route('biblioteca.store') }}" enctype="multipart/form-data" class="card">@csrf
<h2>Publicar recurso</h2><label>Categoría<select name="categoria_recurso_id" required>@foreach($categorias as $categoria)<option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>@endforeach</select></label>
<x-campo nombre="titulo" etiqueta="Título" requerido /><x-campo nombre="descripcion" etiqueta="Descripción" tipo="textarea" />
<label>Archivo <input type="file" name="archivo" required></label><button class="btn">Publicar</button></form>
@endif
@foreach($categorias as $categoria)<section><h2>{{ $categoria->nombre }}</h2>
@forelse($categoria->recursos as $recurso)<article class="card"><h3>{{ $recurso->titulo }}</h3><p>{{ $recurso->descripcion }}</p><a class="btn" href="{{ route('biblioteca.download', $recurso) }}">Descargar</a>
@if(auth()->user()->es('dirigente_distrital'))<form method="POST" action="{{ route('biblioteca.destroy', $recurso) }}" data-confirmar="¿Archivar este recurso?">@csrf @method('DELETE')<button class="btn secondary">Archivar</button></form>@endif</article>
@empty<p>Sin recursos publicados.</p>@endforelse</section>@endforeach
@endsection
