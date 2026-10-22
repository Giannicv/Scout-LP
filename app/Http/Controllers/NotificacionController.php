<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    public function index(Request $request)
    {
        return view('panel.notificaciones', ['notificaciones' => $request->user()->notifications()->latest()->paginate(20)]);
    }

    public function leer(Request $request, string $notificacion)
    {
        $registro = $request->user()->notifications()->findOrFail($notificacion);
        $registro->markAsRead();

        return back();
    }
}
