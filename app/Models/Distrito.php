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

    public function grupos(): HasMany
    {
        return $this->hasMany(GrupoScout::class, 'distrito_id');
    }
}
