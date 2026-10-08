<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Representa los registros de grupos_scout y sus relaciones originales.
 */
class GrupoScout extends Model
{
    protected $table = 'grupos_scout';

    public const CREATED_AT = null;

    public const UPDATED_AT = null;

    public $timestamps = false;

    protected $fillable = [
        'distrito_id',
        'nombre',
        'direccion',
        'telefono',
        'email',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'distrito_id' => 'integer',
            'activo' => 'boolean',
        ];
    }

    public function distrito(): BelongsTo
    {
        return $this->belongsTo(Distrito::class, 'distrito_id');
    }

    public function inscripcionesPorGrupoId(): HasMany
    {
        return $this->hasMany(Inscripcion::class, 'grupo_id');
    }

    public function miembrosPorGrupoId(): HasMany
    {
        return $this->hasMany(Miembro::class, 'grupo_id');
    }

    public function unidadesPorGrupoId(): HasMany
    {
        return $this->hasMany(Unidad::class, 'grupo_id');
    }
}
