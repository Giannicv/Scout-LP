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

    public function view(User $user, Evento $evento): bool
    {
        if (! $user->estaActivo()) {
            return false;
        }
        if ($user->es(RolNombre::DISTRITAL->value)) {
            return true;
        }
        if ($user->es(RolNombre::GRUPO->value)) {
            return $evento->creado_por === $user->id || $evento->estado === 'publicado'
                || $evento->proponente?->grupo_id === $user->grupo_id;
        }

        return $user->es(RolNombre::MIEMBRO->value) && ($evento->propuesto_por_miembro_id === $user->miembro_id
            || ($evento->estado === 'publicado' && $user->miembro_id
                && $evento->ramas()->whereKey($user->miembro->rama_id)->exists()));
    }

    public function update(User $user, Evento $evento): bool
    {
        return ! in_array($evento->estado, ['cancelado', 'finalizado'], true)
            && ($evento->estado !== 'publicado' || $evento->fecha_inicio->isFuture())
            && ($user->es(RolNombre::DISTRITAL->value)
                || ($user->es(RolNombre::GRUPO->value) && $evento->creado_por === $user->id));
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
