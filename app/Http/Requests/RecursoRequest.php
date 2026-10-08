<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Valida únicamente los campos publicados en el catálogo cerrado de recursos. */
class RecursoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $recurso = $this->route('recurso');
        $rol = $this->user()?->rol?->nombre;

        return $rol === 'administrador' || ($rol === 'dirigente' && in_array($recurso,
            ['actividades', 'comunicados']));
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge(['email' => strtolower(trim($this->input('email')))]);
        }
    }

    public function rules(): array
    {
        $recurso = $this->route('recurso');
        $campos = config('recursos.'.$recurso.'.campos');
        abort_unless($campos, 404);
        $reglas = array_map(fn ($campo) => $campo['reglas'], $campos);
        if ($recurso === 'usuarios') {
            $reglas['email'][] = Rule::unique('usuarios', 'email')->ignore($this->route('registro'));
            if (! $this->route('registro')) {
                $reglas['password'][0] = 'required';
            }
        }

        return $reglas;
    }
}
