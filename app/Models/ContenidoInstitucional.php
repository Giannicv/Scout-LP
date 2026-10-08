<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Representa los registros de contenido_institucional y sus relaciones originales.
 */
class ContenidoInstitucional extends Model
{
    protected $table = 'contenido_institucional';

    public const CREATED_AT = null;

    public const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'clave',
        'titulo',
        'contenido',
        'actualizado_por',
        'actualizado_en',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'actualizado_por' => 'integer',
            'actualizado_en' => 'datetime',
        ];
    }

    public function actualizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actualizado_por');
    }
}
