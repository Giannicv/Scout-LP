<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Miembro;
use App\Models\Recurso;
use App\Models\User;
use Illuminate\Http\Request;

class PanelController extends Controller
{
    public function cuenta(Request $request)
    {
        $usuario = $request->user()->load(['rol', 'grupo']);
        abort_if($usuario->es('miembro'), 403);

        return view('panel.cuenta', compact('usuario'));
    }

    public function dashboard(Request $request)
    {
        $usuario = $request->user()->load(['rol', 'grupo', 'miembro.rama']);
        $esMiembro = $usuario->es('miembro');
        $esDistrital = $usuario->es('dirigente_distrital');
        $esGrupo = $usuario->es('dirigente_grupo');
        $consultaEventos = Evento::query()->where('estado', 'publicado')->where('fecha_fin', '>=', now());
        if ($esMiembro) {
            $consultaEventos->where(fn ($q) => $q
                ->whereHas('ramas', fn ($ramas) => $ramas->whereKey($usuario->miembro?->rama_id ?? 0))
                ->orWhereHas('inscripciones', fn ($inscripciones) => $inscripciones
                    ->where('miembro_id', $usuario->miembro_id ?? 0)->where('estado', 'inscrito')));
        }

        $datos = [
            'eventos' => (clone $consultaEventos)->count(),
            'recursos' => Recurso::where('estado', 'publicado')->count(),
            'notificaciones' => $usuario->unreadNotifications()->count(),
            'solicitudes' => $esDistrital
                ? User::where('estado', 'pendiente')->count()
                : ($esGrupo ? User::where('estado', 'pendiente')->where('grupo_id', $usuario->grupo_id)->whereHas('rol', fn ($q) => $q->where('nombre', 'miembro'))->count() : 0),
            'miembros' => $esMiembro ? null : Miembro::where('activo', true)
                ->when($esGrupo, fn ($q) => $q->where('grupo_id', $usuario->grupo_id))->count(),
            'propuestas' => $esMiembro ? 0 : Evento::where('estado', 'propuesto')
                ->when($esGrupo, fn ($q) => $q->whereHas('proponente', fn ($miembros) => $miembros->where('grupo_id', $usuario->grupo_id)))
                ->count(),
        ];

        $eventosProximos = $consultaEventos
            ->withCount(['inscripciones as inscritos_count' => fn ($q) => $q->where('estado', 'inscrito')])
            ->when($esMiembro && $usuario->miembro_id, fn ($q) => $q->with(['inscripciones' => fn ($inscripciones) => $inscripciones
                ->where('miembro_id', $usuario->miembro_id)->where('estado', 'inscrito')]))
            ->orderBy('fecha_inicio')->limit(3)->get();
        $avisosRecientes = $usuario->unreadNotifications()->latest()->limit(3)->get();

        return view('panel.dashboard', compact('usuario', 'datos', 'eventosProximos', 'avisosRecientes'));
    }
}
