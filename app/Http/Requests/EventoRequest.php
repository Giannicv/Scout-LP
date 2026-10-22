<?php

namespace App\Http\Requests;

use App\Enums\EstadoEvento;
use App\Enums\TipoEvento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'ramas' => 'required|array|min:1', 'ramas.*' => 'integer|exists:ramas,id',
            'requiere_autorizacion' => 'nullable|boolean',
            'instrucciones_autorizacion' => 'nullable|required_if:requiere_autorizacion,1|string|max:5000',
            'inscripcion_habilitada' => 'nullable|boolean',
            'inscripcion_desde' => 'nullable|date', 'inscripcion_hasta' => 'nullable|date|after_or_equal:inscripcion_desde',
            'estado' => ['nullable', Rule::in([EstadoEvento::BORRADOR->value, EstadoEvento::PUBLICADO->value])],
        ];
    }
}
