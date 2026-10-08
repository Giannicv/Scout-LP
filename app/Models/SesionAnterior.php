<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Representa los registros de sesiones_login y sus relaciones originales.
 */
class SesionAnterior extends Model
{
    protected $table = 'sesiones_login';

    public const CREATED_AT = null;

    public const UPDATED_AT = null;

    public $timestamps = false;

    protected $primaryKey = 'token_hash';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'token_hash',
        'usuario_id',
        'actividad',
    ];

    protected function casts(): array
    {
        return [
            'usuario_id' => 'integer',
            'actividad' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
