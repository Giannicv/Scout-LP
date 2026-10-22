<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Miembro;
use App\Models\Recurso;
use App\Models\User;
use Illuminate\Http\Request;

class PanelController extends Controller
{
    public function dashboard(Request $request)
    {
        $usuario = $request->user()->load(['rol', 'grupo', 'miembro.rama']);
        $datos = [
            'eventos' => Evento::where('estado', 'publicado')->where('fecha_fin', '>=', now())->count(),
            'recursos' => Recurso::where('estado', 'publicado')->count(),
            'notificaciones' => $usuario->unreadNotifications()->count(),
            'solicitudes' => $usuario->es('dirigente_distrital')
                ? User::where('estado', 'pendiente')->whereHas('rol', fn ($q) => $q->where('nombre', 'dirigente_grupo'))->count()
                : ($usuario->es('dirigente_grupo') ? User::where('estado', 'pendiente')->where('grupo_id', $usuario->grupo_id)->whereHas('rol', fn ($q) => $q->where('nombre', 'miembro'))->count() : 0),
            'miembros' => $usuario->es('dirigente_grupo') ? Miembro::where('grupo_id', $usuario->grupo_id)->where('activo', true)->count() : Miembro::where('activo', true)->count(),
        ];

        return view('panel.dashboard', compact('usuario', 'datos'));
    }
}
