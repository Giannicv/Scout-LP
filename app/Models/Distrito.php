<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Representa los registros de distritos y sus relaciones originales.
 */
class Distrito extends Model
{
    protected $table = 'distritos';

    public const CREATED_AT = null;

    public const UPDATED_AT = null;

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'departamento',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
        ];
    }

    public function gruposScoutPorDistritoId(): HasMany
    {
        return $this->hasMany(GrupoScout::class, 'distrito_id');
    }

    public function usuariosPorDistritoId(): HasMany
    {
        return $this->hasMany(User::class, 'distrito_id');
    }
}
