<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Valida los flujos propios de cada rol, incluidos los formularios por lotes. */
class OperacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $roles = [
            'inscribir' => ['familia'], 'vincular' => ['familia'], 'medica' => ['familia'],
            'autorizar' => ['familia'], 'pagar' => ['familia'],
            'resolver' => ['coordinador'], 'asignar' => ['coordinador'],
            'asistencia' => ['dirigente'], 'progresiones' => ['dirigente'],
            'leer' => ['familia', 'miembro'], 'perfil' => ['miembro'],
        ];

        return in_array($this->user()?->rol?->nombre, $roles[$this->route('accion')] ?? [], true);
    }

    public function rules(): array
    {
        return match ($this->route('accion')) {
            'inscribir' => [
                'nombre' => 'required|string|max:100', 'apellido' => 'required|string|max:100',
                'edad' => 'required|integer|between:7,21', 'departamento' => 'required|exists:distritos,departamento',

                'alergias' => 'nullable|string|max:2000', 'emergencia' => 'required|string|max:300',
                'confirmacion' => 'accepted',
            ],
            'vincular' => ['nombre' => 'required|string|max:201', 'codigo' => 'required|string|max:60'],
            'medica' => ['miembro_id' => 'required|integer', 'alergias' => 'nullable|string|max:2000',
                'contacto_emergencia' => 'required|string|max:300'],
            'autorizar' => ['autorizacion_id' => 'required|integer', 'estado' => 'required|in:firmada,rechazada'],
            'pagar' => ['cuota_id' => 'required|integer', 'comprobante_nombre' => 'required|string|max:200'],
            'resolver' => ['inscripcion_id' => 'required|integer', 'estado' => 'required|in:aprobada,rechazada',
                'unidad_id' => 'required_if:estado,aprobada|nullable|integer',
                'motivo' => 'required_if:estado,rechazada|nullable|string|max:2000'],
            'asignar' => ['unidad_id' => 'required|integer', 'dirigente_id' => 'nullable|integer'],
            'asistencia' => ['actividad_id' => 'required|integer', 'fecha' => 'required|date',
                'asistencias' => 'required|array|max:500', 'asistencias.*' => 'required|in:presente,ausente,justificado'],

            'progresiones' => ['miembro_id' => 'required|integer', 'requisitos' => 'nullable|array|max:100',
                'requisitos.*' => 'integer|exists:requisitos,id', 'fecha' => 'required|date',
                'observacion' => 'nullable|string|max:2000'],
            'leer' => ['comunicado_id' => 'required|integer'],
            'perfil' => ['nombre' => 'required|string|max:100', 'apellido' => 'required|string|max:100',
                'email' => ['required', 'email', 'max:150', Rule::unique('usuarios')->ignore($this->user()->id)],
                'telefono' => 'nullable|string|max:30', 'lema' => 'nullable|string|max:300',
                'foto' => ['nullable', 'string', 'max:255', 'regex:~^(https?://|img/)~']],
            default => [],
        };
    }
}
