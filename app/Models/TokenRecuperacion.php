<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Representa los registros de tokens_recuperacion y sus relaciones originales.
 */
class TokenRecuperacion extends Model
{
    protected $table = 'tokens_recuperacion';

    public const CREATED_AT = null;

    public const UPDATED_AT = null;

    public $timestamps = false;

    protected $primaryKey = 'token_hash';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'token_hash',
        'usuario_id',
        'vence_en',
    ];

    protected function casts(): array
    {
        return [
            'usuario_id' => 'integer',
            'vence_en' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
