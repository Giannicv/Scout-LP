<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Representa los registros de vinculos_familia y sus relaciones originales.
 */
class VinculoFamilia extends Pivot
{
    protected $table = 'vinculos_familia';

    public const CREATED_AT = null;

    public const UPDATED_AT = null;

    public $timestamps = false;

    public $incrementing = false;

    protected $fillable = [
        'tutor_id',
        'miembro_id',
    ];

    protected function casts(): array
    {
        return [
            'tutor_id' => 'integer',
            'miembro_id' => 'integer',
        ];
    }

    public function miembro(): BelongsTo
    {
        return $this->belongsTo(Miembro::class, 'miembro_id');
    }

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Tutor::class, 'tutor_id');
    }
}
