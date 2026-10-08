<?php

namespace App\Http\Controllers;

use App\Http\Requests\OperacionRequest;
use App\Models\Actividad;
use App\Models\Asistencia;
use App\Models\Auditoria;
use App\Models\Autorizacion;
use App\Models\Comunicado;
use App\Models\Cuota;
use App\Models\FichaMedica;
use App\Models\Inscripcion;
use App\Models\Insignia;
use App\Models\Miembro;
use App\Models\MiembroRequisito;
use App\Models\Rama;
use App\Models\Requisito;
use App\Models\User;
use App\Services\AlcanceScout;
use App\Services\GestionRecursos;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** Ejecuta operaciones atómicas con autorización del alcance de cada registro. */
class OperacionController extends Controller
{
    public function ejecutar(OperacionRequest $request, string $accion, AlcanceScout $alcance)
    {
        $usuario = $request->user();
        $datos = $request->validated();
        DB::transaction(function () use ($accion, $datos, $usuario, $alcance) {
            switch ($accion) {
                case 'inscribir':
                    $rama = Rama::where('edad_minima', '<=', $datos['edad'])
                        ->where('edad_maxima', '>=', $datos['edad'])->firstOrFail();
                    $duplicada = Inscripcion::where('tutor_id', $usuario->tutor_id)
                        ->where('nombre_aspirante', $datos['nombre'])->where('apellido_aspirante',
                            $datos['apellido'])
                        ->where('estado', 'pendiente')->exists();
                    if ($duplicada) {
                        throw ValidationException::withMessages(['nombre' => 'Ya existe una solicitud pendiente.']);
                    }
                    Inscripcion::create([
                        'nombre_aspirante' => $datos['nombre'], 'apellido_aspirante' => $datos['apellido'],
                        'fecha_nacimiento' => now()->subYears($datos['edad'])->toDateString(),
                        'rama_id' => $rama->id, 'tutor_id' => $usuario->tutor_id,
                        'departamento' => $datos['departamento'], 'estado' => 'pendiente',
                        'nombre_tutor' => $usuario->nombre.' '.$usuario->apellido,
                        'telefono_tutor' => $usuario->telefono, 'email_tutor' => $usuario->email,
                        'ficha_medica' => ['alergias' => $datos['alergias'] ?? '',
                            'emergencia' => $datos['emergencia']],
                    ]);
                    break;
                case 'vincular':
                    $miembro = Miembro::whereRaw("lower(nombre || ' ' || apellido) = lower(?)", [$datos['nombre']])
                        ->whereRaw('upper(codigo) = upper(?)', [$datos['codigo']])->lockForUpdate()->first();
                    if (! $miembro) {
                        throw ValidationException::withMessages(['codigo' => 'Nombre y código no coinciden.']);
                    }
                    $miembro->familias()->syncWithoutDetaching([$usuario->tutor_id]);
                    if (! $miembro->tutor_id) {
                        $miembro->update(['tutor_id' => $usuario->tutor_id]);
                    }
                    $this->actualizarAutorizaciones($miembro->unidad_id);
                    break;
                case 'medica':
                    $miembro = $alcance->miembro($usuario, $datos['miembro_id']);
                    FichaMedica::updateOrCreate(['miembro_id' => $miembro->id], [
                        'alergias' => $datos['alergias'] ?? '', 'contacto_emergencia' => $datos['contacto_emergencia'],

                    ]);
                    break;
                case 'autorizar':
                    $autorizacion = Autorizacion::where('tutor_id', $usuario->tutor_id)
                        ->lockForUpdate()->findOrFail($datos['autorizacion_id']);
                    abort_unless($autorizacion->estado === 'pendiente', 409, 'La autorización ya fue resuelta.');
                    $autorizacion->update(['estado' => $datos['estado'], 'firmada_en' => now()]);
                    break;
                case 'pagar':
                    $cuota = Cuota::lockForUpdate()->findOrFail($datos['cuota_id']);
                    $alcance->miembro($usuario, $cuota->miembro_id);
                    abort_unless(in_array($cuota->estado, ['pendiente', 'vencido']), 409);
                    $cuota->update(['estado' => 'en_revision', 'registrado_en' => now(),
                        'comprobante_nombre' => basename(str_replace('\\', '/', $datos['comprobante_nombre']))]);
                    break;
                case 'resolver':
                    $inscripcion = Inscripcion::where('departamento', $usuario->departamento)
                        ->lockForUpdate()->findOrFail($datos['inscripcion_id']);
                    abort_unless($inscripcion->estado === 'pendiente', 409, 'La solicitud ya fue resuelta.');
                    if ($datos['estado'] === 'rechazada') {
                        $inscripcion->update(['estado' => 'rechazada', 'motivo_rechazo' => $datos['motivo'],
                            'revisado_por' => $usuario->id, 'revisado_en' => now()]);
                        break;
                    }
                    $unidad = $alcance->unidad($usuario, $datos['unidad_id']);
                    if ($unidad->rama_id !== $inscripcion->rama_id) {
                        throw ValidationException::withMessages(['unidad_id' => 'Selecciona la rama de la solicitud.']);
                    }
                    $miembro = Miembro::create([
                        'nombre' => $inscripcion->nombre_aspirante, 'apellido' => $inscripcion->apellido_aspirante,
                        'fecha_nacimiento' => $inscripcion->fecha_nacimiento, 'rama_id' => $unidad->rama_id,
                        'grupo_id' => $unidad->grupo_id, 'unidad_id' => $unidad->id,
                        'tutor_id' => $inscripcion->tutor_id, 'codigo' => 'BOL-'.Str::upper(Str::random(10)),
                    ]);
                    $miembro->familias()->attach($inscripcion->tutor_id);
                    FichaMedica::create(['miembro_id' => $miembro->id,
                        'alergias' => $inscripcion->ficha_medica['alergias'] ?? '',
                        'contacto_emergencia' => $inscripcion->ficha_medica['emergencia'] ?? '']);
                    $inscripcion->update(['estado' => 'aprobada', 'miembro_id' => $miembro->id,
                        'unidad_id' => $unidad->id, 'grupo_id' => $unidad->grupo_id,
                        'revisado_por' => $usuario->id, 'revisado_en' => now()]);
                    $this->actualizarAutorizaciones($unidad->id);
                    Auditoria::create(['actor_id' => $usuario->id, 'accion' => 'aprobar_inscripcion',
                        'detalle' => ['inscripcion' => $inscripcion->id, 'miembro' => $miembro->id]]);
                    break;
                case 'asignar':
                    $unidad = $alcance->unidad($usuario, $datos['unidad_id']);
                    $dirigente = empty($datos['dirigente_id']) ? null : User::where('activo', true)
                        ->where('departamento', $usuario->departamento)
                        ->whereHas('rol', fn ($roles) => $roles->where('nombre', 'dirigente'))
                        ->findOrFail($datos['dirigente_id']);
                    $unidad->update(['dirigente_id' => $dirigente?->id]);
                    break;
                case 'asistencia':
                    $actividad = Actividad::findOrFail($datos['actividad_id']);
                    $alcance->unidad($usuario, $actividad->unidad_id);
                    $miembros = $alcance->miembros($usuario)->where('unidad_id', $actividad->unidad_id)->get();
                    foreach ($miembros as $miembro) {
                        if (! isset($datos['asistencias'][$miembro->id])) {
                            throw ValidationException::withMessages(['asistencias' => 'Marca a todos los miembros.']);
                        }
                        Asistencia::updateOrCreate(['actividad_id' => $actividad->id,
                            'miembro_id' => $miembro->id],
                            ['fecha' => $datos['fecha'], 'estado' => $datos['asistencias'][$miembro->id]]);
                    }
                    break;
                case 'progresiones':
                    $miembro = $alcance->miembro($usuario, $datos['miembro_id']);
                    $seleccionados = $datos['requisitos'] ?? [];
                    $permitidos = Requisito::whereNull('rama_id')->orWhere('rama_id',
                        $miembro->rama_id)->pluck('id');
                    abort_if(array_diff($seleccionados, $permitidos->all()), 422, 'Requisito fuera de la rama.');
                    MiembroRequisito::where('miembro_id', $miembro->id)->whereNotIn('requisito_id',
                        $seleccionados)->delete();
                    $insignias = [];
                    foreach ($seleccionados as $requisitoId) {
                        $valores = ['fecha' => $datos['fecha'], 'observacion' => $datos['observacion'] ?? '',
                            'registrado_por' => $usuario->id];
                        MiembroRequisito::updateOrCreate(['miembro_id' => $miembro->id,
                            'requisito_id' => $requisitoId], $valores);
                        if ($insignia = Insignia::where('requisito_id', $requisitoId)->first()) {
                            $insignias[$insignia->id] = $valores;
                        }
                    }
                    $miembro->insignias()->sync($insignias);
                    break;
                case 'leer':
                    $comunicado = Comunicado::whereIn('unidad_id', $alcance->unidades($usuario)->select('id'))
                        ->whereIn('destino', ['ambos', $usuario->rol->nombre])->findOrFail($datos['comunicado_id']);
                    DB::table('comunicado_lecturas')->insertOrIgnore([
                        'comunicado_id' => $comunicado->id, 'usuario_id' => $usuario->id,
                    ]);
                    break;
                case 'perfil':
                    $usuario->update(array_intersect_key($datos, array_flip(['nombre',
                        'apellido', 'email', 'telefono'])));
                    $alcance->miembro($usuario, $usuario->miembro_id)->update($datos);
                    break;
            }
        });

        return back()->with('estado', 'Cambios guardados correctamente.');
    }

    private function actualizarAutorizaciones(?int $unidadId): void
    {
        foreach (Actividad::where('unidad_id', $unidadId)->where('fecha', '>=', today())->get() as $actividad) {
            app(GestionRecursos::class)->generarAutorizaciones($actividad);
        }
    }
}
