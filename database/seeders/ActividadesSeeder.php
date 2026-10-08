<?php

namespace Database\Seeders;

use App\Models\Actividad;
use App\Models\Asistencia;
use App\Models\Comunicado;
use App\Models\Cuota;
use App\Models\Inscripcion;
use App\Models\Insignia;
use App\Models\MiembroRequisito;
use App\Models\Requisito;
use App\Services\GestionRecursos;
use Illuminate\Database\Seeder;

/** Carga actividades, progresiones y solicitudes de ejemplo de forma repetible. */
class ActividadesSeeder extends Seeder
{
    public function run(): void
    {
        $datos = ContextoDemo::datos()['sprint'];
        $actividades = [];
        foreach ($datos['actividades'] as $actividad) {
            $registro = Actividad::firstOrCreate(['titulo' => $actividad['titulo'],
                'unidad_id' => ContextoDemo::unidad($actividad['unidadId'])->id], [
                    'fecha' => $actividad['fecha'], 'hora' => $actividad['hora'], 'lugar' => $actividad['lugar'],
                    'descripcion' => $actividad['descripcion'], 'requiere_autorizacion' => $actividad['requiereAutorizacion'],

                ]);
            $actividades[$actividad['id']] = $registro;
            app(GestionRecursos::class)->generarAutorizaciones($registro);
        }
        foreach ($datos['asistencias'] as $asistencia) {
            Asistencia::firstOrCreate(['actividad_id' => $actividades[$asistencia['actividadId']]->id,
                'miembro_id' => ContextoDemo::miembro($asistencia['miembroId'])->id],
                ['fecha' => $asistencia['fecha'], 'estado' => strtolower($asistencia['estado'])]);
        }
        foreach ($datos['progresiones'] as $progresion) {
            $datosRequisito = collect($datos['requisitos'])->firstWhere('id', $progresion['requisitoId']);
            $requisito = Requisito::where('nombre', $datosRequisito['nombre'])->firstOrFail();
            $miembro = ContextoDemo::miembro($progresion['miembroId']);
            $valores = ['fecha' => $progresion['fecha'], 'observacion' => $progresion['observacion']];
            MiembroRequisito::firstOrCreate(['miembro_id' => $miembro->id,
                'requisito_id' => $requisito->id], $valores);
            if ($insignia = Insignia::where('requisito_id', $requisito->id)->first()) {
                if (! $miembro->insignias()->where('insignias.id', $insignia->id)->exists()) {
                    $miembro->insignias()->attach($insignia->id, $valores);
                }
            }
        }
        foreach ($datos['comunicados'] as $comunicado) {
            Comunicado::firstOrCreate(['titulo' => $comunicado['titulo'],
                'unidad_id' => ContextoDemo::unidad($comunicado['unidadId'])->id], [
                    'autor_id' => ContextoDemo::usuario($comunicado['autorId'])->id,
                    'contenido' => $comunicado['contenido'], 'destino' => $comunicado['destino'],
                ]);
        }
        foreach ($datos['cuotas'] as $cuota) {
            Cuota::firstOrCreate(['miembro_id' => ContextoDemo::miembro($cuota['miembroId'])->id,
                'concepto' => $cuota['concepto']], ['monto' => $cuota['monto'],
                    'vencimiento' => $cuota['vencimiento'], 'estado' => strtolower($cuota['estado'])]);
        }
        foreach ($datos['solicitudes'] as $solicitud) {
            $familia = ContextoDemo::usuario($solicitud['familiaId']);
            $partesNombre = explode(' ', $solicitud['nombre'], 2);
            Inscripcion::firstOrCreate(['nombre_aspirante' => $partesNombre[0],
                'apellido_aspirante' => $partesNombre[1] ?? '', 'tutor_id' => $familia->tutor_id], [
                    'fecha_nacimiento' => today()->subYears($solicitud['edad']),
                    'rama_id' => ContextoDemo::rama($solicitud['rama'])->id,
                    'departamento' => $solicitud['departamento'],
                    'nombre_tutor' => $familia->nombre.' '.$familia->apellido,
                    'telefono_tutor' => $familia->telefono,
                    'email_tutor' => $familia->email, 'ficha_medica' => $solicitud['medica'],
                ]);
        }
    }
}
