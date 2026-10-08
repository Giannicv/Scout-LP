<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Representa los registros de fichas_medicas y sus relaciones originales.
 */
class FichaMedica extends Model
{
    protected $table = 'fichas_medicas';

    public const CREATED_AT = null;

    public const UPDATED_AT = null;

    public $timestamps = false;

    protected $primaryKey = 'miembro_id';

    public $incrementing = false;

    protected $fillable = [
        'miembro_id',
        'alergias',
        'contacto_emergencia',
        'telefono_emergencia',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'miembro_id' => 'integer',
        ];
    }

    public function miembro(): BelongsTo
    {
        return $this->belongsTo(Miembro::class, 'miembro_id');
    }
}
