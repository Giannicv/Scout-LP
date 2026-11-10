<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('ramas')
            ->where('slug', 'rovers')
            ->update(['imagen_url' => 'img/Rovers.png']);
    }

    public function down(): void
    {
        DB::table('ramas')
            ->where('slug', 'rovers')
            ->where('imagen_url', 'img/Rovers.png')
            ->update(['imagen_url' => null]);
    }
};
