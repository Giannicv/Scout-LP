<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Representa los registros de autorizaciones y sus relaciones originales.
 */
class Autorizacion extends Model
{
    protected $table = 'autorizaciones';

    public const CREATED_AT = null;

    public const UPDATED_AT = null;

    public $timestamps = false;

    protected $fillable = [
        'actividad_id',
        'miembro_id',
        'tutor_id',
        'estado',
        'firmada_en',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'actividad_id' => 'integer',
            'miembro_id' => 'integer',
            'tutor_id' => 'integer',
            'firmada_en' => 'datetime',
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

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Tutor::class, 'tutor_id');
    }
}
