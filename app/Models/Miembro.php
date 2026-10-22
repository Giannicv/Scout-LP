<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Miembro extends Model
{
    use HasFactory;

    protected $table = 'miembros';

    protected $fillable = ['grupo_id', 'rama_id', 'nombre', 'apellido', 'fecha_nacimiento', 'telefono', 'email', 'activo'];

    protected function casts(): array
    {
        return ['fecha_nacimiento' => 'date', 'activo' => 'boolean'];
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(GrupoScout::class, 'grupo_id');
    }

    public function rama(): BelongsTo
    {
        return $this->belongsTo(Rama::class, 'rama_id');
    }

    public function usuario(): HasOne
    {
        return $this->hasOne(User::class, 'miembro_id');
    }

    public function contactos(): HasMany
    {
        return $this->hasMany(ContactoMiembro::class, 'miembro_id');
    }

    public function inscripciones(): HasMany
    {
        return $this->hasMany(InscripcionEvento::class, 'miembro_id');
    }
}
