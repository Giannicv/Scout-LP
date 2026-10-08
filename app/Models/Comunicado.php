<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Representa los registros de comunicados y sus relaciones originales.
 */
class Comunicado extends Model
{
    protected $table = 'comunicados';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = null;

    protected $fillable = [
        'autor_id',
        'unidad_id',
        'departamento',
        'titulo',
        'contenido',
        'destino',
        'creado_en',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'autor_id' => 'integer',
            'unidad_id' => 'integer',
            'creado_en' => 'datetime',
        ];
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }

    public function unidad(): BelongsTo
    {
        return $this->belongsTo(Unidad::class, 'unidad_id');
    }

    public function comunicadoLecturasPorComunicadoId(): HasMany
    {
        return $this->hasMany(ComunicadoLectura::class, 'comunicado_id');
    }
}
