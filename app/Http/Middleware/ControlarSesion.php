<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/** Invalida sesiones desactivadas, antiguas o sin actividad y evita caché privada. */
class ControlarSesion
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($usuario = $request->user()) {
            $vencida = time() - $request->session()->get('ultima_actividad', time()) >= 1800;
            $version = $request->session()->get('version_acceso', $usuario->version_acceso);
            if (! $usuario->estaActivo() || $vencida || $version !== $usuario->version_acceso) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('estado', 'Tu sesión terminó. Vuelve a ingresar.');
            }
            $request->session()->put('ultima_actividad', time());
        }
        $respuesta = $next($request);
        $respuesta->headers->set('Cache-Control', 'no-store, private, max-age=0');

        return $respuesta;
    }
}
