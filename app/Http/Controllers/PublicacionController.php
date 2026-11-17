<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\ContenidoInstitucional;
use App\Models\Noticia;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PublicacionController extends Controller
{
    public function __construct(private AuditoriaService $auditoria) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Noticia::class);
        $buscar = $request->query('buscar', '');
        $buscar = is_string($buscar) ? trim(mb_substr($buscar, 0, 100)) : '';
        $estado = in_array($request->query('estado'), ['borrador', 'publicado', 'archivado'], true)
            ? $request->query('estado') : '';
        $categoria = $request->query('categoria');
        $categoria = is_scalar($categoria) && ctype_digit((string) $categoria) ? (string) $categoria : '';
        $base = Noticia::query();
        if ($buscar !== '') {
            $termino = '%'.mb_strtolower($buscar).'%';
            $base->where(fn ($q) => $q->whereRaw('LOWER(titulo) LIKE ?', [$termino])
                ->orWhereRaw('LOWER(resumen) LIKE ?', [$termino]));
        }
        if ($categoria !== '') {
            $base->where('categoria_id', $categoria);
        }
        $conteos = (clone $base)->selectRaw('estado, COUNT(*) as total')->groupBy('estado')->pluck('total', 'estado');
        $consulta = (clone $base)->with('categoria');
        if ($estado !== '') {
            $consulta->where('estado', $estado);
        }

        return view('panel.publicaciones', [
            'noticias' => $consulta->orderByDesc('updated_at')->paginate(12)->withQueryString(),
            'categorias' => Categoria::withCount('noticiasPorCategoriaId')->orderBy('nombre')->get(),
            'contenidos' => ContenidoInstitucional::orderBy('clave')->get(),
            'conteos' => $conteos,
            'buscar' => $buscar,
            'estadoFiltro' => $estado,
            'categoriaFiltro' => $categoria,
            'totalPublicaciones' => Noticia::count(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Noticia::class);
        $datos = $this->validar($request);
        $imagenNueva = null;
        try {
            $noticia = DB::transaction(function () use ($request, $datos, &$imagenNueva) {
                if ($imagenNueva = $this->guardarImagen($request)) {
                    $datos['imagen_url'] = $imagenNueva;
                }
                $noticia = Noticia::create($datos + ['autor_id' => $request->user()->id,
                    'slug' => Str::slug($datos['titulo']).'-'.Str::lower(Str::random(5)),
                    'destacada' => $datos['estado'] === 'publicado' && $request->boolean('destacada'),
                    'publicado_en' => $datos['estado'] === 'publicado' ? now() : null]);
                $this->auditoria->registrar($request, 'crear', 'noticia', $noticia->id, ['estado' => $noticia->estado]);

                return $noticia;
            });
        } catch (\Throwable $e) {
            $this->eliminarImagenLocal($imagenNueva);
            throw $e;
        }

        return redirect()->route('publicaciones.index')->with('estado', match (true) {
            $noticia->destacada => 'Historia publicada en la portada.',
            $noticia->estado === 'publicado' => 'Noticia publicada.',
            default => 'Borrador guardado.',
        });
    }

    public function update(Request $request, Noticia $noticia)
    {
        $this->authorize('update', $noticia);
        if ($noticia->estado === 'archivado') {
            throw ValidationException::withMessages(['noticia' => 'Restaura esta publicación antes de editarla.']);
        }
        $datos = $this->validar($request);
        $imagenNueva = null;
        $imagenAnterior = $noticia->imagen_url;
        try {
            DB::transaction(function () use ($request, $noticia, $datos, &$imagenNueva) {
                $noticia = Noticia::whereKey($noticia->id)->lockForUpdate()->firstOrFail();
                if ($noticia->estado === 'archivado') {
                    throw ValidationException::withMessages(['noticia' => 'Restaura esta publicación antes de editarla.']);
                }
                if ($imagenNueva = $this->guardarImagen($request)) {
                    $datos['imagen_url'] = $imagenNueva;
                } elseif (empty($datos['imagen_url'])) {
                    if ($request->boolean('quitar_imagen')) {
                        $datos['imagen_url'] = null;
                    } else {
                        unset($datos['imagen_url']);
                    }
                }
                $noticia->update($datos + ['destacada' => $datos['estado'] === 'publicado' && $request->boolean('destacada'),
                    'publicado_en' => $datos['estado'] === 'publicado' ? ($noticia->publicado_en ?? now()) : null]);
                $this->auditoria->registrar($request, 'actualizar', 'noticia', $noticia->id, ['estado' => $noticia->estado]);
            });
        } catch (\Throwable $e) {
            $this->eliminarImagenLocal($imagenNueva);
            throw $e;
        }
        if ($imagenAnterior !== $noticia->fresh()->imagen_url) {
            $this->eliminarImagenLocal($imagenAnterior);
        }

        return back()->with('estado', $datos['estado'] === 'publicado' ? 'Publicación guardada y visible en el sitio.' : 'Borrador guardado.');
    }

    public function destroy(Request $request, Noticia $noticia)
    {
        $this->authorize('delete', $noticia);
        DB::transaction(function () use ($request, $noticia) {
            $noticia = Noticia::whereKey($noticia->id)->lockForUpdate()->firstOrFail();
            if ($noticia->estado === 'archivado') {
                throw ValidationException::withMessages(['noticia' => 'Esta publicación ya está archivada.']);
            }
            $noticia->update(['estado' => 'archivado', 'destacada' => false]);
            $this->auditoria->registrar($request, 'archivar', 'noticia', $noticia->id);
        });

        return back()->with('estado', 'Noticia archivada sin eliminar su historial.');
    }

    public function restaurar(Request $request, Noticia $noticia)
    {
        $this->authorize('update', $noticia);
        DB::transaction(function () use ($request, $noticia) {
            $noticia = Noticia::whereKey($noticia->id)->lockForUpdate()->firstOrFail();
            if ($noticia->estado !== 'archivado') {
                throw ValidationException::withMessages(['noticia' => 'Solo puedes restaurar publicaciones archivadas.']);
            }
            $noticia->update(['estado' => 'borrador', 'destacada' => false, 'publicado_en' => null]);
            $this->auditoria->registrar($request, 'restaurar', 'noticia', $noticia->id);
        });

        return redirect()->route('publicaciones.index', ['estado' => 'borrador'])
            ->with('estado', 'Publicación restaurada como borrador.');
    }

    public function categoria(Request $request)
    {
        $this->authorize('create', Noticia::class);
        $datos = $request->validate(['nombre' => 'required|string|max:80|unique:categorias,nombre']);
        $slug = Str::slug($datos['nombre']);
        if ($slug === '' || Categoria::where('slug', $slug)->exists()) {
            throw ValidationException::withMessages(['nombre' => 'Elige un nombre de categoría diferente.']);
        }
        $categoria = Categoria::create(['nombre' => $datos['nombre'], 'slug' => $slug]);
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
        $datos = $request->validate(['categoria_id' => 'nullable|exists:categorias,id', 'titulo' => 'required|string|max:180',
            'resumen' => 'required|string|max:1000', 'contenido' => 'required|string|max:20000',
            'imagen_url' => 'nullable|url|max:255', 'estado' => 'required|in:borrador,publicado',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'destacada' => 'nullable|boolean', 'quitar_imagen' => 'nullable|boolean']);

        return array_diff_key($datos, array_flip(['imagen', 'destacada', 'quitar_imagen']));
    }

    private function guardarImagen(Request $request): ?string
    {
        return $request->hasFile('imagen') ? 'storage/'.$request->file('imagen')->store('noticias', 'public') : null;
    }

    private function eliminarImagenLocal(?string $ruta): void
    {
        if ($ruta && str_starts_with($ruta, 'storage/noticias/')) {
            Storage::disk('public')->delete(Str::after($ruta, 'storage/'));
        }
    }
}
