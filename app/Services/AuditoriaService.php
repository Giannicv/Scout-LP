<?php

namespace App\Services;

use App\Models\Auditoria;
use Illuminate\Http\Request;

class AuditoriaService
{
    public function registrar(Request $request, string $accion, string $entidad, int|string|null $id, array $datos = []): void
    {
        Auditoria::create([
            'actor_id' => $request->user()?->id,
            'accion' => $accion,
            'entidad' => $entidad,
            'entidad_id' => $id === null ? null : (string) $id,
            'datos' => $datos,
            'ip' => $request->ip(),
        ]);
    }
}
