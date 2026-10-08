<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Representa los registros de inscripciones y sus relaciones originales.
 */
class Inscripcion extends Model
{
    protected $table = 'inscripciones';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = null;

    protected $fillable = [
        'rama_id',
        'grupo_id',
        'miembro_id',
        'nombre_aspirante',
        'apellido_aspirante',
        'fecha_nacimiento',
        'nombre_tutor',
        'telefono_tutor',
        'email_tutor',
        'observaciones',
        'estado',
        'revisado_por',
        'creado_en',
        'revisado_en',
        'tutor_id',
        'departamento',
        'unidad_id',
        'motivo_rechazo',
        'ficha_medica',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'rama_id' => 'integer',
            'grupo_id' => 'integer',
            'miembro_id' => 'integer',
            'fecha_nacimiento' => 'date',
            'revisado_por' => 'integer',
            'creado_en' => 'datetime',
            'revisado_en' => 'datetime',
            'tutor_id' => 'integer',
            'unidad_id' => 'integer',
            'ficha_medica' => 'array',
        ];
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(GrupoScout::class, 'grupo_id');
    }

    public function miembro(): BelongsTo
    {
        return $this->belongsTo(Miembro::class, 'miembro_id');
    }

    public function rama(): BelongsTo
    {
        return $this->belongsTo(Rama::class, 'rama_id');
    }

    public function revisadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisado_por');
    }

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Tutor::class, 'tutor_id');
    }

    public function unidad(): BelongsTo
    {
        return $this->belongsTo(Unidad::class, 'unidad_id');
    }
}
