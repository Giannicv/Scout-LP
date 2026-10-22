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
        return $user->es(RolNombre::DISTRITAL->value);
    }

    public function download(User $user, Recurso $recurso): bool
    {
        return $user->estaActivo() && $recurso->estado === 'publicado';
    }

    public function delete(User $user, Recurso $recurso): bool
    {
        return $user->es(RolNombre::DISTRITAL->value);
    }
}
