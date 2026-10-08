<?php

namespace Database\Seeders;

use App\Models\Distrito;
use App\Models\GrupoScout;
use App\Models\Insignia;
use App\Models\Rama;
use App\Models\Requisito;
use App\Models\Rol;
use Illuminate\Database\Seeder;

/** Carga catálogos, ramas educativas y requisitos sin duplicar registros. */
class CatalogosSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['administrador', 'coordinador', 'dirigente', 'familia', 'miembro'] as $nombreRol) {
            Rol::firstOrCreate(['nombre' => $nombreRol], ['descripcion' => ucfirst($nombreRol)]);
        }
        $rangos = ['manada' => [7, 10], 'tropa' => [11, 14], 'comunidad' => [15, 17], 'clan' => [18, 21]];
        foreach (ContextoDemo::datos()['publico']['ramas'] as $posicion => $rama) {
            Rama::firstOrCreate(['slug' => $rama['id']], [
                'nombre' => $rama['nombre'], 'edad_minima' => $rangos[$rama['id']][0],
                'edad_maxima' => $rangos[$rama['id']][1], 'orden' => $posicion,
                'descripcion' => $rama['descripcion'], 'lema' => $rama['frase'],
                'imagen_url' => $rama['imagen'], 'actividades' => $rama['actividades'],
            ]);
        }
        foreach (ContextoDemo::datos()['publico']['departamentos'] as $departamento) {
            $distrito = Distrito::firstOrCreate(['departamento' => $departamento],
                ['nombre' => 'Distrito '.$departamento]);
            GrupoScout::firstOrCreate(['distrito_id' => $distrito->id], ['nombre' => 'Grupo '.$departamento]);
        }
        foreach (ContextoDemo::datos()['sprint']['requisitos'] as $requisito) {
            $registro = Requisito::firstOrCreate(['nombre' => $requisito['nombre']],
                ['etapa' => $requisito['etapa'], 'tipo' => $requisito['tipo']]);
            if ($registro->tipo === 'Insignia') {
                Insignia::firstOrCreate(['requisito_id' => $registro->id],
                    ['nombre' => $registro->nombre, 'etapa' => $registro->etapa]);
            }
        }
    }
}
