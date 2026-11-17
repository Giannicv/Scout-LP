<?php

namespace App\Http\Controllers;

use App\Models\CategoriaRecurso;
use App\Models\Recurso;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BibliotecaController extends Controller
{
    public function __construct(private AuditoriaService $auditoria) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Recurso::class);
        $puedePublicar = $request->user()->can('create', Recurso::class);
        $distrital = $request->user()->es('dirigente_distrital');
        $buscar = $request->query('buscar', '');
        $buscar = is_string($buscar) ? trim(mb_substr($buscar, 0, 100)) : '';
        $categoria = $request->query('categoria');
        $categoria = is_scalar($categoria) && ctype_digit((string) $categoria) ? (string) $categoria : '';
        $estado = $puedePublicar && $request->query('estado') === 'archivado' ? 'archivado' : 'publicado';

        $consulta = Recurso::with('categoria')->where('estado', $estado);
        if ($estado === 'archivado' && ! $distrital) {
            $consulta->where('publicado_por', $request->user()->id);
        }
        if ($buscar !== '') {
            $termino = '%'.mb_strtolower($buscar).'%';
            $consulta->where(fn ($q) => $q->whereRaw('LOWER(titulo) LIKE ?', [$termino])
                ->orWhereRaw('LOWER(descripcion) LIKE ?', [$termino]));
        }
        if ($categoria !== '') {
            $consulta->where('categoria_recurso_id', $categoria);
        }

        $categorias = CategoriaRecurso::withCount(['recursos as visibles_count' => function ($q) use ($estado, $distrital, $request) {
            $q->where('estado', $estado);
            if ($estado === 'archivado' && ! $distrital) {
                $q->where('publicado_por', $request->user()->id);
            }
        }])->orderBy('orden')->orderBy('nombre')->get();

        return view('panel.biblioteca', [
            'recursos' => $consulta->orderByDesc('publicado_en')->orderByDesc('id')->paginate(12)->withQueryString(),
            'categorias' => $categorias,
            'buscar' => $buscar,
            'categoriaFiltro' => $categoria,
            'estadoFiltro' => $estado,
            'puedePublicar' => $puedePublicar,
        ]);
    }

    public function show(Request $request, Recurso $recurso)
    {
        $this->authorize('view', $recurso);
        $recurso->load(['categoria', 'publicador']);
        $puedeGestionar = $request->user()->can('update', $recurso);

        return view('panel.recurso-detalle', ['recurso' => $recurso,
            'categorias' => $puedeGestionar
                ? CategoriaRecurso::orderBy('orden')->orderBy('nombre')->get() : collect(),
            'puedeGestionar' => $puedeGestionar]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Recurso::class);
        $datos = $request->validate([
            'categoria_recurso_id' => 'required|exists:categorias_recursos,id',
            'titulo' => 'required|string|max:180', 'descripcion' => 'nullable|string|max:2000',
            'archivo' => 'required|file|max:15360|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);
        $archivo = $request->file('archivo');
        $archivoPath = null;
        $imagenPath = null;
        try {
            $recurso = DB::transaction(function () use ($request, $datos, $archivo, &$archivoPath, &$imagenPath) {
                $archivoPath = $archivo->store('biblioteca', 'local');
                $imagenPath = $this->guardarImagen($request);
                $recurso = Recurso::create([
                    'categoria_recurso_id' => $datos['categoria_recurso_id'],
                    'titulo' => $datos['titulo'],
                    'descripcion' => $datos['descripcion'] ?? null,
                    'imagen_path' => $imagenPath,
                    'publicado_por' => $request->user()->id,
                    'archivo_path' => $archivoPath,
                    'nombre_original' => $archivo->getClientOriginalName(),
                    'mime_type' => $archivo->getMimeType(),
                    'tamano_bytes' => $archivo->getSize(),
                    'estado' => 'publicado',
                    'publicado_en' => now(),
                ]);
                $this->auditoria->registrar($request, 'publicar', 'recurso', $recurso->id,
                    ['nombre_original' => $recurso->nombre_original, 'tamano_bytes' => $recurso->tamano_bytes]);

                return $recurso;
            });
        } catch (\Throwable $e) {
            if ($archivoPath) {
                Storage::disk('local')->delete($archivoPath);
            }
            $this->eliminarImagen($imagenPath);
            throw $e;
        }

        return redirect()->route('biblioteca.show', $recurso)->with('estado', 'Recurso publicado en la biblioteca.');
    }

    public function update(Request $request, Recurso $recurso)
    {
        $this->authorize('update', $recurso);
        if ($recurso->estado === 'archivado') {
            throw ValidationException::withMessages(['recurso' => 'Restaura el recurso antes de editarlo.']);
        }
        $datos = $request->validate([
            'categoria_recurso_id' => 'required|exists:categorias_recursos,id',
            'titulo' => 'required|string|max:180', 'descripcion' => 'nullable|string|max:2000',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'quitar_imagen' => 'nullable|boolean',
        ]);
        $imagenAnterior = $recurso->imagen_path;
        $imagenNueva = null;
        try {
            DB::transaction(function () use ($request, $recurso, $datos, &$imagenNueva) {
                $recurso = Recurso::whereKey($recurso->id)->lockForUpdate()->firstOrFail();
                if ($recurso->estado === 'archivado') {
                    throw ValidationException::withMessages(['recurso' => 'Restaura el recurso antes de editarlo.']);
                }
                $cambios = [
                    'categoria_recurso_id' => $datos['categoria_recurso_id'],
                    'titulo' => $datos['titulo'],
                    'descripcion' => $datos['descripcion'] ?? null,
                ];
                if ($imagenNueva = $this->guardarImagen($request)) {
                    $cambios['imagen_path'] = $imagenNueva;
                } elseif ($request->boolean('quitar_imagen')) {
                    $cambios['imagen_path'] = null;
                }
                $recurso->update($cambios);
                $this->auditoria->registrar($request, 'actualizar', 'recurso', $recurso->id);
            });
        } catch (\Throwable $e) {
            $this->eliminarImagen($imagenNueva);
            throw $e;
        }
        if ($imagenAnterior !== $recurso->fresh()->imagen_path) {
            $this->eliminarImagen($imagenAnterior);
        }

        return redirect()->route('biblioteca.show', $recurso)->with('estado', 'Recurso actualizado.');
    }

    public function preview(Recurso $recurso)
    {
        $this->authorize('download', $recurso);
        abort_unless($recurso->sePuedePrevisualizar(), 404);
        abort_unless(Storage::disk('local')->exists($recurso->archivo_path), 404);

        return response()->file(Storage::disk('local')->path($recurso->archivo_path), [
            'Content-Type' => $recurso->mime_type === 'text/plain' ? 'text/plain; charset=UTF-8' : 'application/pdf',
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function download(Request $request, Recurso $recurso)
    {
        $this->authorize('download', $recurso);
        abort_unless(Storage::disk('local')->exists($recurso->archivo_path), 404);
        $this->auditoria->registrar($request, 'descargar', 'recurso', $recurso->id);

        return Storage::disk('local')->download($recurso->archivo_path, $recurso->nombre_original, [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function destroy(Request $request, Recurso $recurso)
    {
        $this->authorize('delete', $recurso);
        DB::transaction(function () use ($request, $recurso) {
            $recurso = Recurso::whereKey($recurso->id)->lockForUpdate()->firstOrFail();
            if ($recurso->estado === 'archivado') {
                throw ValidationException::withMessages(['recurso' => 'Este recurso ya está archivado.']);
            }
            $recurso->update(['estado' => 'archivado']);
            $this->auditoria->registrar($request, 'archivar', 'recurso', $recurso->id);
        });

        return redirect()->route('biblioteca.index', ['estado' => 'archivado'])
            ->with('estado', 'Recurso archivado. Puedes restaurarlo cuando lo necesites.');
    }

    public function restaurar(Request $request, Recurso $recurso)
    {
        $this->authorize('update', $recurso);
        DB::transaction(function () use ($request, $recurso) {
            $recurso = Recurso::whereKey($recurso->id)->lockForUpdate()->firstOrFail();
            if ($recurso->estado !== 'archivado') {
                throw ValidationException::withMessages(['recurso' => 'Solo puedes restaurar recursos archivados.']);
            }
            $recurso->update(['estado' => 'publicado', 'publicado_en' => now()]);
            $this->auditoria->registrar($request, 'restaurar', 'recurso', $recurso->id);
        });

        return redirect()->route('biblioteca.show', $recurso)->with('estado', 'Recurso restaurado en la biblioteca.');
    }

    private function guardarImagen(Request $request): ?string
    {
        return $request->hasFile('imagen')
            ? 'storage/'.$request->file('imagen')->store('biblioteca-portadas', 'public') : null;
    }

    private function eliminarImagen(?string $ruta): void
    {
        if ($ruta && str_starts_with($ruta, 'storage/biblioteca-portadas/')) {
            Storage::disk('public')->delete(Str::after($ruta, 'storage/'));
        }
    }
}
