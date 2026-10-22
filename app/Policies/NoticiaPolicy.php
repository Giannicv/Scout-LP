<?php

namespace App\Policies;

use App\Enums\RolNombre;
use App\Models\Noticia;
use App\Models\User;

class NoticiaPolicy
{
    public function before(User $user): ?bool
    {
        return $user->es(RolNombre::DISTRITAL->value) ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Noticia $noticia): bool
    {
        return false;
    }

    public function delete(User $user, Noticia $noticia): bool
    {
        return false;
    }
}
