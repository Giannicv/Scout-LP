<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rama extends Model
{
    public $timestamps = false;

    protected $table = 'ramas';

    protected $fillable = ['nombre', 'slug', 'edad_minima', 'edad_maxima', 'descripcion', 'lema', 'color_hex', 'imagen_url', 'orden'];

    public function miembros(): HasMany
    {
        return $this->hasMany(Miembro::class, 'rama_id');
    }

    public function eventos(): BelongsToMany
    {
        return $this->belongsToMany(Evento::class, 'evento_rama');
    }
}
