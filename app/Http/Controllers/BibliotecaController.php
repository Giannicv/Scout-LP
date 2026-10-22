<?php

namespace App\Http\Controllers;

use App\Models\CategoriaRecurso;
use App\Models\Recurso;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BibliotecaController extends Controller
{
    public function __construct(private AuditoriaService $auditoria) {}

    public function index()
    {
        $this->authorize('viewAny', Recurso::class);

        return view('panel.biblioteca', ['categorias' => CategoriaRecurso::with(['recursos' => fn ($q) => $q->where('estado', 'publicado')->latest()])->orderBy('orden')->get()]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Recurso::class);
        $datos = $request->validate(['categoria_recurso_id' => 'required|exists:categorias_recursos,id',
            'titulo' => 'required|string|max:180', 'descripcion' => 'nullable|string|max:2000',
            'archivo' => 'required|file|max:15360|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt']);
        $archivo = $request->file('archivo');
        $path = $archivo->store('biblioteca', 'local');
        $recurso = Recurso::create($datos + ['publicado_por' => $request->user()->id, 'archivo_path' => $path,
            'nombre_original' => $archivo->getClientOriginalName(), 'mime_type' => $archivo->getMimeType(),
            'tamano_bytes' => $archivo->getSize(), 'estado' => 'publicado', 'publicado_en' => now()]);
        $this->auditoria->registrar($request, 'publicar', 'recurso', $recurso->id,
            ['nombre_original' => $recurso->nombre_original, 'tamano_bytes' => $recurso->tamano_bytes]);

        return back()->with('estado', 'Recurso publicado.');
    }

    public function download(Request $request, Recurso $recurso)
    {
        $this->authorize('download', $recurso);
        abort_unless(Storage::disk('local')->exists($recurso->archivo_path), 404);
        $this->auditoria->registrar($request, 'descargar', 'recurso', $recurso->id);

        return Storage::disk('local')->download($recurso->archivo_path, $recurso->nombre_original);
    }

    public function destroy(Request $request, Recurso $recurso)
    {
        $this->authorize('delete', $recurso);
        $recurso->update(['estado' => 'archivado']);
        $this->auditoria->registrar($request, 'archivar', 'recurso', $recurso->id);

        return back()->with('estado', 'Recurso archivado; el archivo y su historial se conservaron.');
    }
}
