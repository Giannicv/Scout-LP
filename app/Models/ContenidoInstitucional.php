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

    protected $fillable = [
        'clave',
        'titulo',
        'contenido',
        'actualizado_por',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'actualizado_por' => 'integer',
        ];
    }

    public function actualizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actualizado_por');
    }
}
