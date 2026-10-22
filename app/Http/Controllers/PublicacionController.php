<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\ContenidoInstitucional;
use App\Models\Noticia;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicacionController extends Controller
{
    public function __construct(private AuditoriaService $auditoria) {}

    public function index()
    {
        $this->authorize('viewAny', Noticia::class);

        return view('panel.publicaciones', ['noticias' => Noticia::with('categoria')->latest()->paginate(12),
            'categorias' => Categoria::orderBy('nombre')->get(),
            'contenidos' => ContenidoInstitucional::orderBy('clave')->get()]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Noticia::class);
        $datos = $this->validar($request);
        $noticia = Noticia::create($datos + ['autor_id' => $request->user()->id,
            'slug' => Str::slug($datos['titulo']).'-'.Str::lower(Str::random(5)),
            'destacada' => $request->boolean('destacada'),
            'publicado_en' => $datos['estado'] === 'publicado' ? now() : null]);
        $this->auditoria->registrar($request, 'crear', 'noticia', $noticia->id, ['estado' => $noticia->estado]);

        return back()->with('estado', $noticia->destacada
            ? 'Historia guardada y seleccionada para la portada.'
            : 'Publicación guardada.');
    }

    public function update(Request $request, Noticia $noticia)
    {
        $this->authorize('update', $noticia);
        $datos = $this->validar($request);
        $noticia->update($datos + ['destacada' => $request->boolean('destacada'),
            'publicado_en' => $datos['estado'] === 'publicado' ? ($noticia->publicado_en ?? now()) : null]);
        $this->auditoria->registrar($request, 'actualizar', 'noticia', $noticia->id, ['estado' => $noticia->estado]);

        return back()->with('estado', 'Noticia actualizada.');
    }

    public function destroy(Request $request, Noticia $noticia)
    {
        $this->authorize('delete', $noticia);
        $noticia->update(['estado' => 'archivado', 'destacada' => false]);
        $this->auditoria->registrar($request, 'archivar', 'noticia', $noticia->id);

        return back()->with('estado', 'Noticia archivada sin eliminar su historial.');
    }

    public function categoria(Request $request)
    {
        $this->authorize('create', Noticia::class);
        $datos = $request->validate(['nombre' => 'required|string|max:80|unique:categorias,nombre']);
        $categoria = Categoria::create(['nombre' => $datos['nombre'], 'slug' => Str::slug($datos['nombre'])]);
        $this->auditoria->registrar($request, 'crear', 'categoria_noticia', $categoria->id);

        return back()->with('estado', 'Categoría creada.');
    }

    public function contenido(Request $request, ContenidoInstitucional $contenido)
    {
        $this->authorize('create', Noticia::class);
        $datos = $request->validate(['titulo' => 'required|string|max:180', 'contenido' => 'required|string|max:20000']);
        $contenido->update($datos + ['actualizado_por' => $request->user()->id]);
        $this->auditoria->registrar($request, 'actualizar', 'contenido_institucional', $contenido->id);

        return back()->with('estado', 'Contenido actualizado.');
    }

    private function validar(Request $request): array
    {
        return $request->validate(['categoria_id' => 'nullable|exists:categorias,id', 'titulo' => 'required|string|max:180',
            'resumen' => 'required|string|max:1000', 'contenido' => 'required|string|max:20000',
            'imagen_url' => 'nullable|url|max:255', 'estado' => 'required|in:borrador,publicado',
            'destacada' => 'nullable|boolean']);
    }
}
