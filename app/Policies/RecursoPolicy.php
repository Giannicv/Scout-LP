<?php

namespace App\Policies;

use App\Enums\RolNombre;
use App\Models\Recurso;
use App\Models\User;

class RecursoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->estaActivo();
    }

    public function create(User $user): bool
    {
        return $user->estaActivo() && ($user->es(RolNombre::DISTRITAL->value)
            || $user->es(RolNombre::GRUPO->value));
    }

    public function view(User $user, Recurso $recurso): bool
    {
        return $user->estaActivo() && ($recurso->estado === 'publicado'
            || $user->es(RolNombre::DISTRITAL->value)
            || ($user->es(RolNombre::GRUPO->value) && $recurso->publicado_por === $user->id));
    }

    public function update(User $user, Recurso $recurso): bool
    {
        return $user->estaActivo() && ($user->es(RolNombre::DISTRITAL->value)
            || ($user->es(RolNombre::GRUPO->value) && $recurso->publicado_por === $user->id));
    }

    public function download(User $user, Recurso $recurso): bool
    {
        return $user->estaActivo() && $recurso->estado === 'publicado';
    }

    public function delete(User $user, Recurso $recurso): bool
    {
        return $this->update($user, $recurso);
    }
}
