<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Representa los registros de insignias y sus relaciones originales.
 */
class Insignia extends Model
{
    protected $table = 'insignias';

    public const CREATED_AT = null;

    public const UPDATED_AT = null;

    public $timestamps = false;

    protected $fillable = [
        'requisito_id',
        'rama_id',
        'nombre',
        'etapa',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'requisito_id' => 'integer',
            'rama_id' => 'integer',
        ];
    }

    public function rama(): BelongsTo
    {
        return $this->belongsTo(Rama::class, 'rama_id');
    }

    public function requisito(): BelongsTo
    {
        return $this->belongsTo(Requisito::class, 'requisito_id');
    }

    public function miembroInsigniasPorInsigniaId(): HasMany
    {
        return $this->hasMany(MiembroInsignia::class, 'insignia_id');
    }
}
