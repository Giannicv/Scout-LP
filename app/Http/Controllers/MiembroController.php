<?php

namespace App\Http\Controllers;

use App\Models\Miembro;
use App\Models\Rama;
use App\Models\User;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MiembroController extends Controller
{
    public function __construct(private AuditoriaService $auditoria) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);
        $usuario = $request->user()->load('rol');
        $consulta = Miembro::with(['grupo', 'rama', 'usuario', 'contactos']);
        if ($usuario->es('dirigente_grupo')) {
            $consulta->where('grupo_id', $usuario->grupo_id);
        }
        if ($request->filled('buscar')) {
            $termino = '%'.mb_strtolower($request->buscar).'%';
            $consulta->where(fn ($q) => $q->whereRaw('LOWER(nombre) LIKE ?', [$termino])
                ->orWhereRaw('LOWER(apellido) LIKE ?', [$termino]));
        }
        if ($request->filled('rama')) {
            $consulta->where('rama_id', $request->rama);
        }

        return view('panel.miembros', ['miembros' => $consulta->orderBy('apellido')->paginate(20)->withQueryString(),
            'ramas' => Rama::orderBy('orden')->get()]);
    }

    public function cambiarEstado(Request $request, User $usuario)
    {
        $this->authorize('updateStatus', $usuario);
        $request->validate(['estado' => ['required', Rule::in(['activo', 'suspendido'])]]);
        $usuario->update(['estado' => $request->estado, 'version_acceso' => $usuario->version_acceso + 1,
            'revisado_por' => $request->user()->id, 'revisado_en' => now()]);
        $this->auditoria->registrar($request, 'cambiar_estado', 'usuario', $usuario->id, ['estado' => $request->estado]);

        return back()->with('estado', 'Estado de cuenta actualizado.');
    }
}
