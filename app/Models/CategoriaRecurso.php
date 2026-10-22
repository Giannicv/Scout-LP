<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaRecurso extends Model
{
    public $timestamps = false;

    protected $table = 'categorias_recursos';

    protected $fillable = ['nombre', 'slug', 'descripcion', 'orden'];

    public function recursos(): HasMany
    {
        return $this->hasMany(Recurso::class, 'categoria_recurso_id');
    }
}
