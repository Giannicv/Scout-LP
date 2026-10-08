<?php

namespace App\Services;

use App\Models\Actividad;
use App\Models\Auditoria;
use App\Models\Autorizacion;
use App\Models\Miembro;
use App\Models\Rol;
use App\Models\Tutor;
use App\Models\Unidad;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** Aplica reglas de negocio y conserva la integridad al guardar y borrar. */
class GestionRecursos
{
    public function guardar(string $recurso, $registro, array $datos, User $usuario)
    {
        return DB::transaction(function () use ($recurso, $registro, $datos, $usuario) {
            if ($recurso === 'usuarios') {
                $rol = Rol::findOrFail($datos['rol_id'])->nombre;
                $this->protegerAdministrador($registro, $rol, (bool) ($datos['activo'] ?? false));
                if ($rol === 'coordinador' && empty($datos['departamento'])) {
                    throw ValidationException::withMessages(['departamento' => 'Selecciona un departamento.']);
                }
                if ($rol === 'miembro' && empty($datos['miembro_id'])) {
                    throw ValidationException::withMessages(['miembro_id' => 'Selecciona la ficha del miembro.']);
                }
                if (! empty($datos['miembro_id']) && User::where('miembro_id', $datos['miembro_id'])
                    ->where('id', '<>', $registro->id ?? 0)->exists()) {
                    throw ValidationException::withMessages(['miembro_id' => 'Esta ficha ya tiene una cuenta.']);
                }
                if (! empty($datos['password'])) {
                    $datos['password_hash'] = Hash::make($datos['password']);
                }
                unset($datos['password']);
                if ($rol === 'familia' && ! $registro->tutor_id) {
                    $datos['telefono'] = $datos['telefono'] ?? '';
                    $datos['tutor_id'] = Tutor::create(array_intersect_key($datos,
                        array_flip(['nombre', 'apellido', 'email', 'telefono'])))->id;
                }
                if ($registro->exists) {
                    $datos['version_acceso'] = $registro->version_acceso + 1;
                }
                $datos['email'] = strtolower($datos['email']);
            }
            if ($recurso === 'noticias' && ! $registro->exists) {
                $datos['slug'] = Str::slug($datos['titulo']).'-'.Str::lower(Str::random(8));
                $datos['autor_id'] = $usuario->id;
            }
            if ($recurso === 'eventos') {
                $datos['estado'] = 'publicado';
                if (! $registro->exists) {
                    $datos['autor_id'] = $usuario->id;
                }
            }
            if ($recurso === 'contenido') {
                $datos['actualizado_por'] = $usuario->id;
            }
            if ($recurso === 'ramas') {
                $datos['actividades'] = array_values(array_filter(array_map('trim',
                    preg_split('/\r?\n/', $datos['actividades_texto']))));
                unset($datos['actividades_texto']);
                if (count($datos['actividades']) < 3 || count($datos['actividades']) > 4) {
                    throw ValidationException::withMessages(['actividades_texto' => 'Incluye entre tres y cuatro actividades.']);
                }
            }
            if ($recurso === 'miembros') {
                if (! $registro->exists) {
                    $datos['codigo'] = 'BOL-'.Str::upper(Str::random(10));
                }
                if (! empty($datos['unidad_id'])) {
                    $unidad = Unidad::findOrFail($datos['unidad_id']);
                    if ($unidad->grupo_id != $datos['grupo_id'] || $unidad->rama_id != $datos['rama_id']) {
                        throw ValidationException::withMessages(['unidad_id' => 'La unidad debe coincidir con grupo y rama.']);
                    }
                }
            }
            $cambiaActividad = false;
            if (in_array($recurso, ['actividades', 'comunicados'], true)) {
                app(AlcanceScout::class)->unidad($usuario, $datos['unidad_id']);
                if ($registro->exists) {
                    app(AlcanceScout::class)->unidad($usuario, $registro->unidad_id);
                }
                if ($recurso === 'comunicados' && ! $registro->exists) {
                    $datos['autor_id'] = $usuario->id;
                }
                if ($recurso === 'actividades' && $registro->exists) {
                    abort_if($registro->unidad_id != $datos['unidad_id'],
                        422, 'No cambies la unidad de una actividad existente.');
                    $registro->fill($datos);
                    $cambiaActividad = $registro->isDirty(['titulo', 'fecha', 'hora', 'lugar', 'descripcion']);
                }
            }
            $registro->fill($datos)->save();
            if ($recurso === 'usuarios' && $rol !== 'dirigente') {
                Unidad::where('dirigente_id', $registro->id)->update(['dirigente_id' => null]);
            }
            if ($recurso === 'actividades') {
                if ($cambiaActividad) {
                    Autorizacion::where('actividad_id', $registro->id)->update(['estado' => 'pendiente',
                        'firmada_en' => null]);
                }
                $this->generarAutorizaciones($registro);
            }

            return $registro;
        });
    }

    public function generarAutorizaciones(Actividad $actividad): void
    {
        if (! $actividad->requiere_autorizacion) {
            Autorizacion::where('actividad_id', $actividad->id)->delete();

            return;
        }
        foreach (Miembro::with('familias')->where('unidad_id', $actividad->unidad_id)->get() as $miembro) {
            foreach ($miembro->familias as $tutor) {
                Autorizacion::firstOrCreate([
                    'actividad_id' => $actividad->id, 'miembro_id' => $miembro->id, 'tutor_id' => $tutor->id,
                ], ['estado' => 'pendiente']);
            }
        }
    }

    public function protegerAdministrador(User $usuario, ?string $nuevoRol, bool $activo): void
    {
        $administradores = User::whereHas('rol', fn ($roles) => $roles->where('nombre', 'administrador'))
            ->where('activo', true)->lockForUpdate()->get();
        if ($usuario->exists && $administradores->contains('id', $usuario->id)
            && ($nuevoRol !== 'administrador' || ! $activo) && $administradores->count() <= 1) {
            throw ValidationException::withMessages(['rol_id' => 'No puedes quitar al último administrador activo.']);
        }
    }

    public function eliminar(string $recurso, $registro, User $actor): void
    {
        DB::transaction(function () use ($recurso, $registro, $actor) {
            if (in_array($recurso, ['usuarios', 'miembros'], true)) {
                $usuario = $recurso === 'usuarios' ? $registro : User::where('miembro_id', $registro->id)->first();
                if ($usuario) {
                    abort_if($usuario->id === $actor->id, 409, 'No puedes eliminar tu propia cuenta.');
                    $this->protegerAdministrador($usuario, null, false);
                    if ($usuario->miembro_id) {
                        Miembro::whereKey($usuario->miembro_id)->delete();
                    }
                    if ($usuario->tutor_id) {
                        if (config('scouts.eliminar_hijos_con_familia')) {
                            Miembro::where('tutor_id', $usuario->tutor_id)->delete();
                        }
                        Tutor::whereKey($usuario->tutor_id)->delete();
                    }
                    $usuario->delete();
                }
            }
            $registro->delete();
            Auditoria::create([
                'actor_id' => $actor->id, 'accion' => 'eliminar_'.$recurso,
                'detalle' => ['id' => $registro->getKey()],
            ]);
        });
    }
}
