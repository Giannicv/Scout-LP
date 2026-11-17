<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('miembros')->whereIn('id', function ($consulta) {
            $consulta->select('miembro_id')->from('usuarios')->whereNotNull('miembro_id')
                ->whereIn('estado', ['pendiente', 'rechazado', 'suspendido']);
        })->update(['activo' => false]);
    }

    public function down(): void
    {
        // No se puede recuperar con certeza el estado anterior de cada miembro.
    }
};
