<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Comprueba el rol en el servidor antes de mostrar o modificar un panel. */
class VerificarRol
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        abort_unless(in_array($request->user()?->rol?->nombre, $roles, true), 403);

        return $next($request);
    }
}
