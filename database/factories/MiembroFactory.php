<?php

namespace Database\Factories;

use App\Models\Distrito;
use App\Models\GrupoScout;
use App\Models\Rama;
use Illuminate\Database\Eloquent\Factories\Factory;

class MiembroFactory extends Factory
{
    public function definition(): array
    {
        $distrito = Distrito::firstOrCreate(['nombre' => 'Distrito de prueba'], ['departamento' => 'La Paz']);
        $grupo = GrupoScout::firstOrCreate(['distrito_id' => $distrito->id, 'nombre' => 'Grupo de prueba'], ['activo' => true]);
        $rama = Rama::firstOrCreate(['slug' => 'rovers'], ['nombre' => 'Rovers', 'edad_minima' => 18,
            'edad_maxima' => 21, 'descripcion' => 'Rama de prueba', 'color_hex' => '#C62828', 'orden' => 4]);

        return ['grupo_id' => $grupo->id, 'rama_id' => $rama->id, 'nombre' => fake()->firstName(),
            'apellido' => fake()->lastName(), 'fecha_nacimiento' => now()->subYears(19), 'activo' => true];
    }
}
