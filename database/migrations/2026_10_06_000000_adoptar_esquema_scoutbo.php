<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distritos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120);
            $table->string('departamento', 80);
            $table->timestamps();
        });
        Schema::create('grupos_scout', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distrito_id')->constrained('distritos')->cascadeOnDelete();
            $table->string('nombre', 120);
            $table->string('direccion')->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->unique(['distrito_id', 'nombre']);
        });
        Schema::create('ramas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique();
            $table->string('slug', 50)->unique();
            $table->unsignedSmallInteger('edad_minima');
            $table->unsignedSmallInteger('edad_maxima');
            $table->text('descripcion');
            $table->string('lema', 160)->nullable();
            $table->string('color_hex', 7);
            $table->string('imagen_url')->nullable();
            $table->unsignedSmallInteger('orden');
        });
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 40)->unique();
            $table->string('descripcion', 180);
        });
        Schema::create('miembros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grupo_id')->constrained('grupos_scout')->restrictOnDelete();
            $table->foreignId('rama_id')->constrained('ramas')->restrictOnDelete();
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->date('fecha_nacimiento');
            $table->string('telefono', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->index(['grupo_id', 'rama_id', 'activo']);
        });
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rol_id')->constrained('roles')->restrictOnDelete();
            $table->foreignId('grupo_id')->nullable()->constrained('grupos_scout')->nullOnDelete();
            $table->foreignId('miembro_id')->nullable()->unique()->constrained('miembros')->nullOnDelete();
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('email', 150)->unique();
            $table->string('password_hash');
            $table->string('estado', 20)->default('pendiente');
            $table->timestamp('ultimo_acceso')->nullable();
            $table->unsignedInteger('version_acceso')->default(1);
            $table->foreignId('revisado_por')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamp('revisado_en')->nullable();
            $table->string('motivo_rechazo', 500)->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->index(['estado', 'grupo_id']);
        });
        Schema::create('contactos_miembro', function (Blueprint $table) {
            $table->id();
            $table->foreignId('miembro_id')->constrained('miembros')->cascadeOnDelete();
            $table->string('nombre', 150);
            $table->string('parentesco', 80)->nullable();
            $table->string('telefono', 30);
            $table->string('tipo', 20)->default('emergencia');
            $table->timestamps();
        });
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 80)->unique();
            $table->string('slug', 90)->unique();
        });
        Schema::create('noticias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('autor_id')->constrained('usuarios')->restrictOnDelete();
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->nullOnDelete();
            $table->string('titulo', 180);
            $table->string('slug', 190)->unique();
            $table->text('resumen');
            $table->text('contenido');
            $table->string('imagen_url')->nullable();
            $table->string('estado', 20)->default('borrador');
            $table->boolean('destacada')->default(false);
            $table->timestamp('publicado_en')->nullable();
            $table->timestamps();
        });
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creado_por')->constrained('usuarios')->restrictOnDelete();
            $table->foreignId('publicado_por')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->foreignId('propuesto_por_miembro_id')->nullable()->constrained('miembros')->nullOnDelete();
            $table->string('titulo', 180);
            $table->string('tipo', 30)->default('evento');
            $table->text('descripcion');
            $table->timestamp('fecha_inicio');
            $table->timestamp('fecha_fin');
            $table->string('lugar', 180);
            $table->string('mapa_url')->nullable();
            $table->decimal('costo', 10, 2)->default(0);
            $table->unsignedInteger('cupo')->nullable();
            $table->text('que_llevar')->nullable();
            $table->text('indicaciones')->nullable();
            $table->string('imagen_url')->nullable();
            $table->boolean('requiere_autorizacion')->default(false);
            $table->text('instrucciones_autorizacion')->nullable();
            $table->boolean('inscripcion_habilitada')->default(true);
            $table->timestamp('inscripcion_desde')->nullable();
            $table->timestamp('inscripcion_hasta')->nullable();
            $table->string('estado', 20)->default('borrador');
            $table->string('motivo_rechazo', 500)->nullable();
            $table->timestamps();
            $table->index(['estado', 'fecha_inicio']);
        });
        Schema::create('evento_rama', function (Blueprint $table) {
            $table->foreignId('evento_id')->constrained('eventos')->cascadeOnDelete();
            $table->foreignId('rama_id')->constrained('ramas')->cascadeOnDelete();
            $table->primary(['evento_id', 'rama_id']);
        });
        Schema::create('inscripciones_evento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos')->cascadeOnDelete();
            $table->foreignId('miembro_id')->constrained('miembros')->cascadeOnDelete();
            $table->string('estado', 20)->default('inscrito');
            $table->timestamp('inscrito_en');
            $table->timestamp('cancelado_en')->nullable();
            $table->timestamps();
            $table->unique(['evento_id', 'miembro_id']);
        });
        Schema::create('categorias_recursos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->string('slug', 110)->unique();
            $table->text('descripcion')->nullable();
            $table->unsignedSmallInteger('orden')->default(0);
        });
        Schema::create('recursos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_recurso_id')->constrained('categorias_recursos')->restrictOnDelete();
            $table->foreignId('publicado_por')->constrained('usuarios')->restrictOnDelete();
            $table->string('titulo', 180);
            $table->text('descripcion')->nullable();
            $table->string('archivo_path');
            $table->string('nombre_original');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('tamano_bytes');
            $table->string('estado', 20)->default('publicado');
            $table->timestamp('publicado_en')->nullable();
            $table->timestamps();
        });
        Schema::create('contenido_institucional', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 80)->unique();
            $table->string('titulo', 180);
            $table->text('contenido');
            $table->foreignId('actualizado_por')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('mensajes_contacto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rama_id')->nullable()->constrained('ramas')->nullOnDelete();
            $table->string('nombre', 150);
            $table->string('email', 150);
            $table->string('telefono', 30)->nullable();
            $table->string('asunto', 180);
            $table->text('mensaje');
            $table->string('estado', 20)->default('nuevo');
            $table->timestamps();
        });
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
        Schema::create('auditoria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->string('accion', 80);
            $table->string('entidad', 100);
            $table->string('entidad_id', 80)->nullable();
            $table->json('datos')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['entidad', 'entidad_id']);
        });
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        foreach (['password_reset_tokens', 'auditoria', 'notifications', 'mensajes_contacto',
            'contenido_institucional', 'recursos', 'categorias_recursos', 'inscripciones_evento',
            'evento_rama', 'eventos', 'noticias', 'categorias', 'contactos_miembro', 'usuarios',
            'miembros', 'roles', 'ramas', 'grupos_scout', 'distritos'] as $tabla) {
            Schema::dropIfExists($tabla);
        }
    }
};
