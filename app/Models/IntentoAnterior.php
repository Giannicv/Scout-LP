<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Representa los registros de intentos_login y sus relaciones originales.
 */
class IntentoAnterior extends Model
{
    protected $table = 'intentos_login';

    public const CREATED_AT = null;

    public const UPDATED_AT = null;

    public $timestamps = false;

    protected $primaryKey = 'clave';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'clave',
        'usuario_id',
        'intentos',
        'bloqueado_hasta',
    ];

    protected function casts(): array
    {
        return [
            'usuario_id' => 'integer',
            'intentos' => 'integer',
            'bloqueado_hasta' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
