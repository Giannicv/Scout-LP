<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Representa los registros de noticias y sus relaciones originales.
 */
class Noticia extends Model
{
    protected $table = 'noticias';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'autor_id',
        'categoria_id',
        'titulo',
        'slug',
        'resumen',
        'contenido',
        'imagen_url',
        'estado',
        'publicado_en',
        'creado_en',
        'actualizado_en',
        'destacada',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'autor_id' => 'integer',
            'categoria_id' => 'integer',
            'publicado_en' => 'datetime',
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
            'destacada' => 'boolean',
        ];
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }
}
