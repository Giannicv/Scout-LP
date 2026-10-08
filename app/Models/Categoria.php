<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Representa los registros de categorias y sus relaciones originales.
 */
class Categoria extends Model
{
    protected $table = 'categorias';

    public const CREATED_AT = null;

    public const UPDATED_AT = null;

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'slug',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
        ];
    }

    public function noticiasPorCategoriaId(): HasMany
    {
        return $this->hasMany(Noticia::class, 'categoria_id');
    }
}
