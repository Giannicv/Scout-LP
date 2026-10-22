<?php

namespace App\Policies;

use App\Enums\RolNombre;
use App\Models\Evento;
use App\Models\User;

class EventoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->estaActivo();
    }

    public function create(User $user): bool
    {
        return $user->estaActivo() && (! $user->es(RolNombre::MIEMBRO->value)
            || $user->miembro?->rama?->slug === 'rovers');
    }

    public function update(User $user, Evento $evento): bool
    {
        return $user->es(RolNombre::DISTRITAL->value)
            || ($user->es(RolNombre::GRUPO->value) && $evento->creado_por === $user->id);
    }

    public function review(User $user, Evento $evento): bool
    {
        return $user->es(RolNombre::DISTRITAL->value)
            || ($user->es(RolNombre::GRUPO->value) && $evento->proponente?->grupo_id === $user->grupo_id);
    }

    public function register(User $user, Evento $evento): bool
    {
        return $user->es(RolNombre::MIEMBRO->value) && (bool) $user->miembro_id;
    }
}
