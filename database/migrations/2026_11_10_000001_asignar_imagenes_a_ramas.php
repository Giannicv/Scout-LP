<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Asigna las fotografías locales de las ramas disponibles. */
    public function up(): void
    {
        foreach ($this->imagenes() as $slug => $imagen) {
            DB::table('ramas')->where('slug', $slug)->update(['imagen_url' => $imagen]);
        }
    }

    public function down(): void
    {
        foreach ($this->imagenes() as $slug => $imagen) {
            DB::table('ramas')
                ->where('slug', $slug)
                ->where('imagen_url', $imagen)
                ->update(['imagen_url' => null]);
        }
    }

    /** @return array<string, string> */
    private function imagenes(): array
    {
        return [
            'lobatos' => 'img/Lobatos.png',
            'exploradores' => 'img/Exploradores.png',
            'pioneros' => 'img/Pioneros.png',
        ];
    }
};
