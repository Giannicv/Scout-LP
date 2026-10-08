<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Comprueba que ejecutar dos veces las semillas no duplique los ejemplos. */
class SemillasScoutTest extends TestCase
{
    use DatabaseTransactions;

    public function test_semillas_repetibles(): void
    {
        $this->seed(DatabaseSeeder::class);
        $tablas = ['usuarios', 'miembros', 'noticias', 'eventos', 'actividades', 'cuotas', 'inscripciones'];
        $cantidades = [];
        foreach ($tablas as $tabla) {
            $cantidades[$tabla] = DB::table($tabla)->count();
        }
        $this->seed(DatabaseSeeder::class);
        foreach ($cantidades as $tabla => $cantidad) {
            $this->assertSame($cantidad, DB::table($tabla)->count(), $tabla);
        }
    }
}
