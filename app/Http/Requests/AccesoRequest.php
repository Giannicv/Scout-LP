<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/** Valida login, registro, recuperación y cambio de contraseña. */
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
                'nombre' => 'required|string|max:100', 'apellido' => 'required|string|max:100',
                'email' => 'required|email|max:150|unique:usuarios,email',
                'telefono' => 'required|string|max:30', 'terminos' => 'accepted',
                'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            ],
            'recuperar.enviar' => ['email' => 'required|email|max:150'],
            default => [
                'token' => 'nullable|string', 'actual' => 'nullable|string',
                'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            ],
        };
    }
}
