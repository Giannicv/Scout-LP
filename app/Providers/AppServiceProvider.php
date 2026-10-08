<?php

namespace App\Providers;

use App\Models\Categoria;
use App\Models\Distrito;
use App\Models\GrupoScout;
use App\Models\Rama;
use App\Models\Rol;
use App\Services\AlcanceScout;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/** Comparte catálogos de formularios sin exponer registros fuera del rol. */
class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Carbon::setLocale('es');
        View::composer(['layouts.publico', 'partials.pie', 'partials.navegacion', 'inicio', 'ramas', 'nosotros'],
            function ($vista) {
                $vista->with('ramasPublicas', Rama::orderBy('orden')->get());
                $vista->with('departamentos', Distrito::distinct()->pluck('departamento'));
            });
        View::composer('components.campo', function ($vista) {
            $usuario = auth()->user();
            $tipo = $vista->getData()['tipo'] ?? 'text';
            $opciones = match ($tipo) {
                'ramas' => Rama::orderBy('orden')->pluck('nombre', 'id'),
                'categorias' => Categoria::pluck('nombre', 'id'),
                'grupos' => $usuario?->rol?->nombre === 'administrador' ? GrupoScout::pluck('nombre',
                    'id') : collect(),
                'roles' => $usuario?->rol?->nombre === 'administrador' ? Rol::pluck('nombre', 'id') : collect(),
                'miembros' => $usuario ? app(AlcanceScout::class)->miembros($usuario)->get()
                    ->mapWithKeys(fn ($miembro) => [$miembro->id => $miembro->nombre.' '.$miembro->apellido]) : collect(),

                'unidades' => $usuario ? app(AlcanceScout::class)->unidades($usuario)->pluck('nombre',
                    'id') : collect(),
                'departamentos' => Distrito::distinct()->pluck('departamento', 'departamento'),
                'publicacion' => ['borrador' => 'Borrador', 'publicado' => 'Publicada'],
                'mensaje' => ['nuevo' => 'Nuevo', 'leido' => 'Leído', 'respondido' => 'Respondido'],
                'destinos' => ['ambos' => 'Familias y miembros', 'familia' => 'Familias', 'miembro' => 'Miembros'],
                default => $vista->getData()['opciones'] ?? [],
            };
            $vista->with('opcionesCampo', $opciones);
        });
    }
}
