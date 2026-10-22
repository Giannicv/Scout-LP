<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\SolicitudCuentaResuelta;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SolicitudController extends Controller
{
    public function __construct(private AuditoriaService $auditoria) {}

    public function index(Request $request)
    {
        $usuario = $request->user()->load('rol');
        $this->authorize('viewAny', User::class);
        $consulta = User::with(['rol', 'grupo', 'miembro.rama'])->where('estado', 'pendiente');
        if ($usuario->es('dirigente_distrital')) {
            $consulta->whereHas('rol', fn ($q) => $q->where('nombre', 'dirigente_grupo'));
        } else {
            $consulta->where('grupo_id', $usuario->grupo_id)->whereHas('rol', fn ($q) => $q->where('nombre', 'miembro'));
        }

        return view('panel.solicitudes', ['solicitudes' => $consulta->oldest()->paginate(15)]);
    }

    public function resolver(Request $request, User $solicitud)
    {
        $request->validate(['decision' => 'required|in:aprobar,rechazar',
            'motivo' => 'nullable|required_if:decision,rechazar|string|max:500']);
        $actor = $request->user()->load('rol');
        $this->authorize('resolve', $solicitud);
        $estado = $request->decision === 'aprobar' ? 'activo' : 'rechazado';
        DB::transaction(function () use ($request, $actor, $solicitud, $estado) {
            $bloqueada = User::with('rol')->whereKey($solicitud->id)->lockForUpdate()->firstOrFail();
            abort_unless($bloqueada->estado === 'pendiente', 422);
            $bloqueada->update(['estado' => $estado, 'revisado_por' => $actor->id,
                'revisado_en' => now(), 'motivo_rechazo' => $request->motivo]);
            if ($bloqueada->miembro_id) {
                $bloqueada->miembro()->update(['activo' => $estado === 'activo']);
            }
            $this->auditoria->registrar($request, $request->decision, 'usuario', $bloqueada->id, ['estado' => $estado]);
        });
        $solicitud->notify(new SolicitudCuentaResuelta($estado === 'activo' ? 'aprobada' : 'rechazada'));

        return back()->with('estado', 'Solicitud '.$estado.'.');
    }
}
