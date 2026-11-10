<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

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
        return $this->motivoNoInscripcion($miembro) === null;
    }

    public function motivoNoInscripcion(Miembro $miembro): ?string
    {
        $ahora = now();
        if ($this->estado !== 'publicado') {
            return 'El evento todavía no está publicado.';
        }
        if (! $this->inscripcion_habilitada) {
            return 'Las inscripciones están deshabilitadas por la organización.';
        }
        if ($this->fecha_inicio->lte($ahora)) {
            return 'El evento ya comenzó o finalizó.';
        }
        if (! $this->incluyeRama($miembro->rama_id)) {
            return 'El evento no está habilitado para tu rama.';
        }
        if ($this->inscripcion_desde?->gt($ahora)) {
            return 'Las inscripciones abren el '.$this->inscripcion_desde->translatedFormat('d \d\e F \d\e Y, H:i').'.';
        }
        if ($this->cierreInscripcion()->lt($ahora)) {
            return 'La ventana de inscripción ya cerró.';
        }
        if ($this->cupo && $this->cantidadInscritos() >= $this->cupo) {
            return 'El evento alcanzó el cupo disponible.';
        }

        return null;
    }

    /** @return array{clave: string, etiqueta: string, mensaje: string} */
    public function estadoOperativo(): array
    {
        $ahora = now();
        if ($this->estado === 'cancelado') {
            return ['clave' => 'cancelado', 'etiqueta' => 'Cancelado', 'mensaje' => 'Este evento fue cancelado.'];
        }
        if (in_array($this->estado, ['borrador', 'propuesto', 'rechazado'], true)) {
            return ['clave' => $this->estado, 'etiqueta' => ucfirst($this->estado),
                'mensaje' => 'El evento aún no está disponible para inscripciones.'];
        }
        if ($this->fecha_fin->lte($ahora) || $this->estado === 'finalizado') {
            return ['clave' => 'finalizado', 'etiqueta' => 'Finalizado', 'mensaje' => 'La actividad ya finalizó.'];
        }
        if ($this->fecha_inicio->lte($ahora)) {
            return ['clave' => 'en_curso', 'etiqueta' => 'En curso', 'mensaje' => 'La actividad se está realizando.'];
        }
        if (! $this->inscripcion_habilitada) {
            return ['clave' => 'cerrado', 'etiqueta' => 'Inscripción cerrada',
                'mensaje' => 'La organización deshabilitó las inscripciones.'];
        }
        if ($this->inscripcion_desde?->gt($ahora)) {
            return ['clave' => 'proximamente', 'etiqueta' => 'Abre pronto',
                'mensaje' => 'Las inscripciones todavía no comenzaron.'];
        }
        if ($this->cierreInscripcion()->lt($ahora)) {
            return ['clave' => 'cerrado', 'etiqueta' => 'Inscripción cerrada',
                'mensaje' => 'La ventana de inscripción finalizó.'];
        }
        if ($this->cupo && $this->cantidadInscritos() >= $this->cupo) {
            return ['clave' => 'completo', 'etiqueta' => 'Cupo completo', 'mensaje' => 'No quedan plazas disponibles.'];
        }

        return ['clave' => 'abierto', 'etiqueta' => 'Inscripción abierta', 'mensaje' => 'El evento acepta inscripciones.'];
    }

    public function cierreInscripcion(): Carbon
    {
        if (! $this->inscripcion_hasta) {
            return $this->fecha_inicio->copy();
        }

        return $this->inscripcion_hasta->lte($this->fecha_inicio)
            ? $this->inscripcion_hasta->copy()
            : $this->fecha_inicio->copy();
    }

    public function cantidadInscritos(): int
    {
        if (array_key_exists('inscritos_count', $this->attributes)) {
            return (int) $this->attributes['inscritos_count'];
        }
        if ($this->relationLoaded('inscripciones')) {
            return $this->inscripciones->where('estado', 'inscrito')->count();
        }

        return $this->inscripciones()->where('estado', 'inscrito')->count();
    }

    private function incluyeRama(int $ramaId): bool
    {
        if ($this->relationLoaded('ramas')) {
            return $this->ramas->contains('id', $ramaId);
        }

        return $this->ramas()->whereKey($ramaId)->exists();
    }
}
