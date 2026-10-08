<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Representa los registros de auditoria y sus relaciones originales.
 */
class Auditoria extends Model
{
    protected $table = 'auditoria';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = null;

    protected $fillable = [
        'actor_id',
        'accion',
        'detalle',
        'creado_en',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'actor_id' => 'integer',
            'detalle' => 'array',
            'creado_en' => 'datetime',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
