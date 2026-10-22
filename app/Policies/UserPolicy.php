<?php

namespace App\Policies;

use App\Enums\RolNombre;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return in_array($actor->rol->nombre, [RolNombre::DISTRITAL->value, RolNombre::GRUPO->value], true);
    }

    public function resolve(User $actor, User $solicitud): bool
    {
        return $actor->es(RolNombre::DISTRITAL->value)
            ? $solicitud->es(RolNombre::GRUPO->value)
            : $actor->es(RolNombre::GRUPO->value) && $solicitud->es(RolNombre::MIEMBRO->value)
                && $solicitud->grupo_id === $actor->grupo_id;
    }

    public function updateStatus(User $actor, User $usuario): bool
    {
        return $actor->id !== $usuario->id && ($actor->es(RolNombre::DISTRITAL->value)
            || ($actor->es(RolNombre::GRUPO->value) && $usuario->es(RolNombre::MIEMBRO->value)
                && $usuario->grupo_id === $actor->grupo_id));
    }
}
