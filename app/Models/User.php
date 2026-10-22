<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = ['rol_id', 'grupo_id', 'miembro_id', 'nombre', 'apellido', 'email',
        'password_hash', 'estado', 'ultimo_acceso', 'version_acceso', 'revisado_por',
        'revisado_en', 'motivo_rechazo'];

    protected $hidden = ['password_hash', 'remember_token'];

    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    protected function casts(): array
    {
        return ['password_hash' => 'hashed', 'ultimo_acceso' => 'datetime', 'revisado_en' => 'datetime'];
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(GrupoScout::class, 'grupo_id');
    }

    public function miembro(): BelongsTo
    {
        return $this->belongsTo(Miembro::class, 'miembro_id');
    }

    public function revisor(): BelongsTo
    {
        return $this->belongsTo(self::class, 'revisado_por');
    }

    public function eventos(): HasMany
    {
        return $this->hasMany(Evento::class, 'creado_por');
    }

    public function es(string $rol): bool
    {
        return $this->rol?->nombre === $rol;
    }

    public function estaActivo(): bool
    {
        return $this->estado === 'activo';
    }
}
