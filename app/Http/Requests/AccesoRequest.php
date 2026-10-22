<?php

namespace App\Http\Requests;

use App\Models\Rama;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AccesoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge(['email' => strtolower(trim($this->input('email')))]);
        }
    }

    public function rules(): array
    {
        return match ($this->route()->getName()) {
            'login.enviar' => ['email' => 'required|email|max:150', 'password' => 'required|string'],
            'registro.enviar' => [
                'tipo_cuenta' => ['required', Rule::in(['miembro', 'dirigente_grupo'])],
                'grupo_id' => 'required|exists:grupos_scout,id',
                'rama_id' => 'required_if:tipo_cuenta,miembro|nullable|exists:ramas,id',
                'fecha_nacimiento' => 'required_if:tipo_cuenta,miembro|nullable|date|before:today',
                'nombre' => 'required|string|max:100', 'apellido' => 'required|string|max:100',
                'email' => 'required|email|max:150|unique:usuarios,email',
                'telefono' => 'nullable|string|max:30', 'terminos' => 'accepted',
                'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            ],
            'recuperar.enviar' => ['email' => 'required|email|max:150'],
            default => ['token' => 'nullable|string', 'email' => 'nullable|email', 'actual' => 'nullable|string',
                'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()]],
        };
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->route()->getName() !== 'registro.enviar' || $this->tipo_cuenta !== 'miembro'
                || ! $this->filled(['rama_id', 'fecha_nacimiento'])) {
                return;
            }
            $rama = Rama::find($this->rama_id);
            if (! $rama) {
                return;
            }

            try {
                $edad = Carbon::parse($this->fecha_nacimiento)->age;
            } catch (\Throwable) {
                return;
            }
            if ($edad < $rama->edad_minima || $edad > $rama->edad_maxima) {
                $validator->errors()->add('rama_id', "La edad debe estar entre {$rama->edad_minima} y {$rama->edad_maxima} años para {$rama->nombre}.");
            }
        });
    }
}
