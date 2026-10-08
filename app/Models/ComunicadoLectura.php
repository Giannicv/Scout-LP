<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Representa los registros de comunicado_lecturas y sus relaciones originales.
 */
class ComunicadoLectura extends Pivot
{
    protected $table = 'comunicado_lecturas';

    public const CREATED_AT = null;

    public const UPDATED_AT = null;

    public $timestamps = false;

    public $incrementing = false;

    protected $fillable = [
        'comunicado_id',
        'usuario_id',
        'leido_en',
    ];

    protected function casts(): array
    {
        return [
            'comunicado_id' => 'integer',
            'usuario_id' => 'integer',
            'leido_en' => 'datetime',
        ];
    }

    public function comunicado(): BelongsTo
    {
        return $this->belongsTo(Comunicado::class, 'comunicado_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
