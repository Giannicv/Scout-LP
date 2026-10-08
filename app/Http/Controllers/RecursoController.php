<?php

namespace App\Http\Controllers;

use App\Http\Requests\RecursoRequest;
use App\Services\AlcanceScout;
use App\Services\GestionRecursos;
use Illuminate\Http\Request;

/** CRUD compartido que solo expone recursos autorizados para el rol actual. */
class RecursoController extends Controller
{
    public function consulta(Request $request, string $recurso)
    {
        $configuracion = config('recursos.'.$recurso);
        abort_unless($configuracion, 404);
        $rol = $request->user()->rol->nombre;
        abort_unless($rol === 'administrador'
            || ($rol === 'dirigente' && in_array($recurso, ['actividades', 'comunicados'])), 403);
        $consulta = $configuracion['modelo']::query();
        if ($rol === 'dirigente') {
            $consulta->whereIn('unidad_id', app(AlcanceScout::class)->unidades($request->user())->select('id'));
        }

        return $consulta;
    }

    public function create(Request $request, string $recurso)
    {
        abort_if(in_array($recurso, ['ramas', 'contenido', 'mensajes']), 405);
        $registro = $this->consulta($request, $recurso)->getModel()->newInstance();

        return $this->formulario($request, $recurso, $registro);
    }

    public function edit(Request $request, string $recurso, int $registro)
    {
        return $this->formulario($request, $recurso, $this->consulta($request, $recurso)->findOrFail($registro));
    }

    private function formulario(Request $request, string $recurso, $registro)
    {
        $configuracion = config('recursos.'.$recurso);
        $rolRuta = $request->user()->rol->nombre === 'administrador' ? 'admin' : 'dirigente';

        return view('panel.formulario', compact('recurso', 'registro', 'configuracion', 'rolRuta'));
    }

    public function store(RecursoRequest $request, string $recurso, GestionRecursos $gestion)
    {
        abort_if(in_array($recurso, ['ramas', 'contenido', 'mensajes']), 405);
        $registro = $this->consulta($request, $recurso)->getModel()->newInstance();
        $gestion->guardar($recurso, $registro, $request->validated(), $request->user());

        return $this->volver($request, $recurso);
    }

    public function update(RecursoRequest $request, string $recurso, int $registro, GestionRecursos $gestion)
    {
        $registro = $this->consulta($request, $recurso)->findOrFail($registro);
        $gestion->guardar($recurso, $registro, $request->validated(), $request->user());

        return $this->volver($request, $recurso);
    }

    public function destroy(Request $request, string $recurso, int $registro, GestionRecursos $gestion)
    {
        abort_if(in_array($recurso, ['ramas', 'contenido']), 405);
        $registro = $this->consulta($request, $recurso)->findOrFail($registro);
        $gestion->eliminar($recurso, $registro, $request->user());

        return $this->volver($request, $recurso);
    }

    private function volver(Request $request, string $recurso)
    {
        $prefijo = $request->user()->rol->nombre === 'administrador' ? 'admin' : 'dirigente';

        return redirect()->route($prefijo.'.'.$recurso)->with('estado', 'Cambios guardados.');
    }
}
