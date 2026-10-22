<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventoFactory extends Factory
{
    public function definition(): array
    {
        return ['creado_por' => User::factory(), 'titulo' => fake()->sentence(4), 'tipo' => 'evento',
            'descripcion' => fake()->paragraph(), 'fecha_inicio' => now()->addWeek(),
            'fecha_fin' => now()->addWeek()->addHours(3), 'lugar' => 'La Paz', 'costo' => 0,
            'inscripcion_habilitada' => true, 'estado' => 'borrador'];
    }
}
