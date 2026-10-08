<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Valida el contacto público antes de guardar el mensaje. */
class ContactoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:100', 'email' => 'required|email|max:150',
            'telefono' => 'nullable|string|max:30', 'asunto' => 'required|string|max:150',
            'rama_id' => 'nullable|exists:ramas,id', 'mensaje' => 'required|string|min:10|max:5000',
            'aceptacion' => 'accepted',
        ];
    }
}
