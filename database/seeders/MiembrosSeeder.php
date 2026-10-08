<?php

namespace Database\Seeders;

use App\Models\FichaMedica;
use App\Models\Miembro;
use App\Models\Unidad;
use Illuminate\Database\Seeder;

/** Vincula unidades, familias y fichas de miembros con los identificadores reales. */
class MiembrosSeeder extends Seeder
{
    public function run(): void
    {
        $datos = ContextoDemo::datos()['sprint'];
        foreach ($datos['unidades'] as $unidad) {
            Unidad::firstOrCreate(['nombre' => $unidad['nombre'],
                'grupo_id' => ContextoDemo::grupo($unidad['departamento'])->id], [
                    'rama_id' => ContextoDemo::rama($unidad['rama'])->id,
                    'dirigente_id' => ContextoDemo::usuario($unidad['dirigenteId'])->id,
                ]);
        }
        foreach ($datos['miembros'] as $miembro) {
            $vinculo = collect($datos['vinculos'])->firstWhere('miembroId', $miembro['id']);
            $tutorId = ContextoDemo::usuario($vinculo['familiaId'])->tutor_id;
            $partesNombre = explode(' ', $miembro['nombre'], 2);
            $registro = Miembro::firstOrCreate(['codigo' => $miembro['codigo']], [
                'nombre' => $partesNombre[0], 'apellido' => $partesNombre[1] ?? '',
                'grupo_id' => ContextoDemo::grupo($miembro['departamento'])->id,
                'rama_id' => ContextoDemo::rama($miembro['rama'])->id,
                'unidad_id' => ContextoDemo::unidad($miembro['unidadId'])->id, 'tutor_id' => $tutorId,
                'fecha_nacimiento' => today()->subYears($miembro['edad']),
                'email' => $miembro['correo'], 'lema' => $miembro['lema'],
                'rol_patrulla' => $miembro['rolPatrulla'],
            ]);
            $registro->familias()->syncWithoutDetaching([$tutorId]);
            FichaMedica::firstOrCreate(['miembro_id' => $registro->id], [
                'alergias' => $miembro['medica']['alergias'], 'contacto_emergencia' => $miembro['medica']['emergencia'],

            ]);
        }
        foreach ($datos['usuarios'] as $usuario) {
            if (isset($usuario['miembroId'])) {
                $registro = ContextoDemo::usuario($usuario['id']);
                if (! $registro->miembro_id) {
                    $registro->update(['miembro_id' => ContextoDemo::miembro($usuario['miembroId'])->id]);
                }
            }
        }
    }
}
