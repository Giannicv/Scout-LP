<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Representa los registros de usuarios y sus relaciones originales.
 */
class User extends Authenticatable
{
    protected $table = 'usuarios';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = null;

    protected $fillable = [
        'rol_id',
        'nombre',
        'apellido',
        'email',
        'password_hash',
        'activo',
        'ultimo_acceso',
        'creado_en',
        'telefono',
        'avatar_url',
        'departamento',
        'distrito_id',
        'tutor_id',
        'miembro_id',
        'version_acceso',
    ];

    protected $hidden = ['password_hash'];

    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function getRememberTokenName(): string
    {
        return '';
    }

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'rol_id' => 'integer',
            'password_hash' => 'hashed',
            'activo' => 'boolean',
            'ultimo_acceso' => 'datetime',
            'creado_en' => 'datetime',
            'distrito_id' => 'integer',
            'tutor_id' => 'integer',
            'miembro_id' => 'integer',
            'version_acceso' => 'integer',
        ];
    }

    public function distrito(): BelongsTo
    {
        return $this->belongsTo(Distrito::class, 'distrito_id');
    }

    public function miembro(): BelongsTo
    {
        return $this->belongsTo(Miembro::class, 'miembro_id');
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Tutor::class, 'tutor_id');
    }

    public function auditoriaPorActorId(): HasMany
    {
        return $this->hasMany(Auditoria::class, 'actor_id');
    }

    public function comunicadoLecturasPorUsuarioId(): HasMany
    {
        return $this->hasMany(ComunicadoLectura::class, 'usuario_id');
    }

    public function comunicadosPorAutorId(): HasMany
    {
        return $this->hasMany(Comunicado::class, 'autor_id');
    }

    public function contenidoInstitucionalPorActualizadoPor(): HasMany
    {
        return $this->hasMany(ContenidoInstitucional::class, 'actualizado_por');
    }

    public function eventosPorAutorId(): HasMany
    {
        return $this->hasMany(Evento::class, 'autor_id');
    }

    public function inscripcionesPorRevisadoPor(): HasMany
    {
        return $this->hasMany(Inscripcion::class, 'revisado_por');
    }

    public function intentosLoginPorUsuarioId(): HasMany
    {
        return $this->hasMany(IntentoAnterior::class, 'usuario_id');
    }

    public function miembroInsigniasPorRegistradoPor(): HasMany
    {
        return $this->hasMany(MiembroInsignia::class, 'registrado_por');
    }

    public function miembroRequisitosPorRegistradoPor(): HasMany
    {
        return $this->hasMany(MiembroRequisito::class, 'registrado_por');
    }

    public function noticiasPorAutorId(): HasMany
    {
        return $this->hasMany(Noticia::class, 'autor_id');
    }

    public function sesionesLoginPorUsuarioId(): HasMany
    {
        return $this->hasMany(SesionAnterior::class, 'usuario_id');
    }

    public function tokensRecuperacionPorUsuarioId(): HasMany
    {
        return $this->hasMany(TokenRecuperacion::class, 'usuario_id');
    }

    public function unidadesPorDirigenteId(): HasMany
    {
        return $this->hasMany(Unidad::class, 'dirigente_id');
    }
}
