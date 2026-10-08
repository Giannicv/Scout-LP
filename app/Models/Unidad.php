<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Representa los registros de unidades y sus relaciones originales.
 */
class Unidad extends Model
{
    protected $table = 'unidades';

    public const CREATED_AT = null;

    public const UPDATED_AT = null;

    public $timestamps = false;

    protected $fillable = [
        'grupo_id',
        'rama_id',
        'nombre',
        'dirigente_id',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'grupo_id' => 'integer',
            'rama_id' => 'integer',
            'dirigente_id' => 'integer',
        ];
    }

    public function dirigente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dirigente_id');
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(GrupoScout::class, 'grupo_id');
    }

    public function rama(): BelongsTo
    {
        return $this->belongsTo(Rama::class, 'rama_id');
    }

    public function actividadesPorUnidadId(): HasMany
    {
        return $this->hasMany(Actividad::class, 'unidad_id');
    }

    public function comunicadosPorUnidadId(): HasMany
    {
        return $this->hasMany(Comunicado::class, 'unidad_id');
    }

    public function inscripcionesPorUnidadId(): HasMany
    {
        return $this->hasMany(Inscripcion::class, 'unidad_id');
    }

    public function miembrosPorUnidadId(): HasMany
    {
        return $this->hasMany(Miembro::class, 'unidad_id');
    }
}
