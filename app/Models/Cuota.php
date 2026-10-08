<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Representa los registros de cuotas y sus relaciones originales.
 */
class Cuota extends Model
{
    protected $table = 'cuotas';

    public const CREATED_AT = null;

    public const UPDATED_AT = null;

    public $timestamps = false;

    protected $fillable = [
        'miembro_id',
        'concepto',
        'monto',
        'estado',
        'vencimiento',
        'comprobante_nombre',
        'registrado_en',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'miembro_id' => 'integer',
            'monto' => 'decimal:2',
            'vencimiento' => 'date',
            'registrado_en' => 'datetime',
        ];
    }

    public function miembro(): BelongsTo
    {
        return $this->belongsTo(Miembro::class, 'miembro_id');
    }
}
