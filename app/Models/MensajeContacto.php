<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Representa los registros de mensajes_contacto y sus relaciones originales.
 */
class MensajeContacto extends Model
{
    protected $table = 'mensajes_contacto';

    protected $fillable = [
        'nombre',
        'email',
        'telefono',
        'asunto',
        'mensaje',
        'estado',
        'rama_id',
        'grupo_id',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'rama_id' => 'integer',
            'grupo_id' => 'integer',
        ];
    }

    public function rama(): BelongsTo
    {
        return $this->belongsTo(Rama::class, 'rama_id');
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(GrupoScout::class, 'grupo_id');
    }
}
