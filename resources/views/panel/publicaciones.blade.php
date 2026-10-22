@extends('layouts.panel', ['titulo' => 'Historias y contenido'])
@section('contenido')
<div class="section-head"><div><p class="eyebrow">CONTENIDO PÚBLICO</p><h2>Historias que inspiran</h2><p class="muted">Las publicaciones marcadas para portada aparecen en la sección “Historias que inspiran” del inicio.</p></div><a class="btn secondary small" href="{{ route('inicio') }}#historias">Ver sección pública</a></div>
<div class="panel-grid">
<form method="POST" action="{{ route('publicaciones.noticias.store') }}" class="card">@csrf<h2>Nueva historia o noticia</h2>
<x-campo nombre="titulo" etiqueta="Título" requerido /><x-campo nombre="resumen" etiqueta="Resumen" tipo="textarea" requerido /><x-campo nombre="contenido" etiqueta="Contenido" tipo="textarea" requerido />
<label>Categoría<select name="categoria_id"><option value="">Sin categoría</option>@foreach($categorias as $categoria)<option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>@endforeach</select></label>
<x-campo nombre="imagen_url" etiqueta="URL de imagen" tipo="url" /><label><input type="checkbox" name="destacada" value="1"> Mostrar en “Historias que inspiran”</label>
<label>Estado<select name="estado"><option value="borrador">Borrador</option><option value="publicado">Publicada</option></select></label><button class="btn">Guardar publicación</button></form>
<form method="POST" action="{{ route('publicaciones.categorias.store') }}" class="card">@csrf<h2>Nueva categoría</h2><x-campo nombre="nombre" etiqueta="Nombre" requerido /><button class="btn">Crear categoría</button></form>
</div>
<section><h2>Publicaciones</h2>@forelse($noticias as $noticia)<details class="card"><summary><strong>{{ $noticia->titulo }}</strong> · <x-estado :valor="$noticia->estado" /> @if($noticia->destacada)<span class="branch-chip">En portada</span>@endif</summary>
<form method="POST" action="{{ route('publicaciones.noticias.update', $noticia) }}">@csrf @method('PUT')
<x-campo nombre="titulo" etiqueta="Título" :valor="$noticia->titulo" requerido /><x-campo nombre="resumen" etiqueta="Resumen" tipo="textarea" :valor="$noticia->resumen" requerido /><x-campo nombre="contenido" etiqueta="Contenido" tipo="textarea" :valor="$noticia->contenido" requerido />
<label>Categoría<select name="categoria_id"><option value="">Sin categoría</option>@foreach($categorias as $categoria)<option value="{{ $categoria->id }}" @selected($noticia->categoria_id === $categoria->id)>{{ $categoria->nombre }}</option>@endforeach</select></label>
<x-campo nombre="imagen_url" etiqueta="URL de imagen" tipo="url" :valor="$noticia->imagen_url" /><label><input type="checkbox" name="destacada" value="1" @checked($noticia->destacada)> Mostrar en “Historias que inspiran”</label>
<label>Estado<select name="estado"><option value="borrador" @selected($noticia->estado === 'borrador')>Borrador</option><option value="publicado" @selected($noticia->estado === 'publicado')>Publicada</option></select></label>
<button class="btn">Actualizar</button></form><form method="POST" action="{{ route('publicaciones.noticias.destroy', $noticia) }}" data-confirmar="¿Archivar esta noticia?" class="inline-actions">@csrf @method('DELETE')<button class="btn danger">Archivar</button></form>
</details>@empty<div class="empty-state">No hay noticias.</div>@endforelse
{{ $noticias->links() }}</section>
<section><h2>Contenido institucional</h2>@foreach($contenidos as $contenido)<form method="POST" action="{{ route('publicaciones.contenido.update', $contenido) }}" class="card">@csrf @method('PUT')
<small>{{ $contenido->clave }}</small><x-campo nombre="titulo" etiqueta="Título" :valor="$contenido->titulo" requerido /><label>Contenido<textarea name="contenido" required>{{ $contenido->contenido }}</textarea></label><button class="btn">Actualizar</button></form>@endforeach</section>
@endsection
