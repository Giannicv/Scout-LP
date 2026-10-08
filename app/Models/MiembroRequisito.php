<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Representa los registros de miembro_requisitos y sus relaciones originales.
 */
class MiembroRequisito extends Model
{
    protected $table = 'miembro_requisitos';

    public const CREATED_AT = null;

    public const UPDATED_AT = null;

    public $timestamps = false;

    protected $fillable = [
        'miembro_id',
        'requisito_id',
        'fecha',
        'observacion',
        'registrado_por',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'miembro_id' => 'integer',
            'requisito_id' => 'integer',
            'fecha' => 'date',
            'registrado_por' => 'integer',
        ];
    }

    public function miembro(): BelongsTo
    {
        return $this->belongsTo(Miembro::class, 'miembro_id');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function requisito(): BelongsTo
    {
        return $this->belongsTo(Requisito::class, 'requisito_id');
    }
}
