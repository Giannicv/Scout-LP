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

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = null;

    protected $fillable = [
        'nombre',
        'email',
        'telefono',
        'asunto',
        'mensaje',
        'estado',
        'creado_en',
        'rama_id',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'creado_en' => 'datetime',
            'rama_id' => 'integer',
        ];
    }

    public function rama(): BelongsTo
    {
        return $this->belongsTo(Rama::class, 'rama_id');
    }
}
