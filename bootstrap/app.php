<?php

use App\Http\Middleware\ControlarSesion;
use App\Http\Middleware\EncabezadosSeguridad;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(web: __DIR__.'/../routes/web.php', commands: __DIR__.'/../routes/console.php', health: '/up')
    ->withMiddleware(function (Middleware $middleware) {
        // cloudflared envía X-Forwarded-*; sin esto Laravel generaría URLs http://127.0.0.1:8000
        // dentro de la página pública del túnel (contenido mixto y redirects al host local).
        // El servidor de desarrollo escucha solo en 127.0.0.1, por lo que no hay exposición directa.
        $middleware->trustProxies(at: '*');
        $middleware->web(append: [ControlarSesion::class, EncabezadosSeguridad::class]);
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('panel'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Laravel conserva los códigos HTTP y oculta las trazas en producción.
    })->create();
