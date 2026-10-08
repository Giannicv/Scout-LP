<?php

namespace App\Services;

use App\Models\Miembro;
use App\Models\Unidad;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/** Construye consultas limitadas a la familia, unidad o departamento del usuario. */
class AlcanceScout
{
    public function miembros(User $usuario): Builder
    {
        $consulta = Miembro::query();

        return match ($usuario->rol->nombre) {
            'administrador' => $consulta,
            'familia' => $consulta->whereHas('familias', fn ($familias) => $familias->where('tutores.id',
                $usuario->tutor_id)),
            'miembro' => $consulta->where('id', $usuario->miembro_id),
            'dirigente' => $consulta->whereIn('unidad_id', $this->unidades($usuario)->select('unidades.id')),
            'coordinador' => $consulta->whereHas('grupo.distrito',
                fn ($distritos) => $distritos->where('departamento', $usuario->departamento)),

            default => $consulta->whereRaw('false'),
        };
    }

    public function unidades(User $usuario): Builder
    {
        $consulta = Unidad::query();

        return match ($usuario->rol->nombre) {
            'administrador' => $consulta,
            'dirigente' => $consulta->where('dirigente_id', $usuario->id),
            'coordinador' => $consulta->whereHas('grupo.distrito',
                fn ($distritos) => $distritos->where('departamento', $usuario->departamento)),

            default => $consulta->whereIn('id', $this->miembros($usuario)->select('unidad_id')),
        };
    }

    public function miembro(User $usuario, int $identificador): Miembro
    {
        return $this->miembros($usuario)->findOrFail($identificador);
    }

    public function unidad(User $usuario, int $identificador): Unidad
    {
        return $this->unidades($usuario)->findOrFail($identificador);
    }
}
