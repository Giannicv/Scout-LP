<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class DirigenteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $dirigente = $this->route('dirigente');

        return $this->user()?->can($dirigente ? 'updateLeader' : 'manageLeaders', $dirigente ?: User::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        foreach (['nombre', 'apellido', 'email'] as $campo) {
            if (is_string($this->input($campo))) {
                $valor = trim($this->input($campo));
                $this->merge([$campo => $campo === 'email' ? mb_strtolower($valor) : $valor]);
            }
        }
    }

    public function rules(): array
    {
        $dirigente = $this->route('dirigente');

        return [
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => ['required', 'email', 'max:150', Rule::unique('usuarios', 'email')->ignore($dirigente?->id)],
            'grupo_id' => ['required', Rule::exists('grupos_scout', 'id')->where(fn ($q) => $q
                ->where('activo', true)->when($dirigente?->grupo_id, fn ($q) => $q->orWhere('id', $dirigente->grupo_id)))],
            ...($dirigente ? [] : ['password' => ['required', 'confirmed', Password::min(6)->letters()->numbers()]]),
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Este correo ya pertenece a una cuenta o solicitud.',
            'grupo_id.exists' => 'Selecciona un grupo activo.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'password.letters' => 'La contraseña debe incluir al menos una letra.',
            'password.numbers' => 'La contraseña debe incluir al menos un número.',
        ];
    }
}
