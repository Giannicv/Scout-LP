<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\SolicitudCuentaResuelta;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SolicitudController extends Controller
{
    public function __construct(private AuditoriaService $auditoria) {}

    public function index(Request $request)
    {
        $usuario = $request->user()->load('rol');
        $this->authorize('viewAny', User::class);
        $consulta = User::with(['rol', 'grupo', 'miembro.rama'])->where('estado', 'pendiente');
        if (! $usuario->es('dirigente_distrital')) {
            $consulta->where('grupo_id', $usuario->grupo_id)->whereHas('rol', fn ($q) => $q->where('nombre', 'miembro'));
        }
        $totalPendientes = (clone $consulta)->count();
        $buscar = $request->query('buscar', '');
        $buscar = is_string($buscar) ? trim(mb_substr($buscar, 0, 100)) : '';
        if ($buscar !== '') {
            $termino = '%'.mb_strtolower($buscar).'%';
            $consulta->where(fn ($q) => $q->whereRaw('LOWER(nombre) LIKE ?', [$termino])
                ->orWhereRaw('LOWER(apellido) LIKE ?', [$termino])
                ->orWhereRaw("LOWER(nombre || ' ' || apellido) LIKE ?", [$termino])
                ->orWhereRaw('LOWER(email) LIKE ?', [$termino]));
        }

        return view('panel.solicitudes', [
            'solicitudes' => $consulta->oldest()->paginate(15)->withQueryString(),
            'totalPendientes' => $totalPendientes,
            'buscar' => $buscar,
            'usuario' => $usuario,
        ]);
    }

    public function resolver(Request $request, User $solicitud)
    {
        $datos = $request->validate(['decision' => 'required|in:aprobar,rechazar',
            'motivo' => 'nullable|required_if:decision,rechazar|string|max:500']);
        $actor = $request->user()->load('rol');
        $this->authorize('resolve', $solicitud);
        $estado = $request->decision === 'aprobar' ? 'activo' : 'rechazado';
        DB::transaction(function () use ($request, $actor, $solicitud, $estado, $datos) {
            $bloqueada = User::with('rol')->whereKey($solicitud->id)->lockForUpdate()->firstOrFail();
            if ($bloqueada->estado !== 'pendiente') {
                throw ValidationException::withMessages(['solicitud' => 'Esta solicitud ya fue revisada. Actualiza la lista.']);
            }
            $bloqueada->update(['estado' => $estado, 'revisado_por' => $actor->id,
                'revisado_en' => now(), 'motivo_rechazo' => $estado === 'rechazado' ? trim($datos['motivo']) : null]);
            if ($bloqueada->miembro_id) {
                $bloqueada->miembro()->update(['activo' => $estado === 'activo']);
            }
            $this->auditoria->registrar($request, $request->decision, 'usuario', $bloqueada->id, ['estado' => $estado]);
        });
        $solicitud->notify(new SolicitudCuentaResuelta($estado === 'activo' ? 'aprobada' : 'rechazada'));

        return back()->with('estado', $estado === 'activo' ? 'Solicitud aprobada.' : 'Solicitud rechazada.');
    }
}
