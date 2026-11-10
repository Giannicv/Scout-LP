<?php

namespace App\Http\Requests;

use App\Enums\EstadoEvento;
use App\Enums\TipoEvento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class EventoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->estaActivo() ?? false;
    }

    public function rules(): array
    {
        return [
            'titulo' => 'required|string|max:180',
            'tipo' => ['required', Rule::enum(TipoEvento::class)],
            'descripcion' => 'required|string|max:10000',
            'fecha_inicio' => 'required|date', 'fecha_fin' => 'required|date|after:fecha_inicio',
            'lugar' => 'required|string|max:180', 'mapa_url' => 'nullable|url|max:255',
            'costo' => 'nullable|numeric|min:0', 'cupo' => 'nullable|integer|min:1',
            'que_llevar' => 'nullable|string|max:5000', 'indicaciones' => 'nullable|string|max:5000',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'ramas' => 'required|array|min:1', 'ramas.*' => 'integer|exists:ramas,id',
            'requiere_autorizacion' => 'nullable|boolean',
            'instrucciones_autorizacion' => 'nullable|required_if:requiere_autorizacion,1|string|max:5000',
            'inscripcion_habilitada' => 'nullable|boolean',
            'inscripcion_desde' => 'nullable|date|before:fecha_inicio',
            'inscripcion_hasta' => 'nullable|date|after_or_equal:inscripcion_desde|before_or_equal:fecha_inicio',
            'estado' => ['nullable', Rule::in([EstadoEvento::BORRADOR->value, EstadoEvento::PUBLICADO->value])],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            if (! $validator->errors()->has('fecha_inicio')
                && (! $this->route('evento') || $this->input('estado') === EstadoEvento::PUBLICADO->value)
                && $this->date('fecha_inicio')?->lte(now())) {
                $validator->errors()->add('fecha_inicio', 'Un evento nuevo o publicado debe comenzar en una fecha futura.');
            }
        }];
    }

    public function messages(): array
    {
        return [
            'inscripcion_desde.before' => 'La apertura de inscripciones debe ser anterior al inicio del evento.',
            'inscripcion_hasta.before_or_equal' => 'El cierre de inscripciones no puede ser posterior al inicio del evento.',
            'inscripcion_hasta.after_or_equal' => 'El cierre debe ser posterior a la apertura de inscripciones.',
            'imagen.image' => 'El archivo seleccionado debe ser una imagen válida.',
            'imagen.max' => 'La imagen no puede superar los 5 MB.',
        ];
    }
}
