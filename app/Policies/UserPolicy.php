<?php

namespace App\Policies;

use App\Enums\RolNombre;
use App\Models\User;

class UserPolicy
{
    public function manageLeaders(User $actor): bool
    {
        return $actor->estaActivo() && $actor->es(RolNombre::DISTRITAL->value);
    }

    public function updateLeader(User $actor, User $dirigente): bool
    {
        return $this->manageLeaders($actor) && $dirigente->es(RolNombre::GRUPO->value)
            && in_array($dirigente->estado, ['activo', 'suspendido'], true);
    }

    public function viewAny(User $actor): bool
    {
        return in_array($actor->rol->nombre, [RolNombre::DISTRITAL->value, RolNombre::GRUPO->value], true);
    }

    public function resolve(User $actor, User $solicitud): bool
    {
        return $actor->es(RolNombre::DISTRITAL->value)
            ? in_array($solicitud->rol?->nombre, [RolNombre::GRUPO->value, RolNombre::MIEMBRO->value], true)
            : $actor->es(RolNombre::GRUPO->value) && $solicitud->es(RolNombre::MIEMBRO->value)
                && $solicitud->grupo_id === $actor->grupo_id;
    }

    public function updateStatus(User $actor, User $usuario): bool
    {
        return $actor->estaActivo() && $actor->id !== $usuario->id
            && $usuario->es(RolNombre::MIEMBRO->value) && (bool) $usuario->miembro_id
            && in_array($usuario->estado, ['activo', 'suspendido'], true)
            && ($actor->es(RolNombre::DISTRITAL->value)
                || ($actor->es(RolNombre::GRUPO->value) && $usuario->grupo_id === $actor->grupo_id));
    }
}
