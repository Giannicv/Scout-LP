<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Representa los registros de asistencias y sus relaciones originales.
 */
class Asistencia extends Model
{
    protected $table = 'asistencias';

    public const CREATED_AT = null;

    public const UPDATED_AT = null;

    public $timestamps = false;

    protected $fillable = [
        'actividad_id',
        'miembro_id',
        'fecha',
        'estado',
        'observacion',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'actividad_id' => 'integer',
            'miembro_id' => 'integer',
            'fecha' => 'date',
        ];
    }

    public function actividad(): BelongsTo
    {
        return $this->belongsTo(Actividad::class, 'actividad_id');
    }

    public function miembro(): BelongsTo
    {
        return $this->belongsTo(Miembro::class, 'miembro_id');
    }
}
