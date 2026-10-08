<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Representa los registros de ramas y sus relaciones originales.
 */
class Rama extends Model
{
    protected $table = 'ramas';

    public const CREATED_AT = null;

    public const UPDATED_AT = null;

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'slug',
        'edad_minima',
        'edad_maxima',
        'descripcion',
        'lema',
        'color_hex',
        'imagen_url',
        'orden',
        'actividades',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'edad_minima' => 'integer',
            'edad_maxima' => 'integer',
            'orden' => 'integer',
            'actividades' => 'array',
        ];
    }

    public function eventosPorRamaId(): HasMany
    {
        return $this->hasMany(Evento::class, 'rama_id');
    }

    public function inscripcionesPorRamaId(): HasMany
    {
        return $this->hasMany(Inscripcion::class, 'rama_id');
    }

    public function insigniasPorRamaId(): HasMany
    {
        return $this->hasMany(Insignia::class, 'rama_id');
    }

    public function mensajesContactoPorRamaId(): HasMany
    {
        return $this->hasMany(MensajeContacto::class, 'rama_id');
    }

    public function miembrosPorRamaId(): HasMany
    {
        return $this->hasMany(Miembro::class, 'rama_id');
    }

    public function requisitosPorRamaId(): HasMany
    {
        return $this->hasMany(Requisito::class, 'rama_id');
    }

    public function unidadesPorRamaId(): HasMany
    {
        return $this->hasMany(Unidad::class, 'rama_id');
    }
}
