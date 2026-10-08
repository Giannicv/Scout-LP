<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Reconstruye el esquema original o adopta una base existente sin modificar sus datos.
 */
return new class extends Migration
{
    public function up(): void
    {
        $tablasCreadas = [];
        if (! DB::selectOne('SELECT 1 FROM pg_type WHERE typname = ?', ['estado_inscripcion'])) {
            DB::statement('CREATE TYPE "estado_inscripcion" AS ENUM (\'pendiente\', \'aprobada\', \'rechazada\', \'cancelada\')');
        }

        if (! DB::selectOne('SELECT 1 FROM pg_type WHERE typname = ?', ['estado_mensaje'])) {
            DB::statement('CREATE TYPE "estado_mensaje" AS ENUM (\'nuevo\', \'leido\', \'respondido\')');
        }

        if (! DB::selectOne('SELECT 1 FROM pg_type WHERE typname = ?', ['estado_publicacion'])) {
            DB::statement('CREATE TYPE "estado_publicacion" AS ENUM (\'borrador\', \'publicado\', \'archivado\')');
        }

        if (! DB::selectOne('SELECT 1 FROM pg_type WHERE typname = ?', ['genero_tipo'])) {
            DB::statement('CREATE TYPE "genero_tipo" AS ENUM (\'masculino\', \'femenino\', \'otro\')');
        }

        if (! Schema::hasTable('actividades')) {
            $tablasCreadas[] = 'actividades';
            Schema::create('actividades', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('unidad_id');
                $table->string('titulo', 200);
                $table->date('fecha');
                $table->time('hora')->default(DB::raw('\'09:00:00\'::time without time zone'));
                $table->string('lugar', 200);
                $table->text('descripcion');
                $table->boolean('requiere_autorizacion')->default(DB::raw('false'));
            });
        }

        if (! Schema::hasTable('asistencias')) {
            $tablasCreadas[] = 'asistencias';
            Schema::create('asistencias', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('actividad_id');
                $table->integer('miembro_id');
                $table->date('fecha');
                $table->text('estado');
                $table->text('observacion')->nullable()->default(DB::raw('\'\'::text'));
                $table->unique(['actividad_id', 'miembro_id'], 'asistencias_actividad_id_miembro_id_key');
                $table->index(['miembro_id'], 'idx_asistencias_miembro');
            });
        }

        if (! Schema::hasTable('auditoria')) {
            $tablasCreadas[] = 'auditoria';
            Schema::create('auditoria', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('actor_id')->nullable();
                $table->text('accion');
                $table->jsonb('detalle')->default(DB::raw('\'{}\'::jsonb'));
                $table->timestampTz('creado_en')->default(DB::raw('now()'));
            });
        }

        if (! Schema::hasTable('autorizaciones')) {
            $tablasCreadas[] = 'autorizaciones';
            Schema::create('autorizaciones', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('actividad_id');
                $table->integer('miembro_id');
                $table->integer('tutor_id');
                $table->text('estado')->default(DB::raw('\'pendiente\'::text'));
                $table->timestampTz('firmada_en')->nullable();
                $table->unique(['actividad_id', 'miembro_id', 'tutor_id'],
                    'autorizaciones_actividad_id_miembro_id_tutor_id_key');
                $table->index(['tutor_id'], 'idx_autorizaciones_tutor');
            });
        }

        if (! Schema::hasTable('categorias')) {
            $tablasCreadas[] = 'categorias';
            Schema::create('categorias', function (Blueprint $table) {
                $table->smallIncrements('id');
                $table->string('nombre', 60);
                $table->string('slug', 60);
                $table->unique(['nombre'], 'categorias_nombre_key');
                $table->unique(['slug'], 'categorias_slug_key');
            });
        }

        if (! Schema::hasTable('comunicado_lecturas')) {
            $tablasCreadas[] = 'comunicado_lecturas';
            Schema::create('comunicado_lecturas', function (Blueprint $table) {
                $table->integer('comunicado_id');
                $table->integer('usuario_id');
                $table->timestampTz('leido_en')->default(DB::raw('now()'));
                $table->primary(['comunicado_id', 'usuario_id'], 'comunicado_lecturas_pkey');
            });
        }

        if (! Schema::hasTable('comunicados')) {
            $tablasCreadas[] = 'comunicados';
            Schema::create('comunicados', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('autor_id')->nullable();
                $table->integer('unidad_id')->nullable();
                $table->string('departamento', 50)->nullable();
                $table->text('titulo');
                $table->text('contenido');
                $table->text('destino')->default(DB::raw('\'ambos\'::text'));
                $table->timestampTz('creado_en')->default(DB::raw('now()'));
            });
        }

        if (! Schema::hasTable('contenido_institucional')) {
            $tablasCreadas[] = 'contenido_institucional';
            Schema::create('contenido_institucional', function (Blueprint $table) {
                $table->increments('id');
                $table->string('clave', 60);
                $table->string('titulo', 150);
                $table->text('contenido');
                $table->integer('actualizado_por')->nullable();
                $table->timestampTz('actualizado_en')->default(DB::raw('now()'));
                $table->unique(['clave'], 'contenido_institucional_clave_key');
            });
        }

        if (! Schema::hasTable('cuotas')) {
            $tablasCreadas[] = 'cuotas';
            Schema::create('cuotas', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('miembro_id');
                $table->text('concepto');
                $table->decimal('monto', 12, 2);
                $table->text('estado')->default(DB::raw('\'pendiente\'::text'));
                $table->date('vencimiento');
                $table->text('comprobante_nombre')->nullable();
                $table->timestampTz('registrado_en')->nullable();
                $table->index(['miembro_id'], 'idx_cuotas_miembro');
            });
        }

        if (! Schema::hasTable('distritos')) {
            $tablasCreadas[] = 'distritos';
            Schema::create('distritos', function (Blueprint $table) {
                $table->increments('id');
                $table->string('nombre', 100);
                $table->string('departamento', 50);
                $table->unique(['nombre', 'departamento'], 'distritos_nombre_departamento_key');
            });
        }

        if (! Schema::hasTable('eventos')) {
            $tablasCreadas[] = 'eventos';
            Schema::create('eventos', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('autor_id')->nullable();
                $table->smallInteger('rama_id')->nullable();
                $table->string('titulo', 200);
                $table->text('descripcion')->nullable();
                $table->string('lugar', 200)->nullable();
                $table->timestampTz('fecha_inicio');
                $table->timestampTz('fecha_fin')->nullable();
                $table->string('imagen_url', 255)->nullable();
                $table->rawColumn('estado', 'estado_publicacion')->default(DB::raw('\'borrador\'::estado_publicacion'));
                $table->timestampTz('creado_en')->default(DB::raw('now()'));
                $table->index(['fecha_inicio'], 'idx_eventos_fecha');
            });
        }

        if (! Schema::hasTable('fichas_medicas')) {
            $tablasCreadas[] = 'fichas_medicas';
            Schema::create('fichas_medicas', function (Blueprint $table) {
                $table->integer('miembro_id');
                $table->text('alergias')->nullable()->default(DB::raw('\'\'::text'));
                $table->text('contacto_emergencia')->nullable()->default(DB::raw('\'\'::text'));
                $table->string('telefono_emergencia', 30)->nullable()->default(DB::raw('\'\'::character varying'));
                $table->text('observaciones')->nullable()->default(DB::raw('\'\'::text'));
                $table->primary(['miembro_id'], 'fichas_medicas_pkey');
            });
        }

        if (! Schema::hasTable('grupos_scout')) {
            $tablasCreadas[] = 'grupos_scout';
            Schema::create('grupos_scout', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('distrito_id');
                $table->string('nombre', 120);
                $table->string('direccion', 200)->nullable();
                $table->string('telefono', 30)->nullable();
                $table->string('email', 150)->nullable();
                $table->boolean('activo')->default(DB::raw('true'));
                $table->unique(['distrito_id', 'nombre'], 'grupos_scout_distrito_id_nombre_key');
            });
        }

        if (! Schema::hasTable('inscripciones')) {
            $tablasCreadas[] = 'inscripciones';
            Schema::create('inscripciones', function (Blueprint $table) {
                $table->increments('id');
                $table->smallInteger('rama_id');
                $table->integer('grupo_id')->nullable();
                $table->integer('miembro_id')->nullable();
                $table->string('nombre_aspirante', 100);
                $table->string('apellido_aspirante', 100);
                $table->date('fecha_nacimiento');
                $table->string('nombre_tutor', 150);
                $table->string('telefono_tutor', 30);
                $table->string('email_tutor', 150);
                $table->text('observaciones')->nullable();
                $table->rawColumn('estado', 'estado_inscripcion')->default(DB::raw('\'pendiente\'::estado_inscripcion'));
                $table->integer('revisado_por')->nullable();
                $table->timestampTz('creado_en')->default(DB::raw('now()'));
                $table->timestampTz('revisado_en')->nullable();
                $table->integer('tutor_id')->nullable();
                $table->string('departamento', 50)->nullable();
                $table->integer('unidad_id')->nullable();
                $table->text('motivo_rechazo')->nullable();
                $table->jsonb('ficha_medica')->default(DB::raw('\'{}\'::jsonb'));
                $table->index(['estado'], 'idx_inscripciones_estado');
            });
        }

        if (! Schema::hasTable('insignias')) {
            $tablasCreadas[] = 'insignias';
            Schema::create('insignias', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('requisito_id')->nullable();
                $table->smallInteger('rama_id')->nullable();
                $table->text('nombre');
                $table->text('etapa');
                $table->unique(['requisito_id'], 'insignias_requisito_id_key');
            });
        }

        if (! Schema::hasTable('intentos_login')) {
            $tablasCreadas[] = 'intentos_login';
            Schema::create('intentos_login', function (Blueprint $table) {
                $table->text('clave');
                $table->integer('usuario_id')->nullable();
                $table->integer('intentos')->default(DB::raw('0'));
                $table->timestampTz('bloqueado_hasta')->nullable();
                $table->primary(['clave'], 'intentos_login_pkey');
            });
        }

        if (! Schema::hasTable('mensajes_contacto')) {
            $tablasCreadas[] = 'mensajes_contacto';
            Schema::create('mensajes_contacto', function (Blueprint $table) {
                $table->increments('id');
                $table->string('nombre', 120);
                $table->string('email', 150);
                $table->string('telefono', 30)->nullable();
                $table->string('asunto', 150);
                $table->text('mensaje');
                $table->rawColumn('estado', 'estado_mensaje')->default(DB::raw('\'nuevo\'::estado_mensaje'));
                $table->timestampTz('creado_en')->default(DB::raw('now()'));
                $table->smallInteger('rama_id')->nullable();
            });
        }

        if (! Schema::hasTable('miembro_insignias')) {
            $tablasCreadas[] = 'miembro_insignias';
            Schema::create('miembro_insignias', function (Blueprint $table) {
                $table->integer('miembro_id');
                $table->integer('insignia_id');
                $table->date('fecha');
                $table->text('observacion')->nullable()->default(DB::raw('\'\'::text'));
                $table->integer('registrado_por')->nullable();
                $table->primary(['miembro_id', 'insignia_id'], 'miembro_insignias_pkey');
            });
        }

        if (! Schema::hasTable('miembro_requisitos')) {
            $tablasCreadas[] = 'miembro_requisitos';
            Schema::create('miembro_requisitos', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('miembro_id');
                $table->integer('requisito_id');
                $table->date('fecha');
                $table->text('observacion')->nullable()->default(DB::raw('\'\'::text'));
                $table->integer('registrado_por')->nullable();
                $table->unique(['miembro_id', 'requisito_id'], 'miembro_requisitos_miembro_id_requisito_id_key');
            });
        }

        if (! Schema::hasTable('miembros')) {
            $tablasCreadas[] = 'miembros';
            Schema::create('miembros', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('grupo_id');
                $table->smallInteger('rama_id');
                $table->integer('tutor_id')->nullable();
                $table->string('nombre', 100);
                $table->string('apellido', 100);
                $table->string('ci', 20)->nullable();
                $table->date('fecha_nacimiento');
                $table->rawColumn('genero', 'genero_tipo')->nullable();
                $table->string('telefono', 30)->nullable();
                $table->string('email', 150)->nullable();
                $table->string('direccion', 200)->nullable();
                $table->date('fecha_ingreso')->default(DB::raw('CURRENT_DATE'));
                $table->boolean('activo')->default(DB::raw('true'));
                $table->timestampTz('creado_en')->default(DB::raw('now()'));
                $table->integer('unidad_id')->nullable();
                $table->string('codigo', 60)->nullable();
                $table->string('rol_patrulla', 60)->nullable()->default(DB::raw('\'Integrante\'::character varying'));
                $table->text('lema')->nullable()->default(DB::raw('\'Siempre listos\'::text'));
                $table->text('foto')->nullable()->default(DB::raw('\'\'::text'));
                $table->index(['grupo_id'], 'idx_miembros_grupo');
                $table->index(['rama_id'], 'idx_miembros_rama');
                $table->index(['unidad_id'], 'idx_miembros_unidad');
                $table->unique(['ci'], 'miembros_ci_key');
                $table->unique(['codigo'], 'miembros_codigo_key');
            });
        }

        if (! Schema::hasTable('migraciones')) {
            $tablasCreadas[] = 'migraciones';
            Schema::create('migraciones', function (Blueprint $table) {
                $table->text('nombre');
                $table->timestampTz('aplicada_en')->nullable()->default(DB::raw('now()'));
                $table->primary(['nombre'], 'migraciones_pkey');
            });
        }

        if (! Schema::hasTable('noticias')) {
            $tablasCreadas[] = 'noticias';
            Schema::create('noticias', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('autor_id')->nullable();
                $table->smallInteger('categoria_id')->nullable();
                $table->string('titulo', 200);
                $table->string('slug', 220);
                $table->string('resumen', 300)->nullable();
                $table->text('contenido');
                $table->string('imagen_url', 255)->nullable();
                $table->rawColumn('estado', 'estado_publicacion')->default(DB::raw('\'borrador\'::estado_publicacion'));
                $table->timestampTz('publicado_en')->nullable();
                $table->timestampTz('creado_en')->default(DB::raw('now()'));
                $table->timestampTz('actualizado_en')->default(DB::raw('now()'));
                $table->boolean('destacada')->default(DB::raw('false'));
                $table->index(['estado', 'publicado_en'], 'idx_noticias_estado_fecha');
                $table->unique(['slug'], 'noticias_slug_key');
            });
        }

        if (! Schema::hasTable('ramas')) {
            $tablasCreadas[] = 'ramas';
            Schema::create('ramas', function (Blueprint $table) {
                $table->smallIncrements('id');
                $table->string('nombre', 60);
                $table->string('slug', 60);
                $table->smallInteger('edad_minima');
                $table->smallInteger('edad_maxima');
                $table->text('descripcion')->nullable();
                $table->string('lema', 150)->nullable();
                $table->char('color_hex', 7)->nullable();
                $table->string('imagen_url', 255)->nullable();
                $table->smallInteger('orden')->default(DB::raw('0'));
                $table->jsonb('actividades')->default(DB::raw('\'[]\'::jsonb'));
                $table->unique(['nombre'], 'ramas_nombre_key');
                $table->unique(['slug'], 'ramas_slug_key');
            });
        }

        if (! Schema::hasTable('requisitos')) {
            $tablasCreadas[] = 'requisitos';
            Schema::create('requisitos', function (Blueprint $table) {
                $table->increments('id');
                $table->smallInteger('rama_id')->nullable();
                $table->text('nombre');
                $table->text('etapa');
                $table->text('tipo')->default(DB::raw('\'Requisito\'::text'));
            });
        }

        if (! Schema::hasTable('roles')) {
            $tablasCreadas[] = 'roles';
            Schema::create('roles', function (Blueprint $table) {
                $table->smallIncrements('id');
                $table->string('nombre', 40);
                $table->string('descripcion', 200)->nullable();
                $table->unique(['nombre'], 'roles_nombre_key');
            });
        }

        if (! Schema::hasTable('semillas')) {
            $tablasCreadas[] = 'semillas';
            Schema::create('semillas', function (Blueprint $table) {
                $table->text('version');
                $table->timestampTz('creado_en')->default(DB::raw('now()'));
                $table->primary(['version'], 'semillas_pkey');
            });
        }

        if (! Schema::hasTable('sesiones_login')) {
            $tablasCreadas[] = 'sesiones_login';
            Schema::create('sesiones_login', function (Blueprint $table) {
                $table->text('token_hash');
                $table->integer('usuario_id');
                $table->timestampTz('actividad')->default(DB::raw('now()'));
                $table->primary(['token_hash'], 'sesiones_login_pkey');
            });
        }

        if (! Schema::hasTable('tokens_recuperacion')) {
            $tablasCreadas[] = 'tokens_recuperacion';
            Schema::create('tokens_recuperacion', function (Blueprint $table) {
                $table->text('token_hash');
                $table->integer('usuario_id');
                $table->timestampTz('vence_en');
                $table->primary(['token_hash'], 'tokens_recuperacion_pkey');
            });
        }

        if (! Schema::hasTable('tutores')) {
            $tablasCreadas[] = 'tutores';
            Schema::create('tutores', function (Blueprint $table) {
                $table->increments('id');
                $table->string('nombre', 100);
                $table->string('apellido', 100);
                $table->string('ci', 20)->nullable();
                $table->string('telefono', 30);
                $table->string('email', 150)->nullable();
                $table->string('parentesco', 40)->nullable();
                $table->unique(['ci'], 'tutores_ci_key');
            });
        }

        if (! Schema::hasTable('unidades')) {
            $tablasCreadas[] = 'unidades';
            Schema::create('unidades', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('grupo_id');
                $table->smallInteger('rama_id');
                $table->string('nombre', 120);
                $table->integer('dirigente_id')->nullable();
                $table->index(['dirigente_id'], 'idx_unidades_dirigente');
                $table->unique(['grupo_id', 'nombre'], 'unidades_grupo_id_nombre_key');
            });
        }

        if (! Schema::hasTable('usuarios')) {
            $tablasCreadas[] = 'usuarios';
            Schema::create('usuarios', function (Blueprint $table) {
                $table->increments('id');
                $table->smallInteger('rol_id');
                $table->string('nombre', 100);
                $table->string('apellido', 100);
                $table->string('email', 150);
                $table->string('password_hash', 255);
                $table->boolean('activo')->default(DB::raw('true'));
                $table->timestampTz('ultimo_acceso')->nullable();
                $table->timestampTz('creado_en')->default(DB::raw('now()'));
                $table->string('telefono', 30)->nullable()->default(DB::raw('\'\'::character varying'));
                $table->text('avatar_url')->nullable();
                $table->string('departamento', 50)->nullable();
                $table->integer('distrito_id')->nullable();
                $table->integer('tutor_id')->nullable();
                $table->integer('miembro_id')->nullable();
                $table->integer('version_acceso')->default(DB::raw('0'));
                $table->index(['distrito_id'], 'idx_usuarios_distrito');
                $table->unique(['email'], 'usuarios_email_key');
                $table->unique(['miembro_id'], 'usuarios_miembro_id_key');
                $table->unique(['tutor_id'], 'usuarios_tutor_id_key');
            });
        }

        if (! Schema::hasTable('vinculos_familia')) {
            $tablasCreadas[] = 'vinculos_familia';
            Schema::create('vinculos_familia', function (Blueprint $table) {
                $table->integer('tutor_id');
                $table->integer('miembro_id');
                $table->primary(['tutor_id', 'miembro_id'], 'vinculos_familia_pkey');
            });
        }

        if (in_array('actividades', $tablasCreadas, true)) {
            Schema::table('actividades', function (Blueprint $table) {
                $table->foreign(['unidad_id'], 'actividades_unidad_id_fkey')
                    ->references(['id'])
                    ->on('unidades')
                    ->onDelete('no action')
                    ->onUpdate('no action');
            });
        }

        if (in_array('asistencias', $tablasCreadas, true)) {
            Schema::table('asistencias', function (Blueprint $table) {
                $table->foreign(['actividad_id'], 'asistencias_actividad_id_fkey')
                    ->references(['id'])
                    ->on('actividades')
                    ->onDelete('cascade')
                    ->onUpdate('no action');
                $table->foreign(['miembro_id'], 'asistencias_miembro_id_fkey')
                    ->references(['id'])
                    ->on('miembros')
                    ->onDelete('cascade')
                    ->onUpdate('no action');
            });
        }

        if (in_array('auditoria', $tablasCreadas, true)) {
            Schema::table('auditoria', function (Blueprint $table) {
                $table->foreign(['actor_id'], 'auditoria_actor_id_fkey')
                    ->references(['id'])
                    ->on('usuarios')
                    ->onDelete('set null')
                    ->onUpdate('no action');
            });
        }

        if (in_array('autorizaciones', $tablasCreadas, true)) {
            Schema::table('autorizaciones', function (Blueprint $table) {
                $table->foreign(['actividad_id'], 'autorizaciones_actividad_id_fkey')
                    ->references(['id'])
                    ->on('actividades')
                    ->onDelete('cascade')
                    ->onUpdate('no action');
                $table->foreign(['miembro_id'], 'autorizaciones_miembro_id_fkey')
                    ->references(['id'])
                    ->on('miembros')
                    ->onDelete('cascade')
                    ->onUpdate('no action');
                $table->foreign(['tutor_id'], 'autorizaciones_tutor_id_fkey')
                    ->references(['id'])
                    ->on('tutores')
                    ->onDelete('cascade')
                    ->onUpdate('no action');
            });
        }

        if (in_array('comunicado_lecturas', $tablasCreadas, true)) {
            Schema::table('comunicado_lecturas', function (Blueprint $table) {
                $table->foreign(['comunicado_id'], 'comunicado_lecturas_comunicado_id_fkey')
                    ->references(['id'])
                    ->on('comunicados')
                    ->onDelete('cascade')
                    ->onUpdate('no action');
                $table->foreign(['usuario_id'], 'comunicado_lecturas_usuario_id_fkey')
                    ->references(['id'])
                    ->on('usuarios')
                    ->onDelete('cascade')
                    ->onUpdate('no action');
            });
        }

        if (in_array('comunicados', $tablasCreadas, true)) {
            Schema::table('comunicados', function (Blueprint $table) {
                $table->foreign(['autor_id'], 'comunicados_autor_id_fkey')
                    ->references(['id'])
                    ->on('usuarios')
                    ->onDelete('set null')
                    ->onUpdate('no action');
                $table->foreign(['unidad_id'], 'comunicados_unidad_id_fkey')
                    ->references(['id'])
                    ->on('unidades')
                    ->onDelete('set null')
                    ->onUpdate('no action');
            });
        }

        if (in_array('contenido_institucional', $tablasCreadas, true)) {
            Schema::table('contenido_institucional', function (Blueprint $table) {
                $table->foreign(['actualizado_por'], 'contenido_institucional_actualizado_por_fkey')
                    ->references(['id'])
                    ->on('usuarios')
                    ->onDelete('set null')
                    ->onUpdate('no action');
            });
        }

        if (in_array('cuotas', $tablasCreadas, true)) {
            Schema::table('cuotas', function (Blueprint $table) {
                $table->foreign(['miembro_id'], 'cuotas_miembro_id_fkey')
                    ->references(['id'])
                    ->on('miembros')
                    ->onDelete('cascade')
                    ->onUpdate('no action');
            });
        }

        if (in_array('eventos', $tablasCreadas, true)) {
            Schema::table('eventos', function (Blueprint $table) {
                $table->foreign(['autor_id'], 'eventos_autor_id_fkey')
                    ->references(['id'])
                    ->on('usuarios')
                    ->onDelete('set null')
                    ->onUpdate('no action');
                $table->foreign(['rama_id'], 'eventos_rama_id_fkey')
                    ->references(['id'])
                    ->on('ramas')
                    ->onDelete('no action')
                    ->onUpdate('no action');
            });
        }

        if (in_array('fichas_medicas', $tablasCreadas, true)) {
            Schema::table('fichas_medicas', function (Blueprint $table) {
                $table->foreign(['miembro_id'], 'fichas_medicas_miembro_id_fkey')
                    ->references(['id'])
                    ->on('miembros')
                    ->onDelete('cascade')
                    ->onUpdate('no action');
            });
        }

        if (in_array('grupos_scout', $tablasCreadas, true)) {
            Schema::table('grupos_scout', function (Blueprint $table) {
                $table->foreign(['distrito_id'], 'grupos_scout_distrito_id_fkey')
                    ->references(['id'])
                    ->on('distritos')
                    ->onDelete('no action')
                    ->onUpdate('no action');
            });
        }

        if (in_array('inscripciones', $tablasCreadas, true)) {
            Schema::table('inscripciones', function (Blueprint $table) {
                $table->foreign(['grupo_id'], 'inscripciones_grupo_id_fkey')
                    ->references(['id'])
                    ->on('grupos_scout')
                    ->onDelete('no action')
                    ->onUpdate('no action');
                $table->foreign(['miembro_id'], 'inscripciones_miembro_id_fkey')
                    ->references(['id'])
                    ->on('miembros')
                    ->onDelete('cascade')
                    ->onUpdate('no action');
                $table->foreign(['rama_id'], 'inscripciones_rama_id_fkey')
                    ->references(['id'])
                    ->on('ramas')
                    ->onDelete('no action')
                    ->onUpdate('no action');
                $table->foreign(['revisado_por'], 'inscripciones_revisado_por_fkey')
                    ->references(['id'])
                    ->on('usuarios')
                    ->onDelete('set null')
                    ->onUpdate('no action');
                $table->foreign(['tutor_id'], 'inscripciones_tutor_id_fkey')
                    ->references(['id'])
                    ->on('tutores')
                    ->onDelete('cascade')
                    ->onUpdate('no action');
                $table->foreign(['unidad_id'], 'inscripciones_unidad_id_fkey')
                    ->references(['id'])
                    ->on('unidades')
                    ->onDelete('set null')
                    ->onUpdate('no action');
            });
        }

        if (in_array('insignias', $tablasCreadas, true)) {
            Schema::table('insignias', function (Blueprint $table) {
                $table->foreign(['rama_id'], 'insignias_rama_id_fkey')
                    ->references(['id'])
                    ->on('ramas')
                    ->onDelete('no action')
                    ->onUpdate('no action');
                $table->foreign(['requisito_id'], 'insignias_requisito_id_fkey')
                    ->references(['id'])
                    ->on('requisitos')
                    ->onDelete('cascade')
                    ->onUpdate('no action');
            });
        }

        if (in_array('intentos_login', $tablasCreadas, true)) {
            Schema::table('intentos_login', function (Blueprint $table) {
                $table->foreign(['usuario_id'], 'intentos_login_usuario_id_fkey')
                    ->references(['id'])
                    ->on('usuarios')
                    ->onDelete('cascade')
                    ->onUpdate('no action');
            });
        }

        if (in_array('mensajes_contacto', $tablasCreadas, true)) {
            Schema::table('mensajes_contacto', function (Blueprint $table) {
                $table->foreign(['rama_id'], 'mensajes_contacto_rama_id_fkey')
                    ->references(['id'])
                    ->on('ramas')
                    ->onDelete('no action')
                    ->onUpdate('no action');
            });
        }

        if (in_array('miembro_insignias', $tablasCreadas, true)) {
            Schema::table('miembro_insignias', function (Blueprint $table) {
                $table->foreign(['insignia_id'], 'miembro_insignias_insignia_id_fkey')
                    ->references(['id'])
                    ->on('insignias')
                    ->onDelete('cascade')
                    ->onUpdate('no action');
                $table->foreign(['miembro_id'], 'miembro_insignias_miembro_id_fkey')
                    ->references(['id'])
                    ->on('miembros')
                    ->onDelete('cascade')
                    ->onUpdate('no action');
                $table->foreign(['registrado_por'], 'miembro_insignias_registrado_por_fkey')
                    ->references(['id'])
                    ->on('usuarios')
                    ->onDelete('set null')
                    ->onUpdate('no action');
            });
        }

        if (in_array('miembro_requisitos', $tablasCreadas, true)) {
            Schema::table('miembro_requisitos', function (Blueprint $table) {
                $table->foreign(['miembro_id'], 'miembro_requisitos_miembro_id_fkey')
                    ->references(['id'])
                    ->on('miembros')
                    ->onDelete('cascade')
                    ->onUpdate('no action');
                $table->foreign(['registrado_por'], 'miembro_requisitos_registrado_por_fkey')
                    ->references(['id'])
                    ->on('usuarios')
                    ->onDelete('set null')
                    ->onUpdate('no action');
                $table->foreign(['requisito_id'], 'miembro_requisitos_requisito_id_fkey')
                    ->references(['id'])
                    ->on('requisitos')
                    ->onDelete('cascade')
                    ->onUpdate('no action');
            });
        }

        if (in_array('miembros', $tablasCreadas, true)) {
            Schema::table('miembros', function (Blueprint $table) {
                $table->foreign(['grupo_id'], 'miembros_grupo_id_fkey')
                    ->references(['id'])
                    ->on('grupos_scout')
                    ->onDelete('no action')
                    ->onUpdate('no action');
                $table->foreign(['rama_id'], 'miembros_rama_id_fkey')
                    ->references(['id'])
                    ->on('ramas')
                    ->onDelete('no action')
                    ->onUpdate('no action');
                $table->foreign(['tutor_id'], 'miembros_tutor_id_fkey')
                    ->references(['id'])
                    ->on('tutores')
                    ->onDelete('set null')
                    ->onUpdate('no action');
                $table->foreign(['unidad_id'], 'miembros_unidad_id_fkey')
                    ->references(['id'])
                    ->on('unidades')
                    ->onDelete('set null')
                    ->onUpdate('no action');
            });
        }

        if (in_array('noticias', $tablasCreadas, true)) {
            Schema::table('noticias', function (Blueprint $table) {
                $table->foreign(['autor_id'], 'noticias_autor_id_fkey')
                    ->references(['id'])
                    ->on('usuarios')
                    ->onDelete('set null')
                    ->onUpdate('no action');
                $table->foreign(['categoria_id'], 'noticias_categoria_id_fkey')
                    ->references(['id'])
                    ->on('categorias')
                    ->onDelete('no action')
                    ->onUpdate('no action');
            });
        }

        if (in_array('requisitos', $tablasCreadas, true)) {
            Schema::table('requisitos', function (Blueprint $table) {
                $table->foreign(['rama_id'], 'requisitos_rama_id_fkey')
                    ->references(['id'])
                    ->on('ramas')
                    ->onDelete('no action')
                    ->onUpdate('no action');
            });
        }

        if (in_array('sesiones_login', $tablasCreadas, true)) {
            Schema::table('sesiones_login', function (Blueprint $table) {
                $table->foreign(['usuario_id'], 'sesiones_login_usuario_id_fkey')
                    ->references(['id'])
                    ->on('usuarios')
                    ->onDelete('cascade')
                    ->onUpdate('no action');
            });
        }

        if (in_array('tokens_recuperacion', $tablasCreadas, true)) {
            Schema::table('tokens_recuperacion', function (Blueprint $table) {
                $table->foreign(['usuario_id'], 'tokens_recuperacion_usuario_id_fkey')
                    ->references(['id'])
                    ->on('usuarios')
                    ->onDelete('cascade')
                    ->onUpdate('no action');
            });
        }

        if (in_array('unidades', $tablasCreadas, true)) {
            Schema::table('unidades', function (Blueprint $table) {
                $table->foreign(['dirigente_id'], 'unidades_dirigente_id_fkey')
                    ->references(['id'])
                    ->on('usuarios')
                    ->onDelete('set null')
                    ->onUpdate('no action');
                $table->foreign(['grupo_id'], 'unidades_grupo_id_fkey')
                    ->references(['id'])
                    ->on('grupos_scout')
                    ->onDelete('no action')
                    ->onUpdate('no action');
                $table->foreign(['rama_id'], 'unidades_rama_id_fkey')
                    ->references(['id'])
                    ->on('ramas')
                    ->onDelete('no action')
                    ->onUpdate('no action');
            });
        }

        if (in_array('usuarios', $tablasCreadas, true)) {
            Schema::table('usuarios', function (Blueprint $table) {
                $table->foreign(['distrito_id'], 'usuarios_distrito_id_fkey')
                    ->references(['id'])
                    ->on('distritos')
                    ->onDelete('set null')
                    ->onUpdate('no action');
                $table->foreign(['miembro_id'], 'usuarios_miembro_id_fkey')
                    ->references(['id'])
                    ->on('miembros')
                    ->onDelete('cascade')
                    ->onUpdate('no action');
                $table->foreign(['rol_id'], 'usuarios_rol_id_fkey')
                    ->references(['id'])
                    ->on('roles')
                    ->onDelete('no action')
                    ->onUpdate('no action');
                $table->foreign(['tutor_id'], 'usuarios_tutor_id_fkey')
                    ->references(['id'])
                    ->on('tutores')
                    ->onDelete('cascade')
                    ->onUpdate('no action');
            });
        }

        if (in_array('vinculos_familia', $tablasCreadas, true)) {
            Schema::table('vinculos_familia', function (Blueprint $table) {
                $table->foreign(['miembro_id'], 'vinculos_familia_miembro_id_fkey')
                    ->references(['id'])
                    ->on('miembros')
                    ->onDelete('cascade')
                    ->onUpdate('no action');
                $table->foreign(['tutor_id'], 'vinculos_familia_tutor_id_fkey')
                    ->references(['id'])
                    ->on('tutores')
                    ->onDelete('cascade')
                    ->onUpdate('no action');
            });
        }

        if (in_array('ramas', $tablasCreadas, true)) {
            DB::statement('ALTER TABLE "ramas" ADD CONSTRAINT "ramas_check" CHECK ((edad_maxima >= edad_minima))');
        }

        if (in_array('eventos', $tablasCreadas, true)) {
            DB::statement('ALTER TABLE "eventos" ADD CONSTRAINT "eventos_check" CHECK (((fecha_fin IS NULL) OR (fecha_fin >= fecha_inicio)))');
        }

        if (in_array('requisitos', $tablasCreadas, true)) {
            DB::statement('ALTER TABLE "requisitos" ADD CONSTRAINT "requisitos_tipo_check" CHECK ((tipo = ANY (ARRAY[\'Requisito\'::text, \'Insignia\'::text])))');
        }

        if (in_array('autorizaciones', $tablasCreadas, true)) {
            DB::statement('ALTER TABLE "autorizaciones" ADD CONSTRAINT "autorizaciones_estado_check" CHECK ((estado = ANY (ARRAY[\'pendiente\'::text, \'firmada\'::text, \'rechazada\'::text])))');
        }

        if (in_array('asistencias', $tablasCreadas, true)) {
            DB::statement('ALTER TABLE "asistencias" ADD CONSTRAINT "asistencias_estado_check" CHECK ((estado = ANY (ARRAY[\'presente\'::text, \'ausente\'::text, \'justificado\'::text])))');
        }

        if (in_array('comunicados', $tablasCreadas, true)) {
            DB::statement('ALTER TABLE "comunicados" ADD CONSTRAINT "comunicados_destino_check" CHECK ((destino = ANY (ARRAY[\'ambos\'::text, \'familia\'::text, \'miembro\'::text])))');
        }

        if (in_array('cuotas', $tablasCreadas, true)) {
            DB::statement('ALTER TABLE "cuotas" ADD CONSTRAINT "cuotas_estado_check" CHECK ((estado = ANY (ARRAY[\'pendiente\'::text, \'en_revision\'::text, \'pagado\'::text, \'vencido\'::text])))');
        }

        if (in_array('cuotas', $tablasCreadas, true)) {
            DB::statement('ALTER TABLE "cuotas" ADD CONSTRAINT "cuotas_monto_check" CHECK ((monto >= (0)::numeric))');
        }

    }

    public function down(): void
    {
        // La base compartida contiene datos del sitio anterior: no se borran al revertir.
        throw new RuntimeException(
            'Esta migración adopta tablas existentes. Restaura un respaldo para revertirla.'
        );
    }
};
