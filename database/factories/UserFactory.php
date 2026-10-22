<?php

namespace Database\Factories;

use App\Models\Rol;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return ['rol_id' => Rol::firstOrCreate(['nombre' => 'dirigente_distrital'],
            ['descripcion' => 'Dirigente distrital'])->id,
            'nombre' => fake()->firstName(), 'apellido' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(), 'password_hash' => Hash::make('Password1'),
            'estado' => 'activo', 'version_acceso' => 1];
    }
}
