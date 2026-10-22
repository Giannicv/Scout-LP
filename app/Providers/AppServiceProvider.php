<?php

namespace App\Providers;

use App\Models\Categoria;
use App\Models\Evento;
use App\Models\Noticia;
use App\Models\Rama;
use App\Models\Recurso;
use App\Models\User;
use App\Policies\EventoPolicy;
use App\Policies\NoticiaPolicy;
use App\Policies\RecursoPolicy;
use App\Policies\UserPolicy;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Carbon::setLocale('es');
        Gate::policy(Evento::class, EventoPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Recurso::class, RecursoPolicy::class);
        Gate::policy(Noticia::class, NoticiaPolicy::class);
        View::composer(['layouts.publico', 'partials.pie', 'partials.navegacion', 'inicio', 'ramas', 'nosotros'], function ($vista) {
            $vista->with('ramasPublicas', Rama::orderBy('orden')->get());
        });
        View::composer('components.campo', function ($vista) {
            $tipo = $vista->getData()['tipo'] ?? 'text';
            $opciones = match ($tipo) {
                'ramas' => Rama::orderBy('orden')->pluck('nombre', 'id'),
                'categorias' => Categoria::pluck('nombre', 'id'),
                'publicacion' => ['borrador' => 'Borrador', 'publicado' => 'Publicada'],
                'mensaje' => ['nuevo' => 'Nuevo', 'leido' => 'Leído', 'respondido' => 'Respondido'],
                default => $vista->getData()['opciones'] ?? [],
            };
            $vista->with('opcionesCampo', $opciones);
        });
    }
}
