<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Representa los registros de eventos y sus relaciones originales.
 */
class Evento extends Model
{
    protected $table = 'eventos';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = null;

    protected $fillable = [
        'autor_id',
        'rama_id',
        'titulo',
        'descripcion',
        'lugar',
        'fecha_inicio',
        'fecha_fin',
        'imagen_url',
        'estado',
        'creado_en',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'autor_id' => 'integer',
            'rama_id' => 'integer',
            'fecha_inicio' => 'datetime',
            'fecha_fin' => 'datetime',
            'creado_en' => 'datetime',
        ];
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }

    public function rama(): BelongsTo
    {
        return $this->belongsTo(Rama::class, 'rama_id');
    }
}
