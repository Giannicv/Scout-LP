<?php

namespace Database\Seeders;

use App\Models\GrupoScout;
use App\Models\Miembro;
use App\Models\Rama;
use App\Models\Unidad;
use App\Models\User;

/** Resuelve identificadores de ejemplos sin depender de secuencias numéricas. */
class ContextoDemo
{
    public static function datos(): array
    {
        return json_decode(file_get_contents(__DIR__.'/datos-demo.json'), true, 512, JSON_THROW_ON_ERROR);
    }

    public static function usuario(string $identificador): User
    {
        $datos = collect(self::datos()['sprint']['usuarios'])->firstWhere('id', $identificador);

        return User::where('email', $datos['correo'])->firstOrFail();
    }

    public static function grupo(string $departamento): GrupoScout
    {
        return GrupoScout::whereHas('distrito', fn ($distritos) => $distritos->where('departamento',
            $departamento))->firstOrFail();
    }

    public static function rama(string $slug): Rama
    {
        return Rama::where('slug', $slug)->firstOrFail();
    }

    public static function unidad(string $identificador): Unidad
    {
        $datos = collect(self::datos()['sprint']['unidades'])->firstWhere('id', $identificador);

        return Unidad::where('nombre', $datos['nombre'])
            ->where('grupo_id', self::grupo($datos['departamento'])->id)->firstOrFail();
    }

    public static function miembro(string $identificador): Miembro
    {
        $datos = collect(self::datos()['sprint']['miembros'])->firstWhere('id', $identificador);

        return Miembro::where('codigo', $datos['codigo'])->firstOrFail();
    }
}
