<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mensajes_contacto', function (Blueprint $table) {
            $table->foreignId('grupo_id')->nullable()->after('rama_id')
                ->constrained('grupos_scout')->nullOnDelete();
            $table->index(['estado', 'created_at']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE mensajes_contacto ADD CONSTRAINT mensajes_contacto_estado_check CHECK (estado IN ('nuevo','en_revision','atendido','archivado'))");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE mensajes_contacto DROP CONSTRAINT IF EXISTS mensajes_contacto_estado_check');
        }

        Schema::table('mensajes_contacto', function (Blueprint $table) {
            $table->dropIndex(['estado', 'created_at']);
            $table->dropConstrainedForeignId('grupo_id');
        });
    }
};
