<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Representa los registros de miembro_insignias y sus relaciones originales.
 */
class MiembroInsignia extends Pivot
{
    protected $table = 'miembro_insignias';

    public const CREATED_AT = null;

    public const UPDATED_AT = null;

    public $timestamps = false;

    public $incrementing = false;

    protected $fillable = [
        'miembro_id',
        'insignia_id',
        'fecha',
        'observacion',
        'registrado_por',
    ];

    protected function casts(): array
    {
        return [
            'miembro_id' => 'integer',
            'insignia_id' => 'integer',
            'fecha' => 'date',
            'registrado_por' => 'integer',
        ];
    }

    public function insignia(): BelongsTo
    {
        return $this->belongsTo(Insignia::class, 'insignia_id');
    }

    public function miembro(): BelongsTo
    {
        return $this->belongsTo(Miembro::class, 'miembro_id');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}
