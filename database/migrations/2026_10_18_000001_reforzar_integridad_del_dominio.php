<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement("ALTER TABLE usuarios ADD CONSTRAINT usuarios_estado_check CHECK (estado IN ('pendiente','activo','rechazado','suspendido'))");
        DB::statement("ALTER TABLE eventos ADD CONSTRAINT eventos_tipo_check CHECK (tipo IN ('actividad','evento','proyecto_rover'))");
        DB::statement("ALTER TABLE eventos ADD CONSTRAINT eventos_estado_check CHECK (estado IN ('borrador','propuesto','publicado','rechazado','cancelado','finalizado'))");
        DB::statement('ALTER TABLE eventos ADD CONSTRAINT eventos_fechas_check CHECK (fecha_fin > fecha_inicio)');
        DB::statement('ALTER TABLE eventos ADD CONSTRAINT eventos_costo_check CHECK (costo >= 0)');
        DB::statement('ALTER TABLE eventos ADD CONSTRAINT eventos_cupo_check CHECK (cupo IS NULL OR cupo > 0)');
        DB::statement("ALTER TABLE inscripciones_evento ADD CONSTRAINT inscripciones_evento_estado_check CHECK (estado IN ('inscrito','cancelado'))");
        DB::statement("ALTER TABLE contactos_miembro ADD CONSTRAINT contactos_miembro_tipo_check CHECK (tipo IN ('tutor','emergencia','ambos'))");
        DB::statement("ALTER TABLE recursos ADD CONSTRAINT recursos_estado_check CHECK (estado IN ('publicado','archivado'))");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach (['usuarios_estado_check', 'eventos_tipo_check', 'eventos_estado_check',
            'eventos_fechas_check', 'eventos_costo_check', 'eventos_cupo_check',
            'inscripciones_evento_estado_check', 'contactos_miembro_tipo_check',
            'recursos_estado_check'] as $restriccion) {
            $tabla = match (true) {
                str_starts_with($restriccion, 'usuarios_') => 'usuarios',
                str_starts_with($restriccion, 'eventos_') => 'eventos',
                str_starts_with($restriccion, 'inscripciones_') => 'inscripciones_evento',
                str_starts_with($restriccion, 'contactos_') => 'contactos_miembro',
                default => 'recursos',
            };
            DB::statement("ALTER TABLE {$tabla} DROP CONSTRAINT IF EXISTS {$restriccion}");
        }
    }
};
