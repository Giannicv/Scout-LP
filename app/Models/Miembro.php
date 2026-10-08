<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Representa los registros de miembros y sus relaciones originales.
 */
class Miembro extends Model
{
    protected $table = 'miembros';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = null;

    protected $fillable = [
        'grupo_id',
        'rama_id',
        'tutor_id',
        'nombre',
        'apellido',
        'ci',
        'fecha_nacimiento',
        'genero',
        'telefono',
        'email',
        'direccion',
        'fecha_ingreso',
        'activo',
        'creado_en',
        'unidad_id',
        'codigo',
        'rol_patrulla',
        'lema',
        'foto',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'grupo_id' => 'integer',
            'rama_id' => 'integer',
            'tutor_id' => 'integer',
            'fecha_nacimiento' => 'date',
            'fecha_ingreso' => 'date',
            'activo' => 'boolean',
            'creado_en' => 'datetime',
            'unidad_id' => 'integer',
        ];
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(GrupoScout::class, 'grupo_id');
    }

    public function rama(): BelongsTo
    {
        return $this->belongsTo(Rama::class, 'rama_id');
    }

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Tutor::class, 'tutor_id');
    }

    public function unidad(): BelongsTo
    {
        return $this->belongsTo(Unidad::class, 'unidad_id');
    }

    public function asistenciasPorMiembroId(): HasMany
    {
        return $this->hasMany(Asistencia::class, 'miembro_id');
    }

    public function autorizacionesPorMiembroId(): HasMany
    {
        return $this->hasMany(Autorizacion::class, 'miembro_id');
    }

    public function cuotasPorMiembroId(): HasMany
    {
        return $this->hasMany(Cuota::class, 'miembro_id');
    }

    public function fichasMedicasPorMiembroId(): HasMany
    {
        return $this->hasMany(FichaMedica::class, 'miembro_id');
    }

    public function inscripcionesPorMiembroId(): HasMany
    {
        return $this->hasMany(Inscripcion::class, 'miembro_id');
    }

    public function miembroInsigniasPorMiembroId(): HasMany
    {
        return $this->hasMany(MiembroInsignia::class, 'miembro_id');
    }

    public function miembroRequisitosPorMiembroId(): HasMany
    {
        return $this->hasMany(MiembroRequisito::class, 'miembro_id');
    }

    public function usuariosPorMiembroId(): HasMany
    {
        return $this->hasMany(User::class, 'miembro_id');
    }

    public function vinculosFamiliaPorMiembroId(): HasMany
    {
        return $this->hasMany(VinculoFamilia::class, 'miembro_id');
    }

    public function familias(): BelongsToMany
    {
        return $this->belongsToMany(Tutor::class, 'vinculos_familia', 'miembro_id', 'tutor_id');
    }

    public function insignias(): BelongsToMany
    {
        return $this->belongsToMany(Insignia::class, 'miembro_insignias', 'miembro_id', 'insignia_id')
            ->withPivot(['fecha', 'observacion', 'registrado_por']);
    }
}
