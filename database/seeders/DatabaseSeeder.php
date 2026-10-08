<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** Orquesta semillas de demostración dentro de una única transacción. */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->call([
                CatalogosSeeder::class, UsuariosDemoSeeder::class, MiembrosSeeder::class,
                ContenidoSeeder::class, ActividadesSeeder::class,
            ]);
        });
    }
}
