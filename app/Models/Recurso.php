<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recurso extends Model
{
    protected $table = 'recursos';

    protected $fillable = ['categoria_recurso_id', 'publicado_por', 'titulo', 'descripcion', 'archivo_path', 'nombre_original', 'mime_type', 'tamano_bytes', 'estado', 'publicado_en'];

    protected function casts(): array
    {
        return ['publicado_en' => 'datetime'];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaRecurso::class, 'categoria_recurso_id');
    }

    public function publicador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'publicado_por');
    }
}
