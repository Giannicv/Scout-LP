<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Representa los registros de requisitos y sus relaciones originales.
 */
class Requisito extends Model
{
    protected $table = 'requisitos';

    public const CREATED_AT = null;

    public const UPDATED_AT = null;

    public $timestamps = false;

    protected $fillable = [
        'rama_id',
        'nombre',
        'etapa',
        'tipo',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'rama_id' => 'integer',
        ];
    }

    public function rama(): BelongsTo
    {
        return $this->belongsTo(Rama::class, 'rama_id');
    }

    public function insigniasPorRequisitoId(): HasMany
    {
        return $this->hasMany(Insignia::class, 'requisito_id');
    }

    public function miembroRequisitosPorRequisitoId(): HasMany
    {
        return $this->hasMany(MiembroRequisito::class, 'requisito_id');
    }
}
