<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InscripcionEvento extends Model
{
    protected $table = 'inscripciones_evento';

    protected $fillable = ['evento_id', 'miembro_id', 'estado', 'inscrito_en', 'cancelado_en'];

    protected function casts(): array
    {
        return ['inscrito_en' => 'datetime', 'cancelado_en' => 'datetime'];
    }

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class, 'evento_id');
    }

    public function miembro(): BelongsTo
    {
        return $this->belongsTo(Miembro::class, 'miembro_id');
    }
}
