<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Representa los registros de actividades y sus relaciones originales.
 */
class Actividad extends Model
{
    protected $table = 'actividades';

    public const CREATED_AT = null;

    public const UPDATED_AT = null;

    public $timestamps = false;

    protected $fillable = [
        'unidad_id',
        'titulo',
        'fecha',
        'hora',
        'lugar',
        'descripcion',
        'requiere_autorizacion',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'unidad_id' => 'integer',
            'fecha' => 'date',
            'requiere_autorizacion' => 'boolean',
        ];
    }

    public function unidad(): BelongsTo
    {
        return $this->belongsTo(Unidad::class, 'unidad_id');
    }

    public function asistenciasPorActividadId(): HasMany
    {
        return $this->hasMany(Asistencia::class, 'actividad_id');
    }

    public function autorizacionesPorActividadId(): HasMany
    {
        return $this->hasMany(Autorizacion::class, 'actividad_id');
    }
}
