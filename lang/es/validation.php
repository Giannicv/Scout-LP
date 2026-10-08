<?php

return [
    'required' => 'El campo :attribute es obligatorio.',
    'accepted' => 'Debes aceptar :attribute.',
    'email' => 'Escribe un correo válido en :attribute.',
    'unique' => 'El valor de :attribute ya está registrado.',
    'exists' => 'Selecciona una opción válida en :attribute.',
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'date' => 'Escribe una fecha válida en :attribute.',
    'before' => ':attribute debe ser anterior a :date.',
    'integer' => ':attribute debe ser un número entero.',
    'in' => 'El valor seleccionado de :attribute es inválido.',
    'regex' => 'El formato de :attribute es inválido.',
    'min' => ['string' => ':attribute debe tener al menos :min caracteres.'],
    'max' => ['string' => ':attribute no puede superar :max caracteres.'],
    'between' => ['numeric' => ':attribute debe estar entre :min y :max.'],
    'attributes' => ['email' => 'correo electrónico', 'password' => 'contraseña'],
];
