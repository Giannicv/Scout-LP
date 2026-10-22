<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Representa los registros de roles y sus relaciones originales.
 */
class Rol extends Model
{
    protected $table = 'roles';

    public const CREATED_AT = null;

    public const UPDATED_AT = null;

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
        ];
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'rol_id');
    }
}
