<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Evento extends Model
{
    use HasFactory;

    protected $table = 'eventos';

    protected $fillable = ['creado_por', 'publicado_por', 'propuesto_por_miembro_id', 'titulo',
        'tipo', 'descripcion', 'fecha_inicio', 'fecha_fin', 'lugar', 'mapa_url', 'costo', 'cupo',
        'que_llevar', 'indicaciones', 'imagen_url', 'requiere_autorizacion',
        'instrucciones_autorizacion', 'inscripcion_habilitada', 'inscripcion_desde',
        'inscripcion_hasta', 'estado', 'motivo_rechazo'];

    protected function casts(): array
    {
        return ['fecha_inicio' => 'datetime', 'fecha_fin' => 'datetime',
            'inscripcion_desde' => 'datetime', 'inscripcion_hasta' => 'datetime', 'costo' => 'decimal:2',
            'requiere_autorizacion' => 'boolean', 'inscripcion_habilitada' => 'boolean'];
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function publicador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'publicado_por');
    }

    public function proponente(): BelongsTo
    {
        return $this->belongsTo(Miembro::class, 'propuesto_por_miembro_id');
    }

    public function ramas(): BelongsToMany
    {
        return $this->belongsToMany(Rama::class, 'evento_rama');
    }

    public function inscripciones(): HasMany
    {
        return $this->hasMany(InscripcionEvento::class, 'evento_id');
    }

    public function aceptaInscripcionDe(Miembro $miembro): bool
    {
        $ahora = now();
        $enVentana = (! $this->inscripcion_desde || $this->inscripcion_desde->lte($ahora))
            && (! $this->inscripcion_hasta || $this->inscripcion_hasta->gte($ahora));
        $hayCupo = ! $this->cupo || $this->inscripciones()->where('estado', 'inscrito')->count() < $this->cupo;

        return $this->estado === 'publicado' && $this->inscripcion_habilitada && $enVentana && $hayCupo
            && $this->ramas()->whereKey($miembro->rama_id)->exists();
    }
}
