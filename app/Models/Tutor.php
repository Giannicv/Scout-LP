<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Representa los registros de tutores y sus relaciones originales.
 */
class Tutor extends Model
{
    protected $table = 'tutores';

    public const CREATED_AT = null;

    public const UPDATED_AT = null;

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'apellido',
        'ci',
        'telefono',
        'email',
        'parentesco',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
        ];
    }

    public function autorizacionesPorTutorId(): HasMany
    {
        return $this->hasMany(Autorizacion::class, 'tutor_id');
    }

    public function inscripcionesPorTutorId(): HasMany
    {
        return $this->hasMany(Inscripcion::class, 'tutor_id');
    }

    public function miembrosPorTutorId(): HasMany
    {
        return $this->hasMany(Miembro::class, 'tutor_id');
    }

    public function usuariosPorTutorId(): HasMany
    {
        return $this->hasMany(User::class, 'tutor_id');
    }

    public function vinculosFamiliaPorTutorId(): HasMany
    {
        return $this->hasMany(VinculoFamilia::class, 'tutor_id');
    }
}
