<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $ahora = now();
            $distrito = DB::table('distritos')->insertGetId(['nombre' => 'Distrito Scout La Paz',
                'departamento' => 'La Paz', 'created_at' => $ahora, 'updated_at' => $ahora]);

            $grupos = [];
            foreach (['Grupo Scout Illimani', 'Grupo Scout Mururata', 'Grupo Scout Sajama'] as $nombre) {
                $grupos[] = DB::table('grupos_scout')->insertGetId(['distrito_id' => $distrito,
                    'nombre' => $nombre, 'direccion' => 'La Paz, Bolivia', 'activo' => true,
                    'created_at' => $ahora, 'updated_at' => $ahora]);
            }

            $roles = [];
            foreach ([
                'dirigente_distrital' => 'Administra el distrito y aprueba dirigentes de grupo.',
                'dirigente_grupo' => 'Administra su grupo y aprueba a sus miembros.',
                'miembro' => 'Participa e inscribe su cuenta en actividades elegibles.',
            ] as $nombre => $descripcion) {
                $roles[$nombre] = DB::table('roles')->insertGetId(compact('nombre', 'descripcion'));
            }

            $ramas = [];
            foreach ([
                ['Lobatos', 'lobatos', 7, 10, '#F5C518', 'Siempre mejor'],
                ['Exploradores', 'exploradores', 11, 14, '#2E7D32', 'Siempre listos'],
                ['Pioneros', 'pioneros', 15, 17, '#1565C0', 'Servir'],
                ['Rovers', 'rovers', 18, 21, '#C62828', 'Servir'],
            ] as $orden => [$nombre, $slug, $min, $max, $color, $lema]) {
                $ramas[$slug] = DB::table('ramas')->insertGetId(['nombre' => $nombre, 'slug' => $slug,
                    'edad_minima' => $min, 'edad_maxima' => $max,
                    'descripcion' => 'Programa educativo Scout para jóvenes de '.$min.' a '.$max.' años.',
                    'lema' => $lema, 'color_hex' => $color, 'orden' => $orden + 1]);
            }

            $password = Hash::make('Scout2026!');
            $distrital = DB::table('usuarios')->insertGetId(['rol_id' => $roles['dirigente_distrital'],
                'nombre' => 'Daniela', 'apellido' => 'Quispe', 'email' => 'distrito@scoutlapaz.bo',
                'password_hash' => $password, 'estado' => 'activo', 'version_acceso' => 1,
                'created_at' => $ahora, 'updated_at' => $ahora]);

            $dirigentes = [];
            foreach ($grupos as $i => $grupo) {
                $dirigentes[] = DB::table('usuarios')->insertGetId(['rol_id' => $roles['dirigente_grupo'],
                    'grupo_id' => $grupo, 'nombre' => 'Dirigente', 'apellido' => 'Grupo '.($i + 1),
                    'email' => 'dirigente'.($i + 1).'@scoutlapaz.bo', 'password_hash' => $password,
                    'estado' => 'activo', 'version_acceso' => 1, 'revisado_por' => $distrital,
                    'revisado_en' => $ahora, 'created_at' => $ahora, 'updated_at' => $ahora]);
            }

            $miembros = [];
            foreach (array_values($ramas) as $i => $rama) {
                $grupo = $grupos[$i % count($grupos)];
                $miembro = DB::table('miembros')->insertGetId(['grupo_id' => $grupo, 'rama_id' => $rama,
                    'nombre' => ['Lucía', 'Mateo', 'Camila', 'Santiago'][$i], 'apellido' => 'Demo',
                    'fecha_nacimiento' => now()->subYears([9, 13, 16, 19][$i])->toDateString(),
                    'email' => 'miembro'.($i + 1).'@scoutlapaz.bo', 'activo' => true,
                    'created_at' => $ahora, 'updated_at' => $ahora]);
                $miembros[] = $miembro;
                DB::table('usuarios')->insert(['rol_id' => $roles['miembro'], 'grupo_id' => $grupo,
                    'miembro_id' => $miembro, 'nombre' => ['Lucía', 'Mateo', 'Camila', 'Santiago'][$i],
                    'apellido' => 'Demo', 'email' => 'miembro'.($i + 1).'@scoutlapaz.bo',
                    'password_hash' => $password, 'estado' => 'activo', 'version_acceso' => 1,
                    'revisado_por' => $dirigentes[$i % count($dirigentes)], 'revisado_en' => $ahora,
                    'created_at' => $ahora, 'updated_at' => $ahora]);
                DB::table('contactos_miembro')->insert(['miembro_id' => $miembro,
                    'nombre' => 'Contacto de emergencia', 'parentesco' => 'Familiar',
                    'telefono' => '7000000'.($i + 1), 'tipo' => 'ambos', 'created_at' => $ahora, 'updated_at' => $ahora]);
            }

            $categoria = DB::table('categorias')->insertGetId(['nombre' => 'Distrito', 'slug' => 'distrito']);
            DB::table('noticias')->insert(['autor_id' => $distrital, 'categoria_id' => $categoria,
                'titulo' => 'Bienvenidos al Distrito Scout La Paz', 'slug' => 'bienvenidos-distrito-scout-la-paz',
                'resumen' => 'Un nuevo espacio digital para nuestra comunidad Scout.',
                'contenido' => 'Aquí encontrarás noticias, eventos, recursos y actividades del Distrito Scout La Paz.',
                'estado' => 'publicado', 'destacada' => true, 'publicado_en' => $ahora,
                'created_at' => $ahora, 'updated_at' => $ahora]);

            foreach ([
                ['Encuentro distrital', 'evento', array_values($ramas), 120],
                ['Servicio comunitario Rover', 'proyecto_rover', [$ramas['rovers']], 30],
                ['Salida de Exploradores', 'actividad', [$ramas['exploradores']], 40],
            ] as $i => [$titulo, $tipo, $ramasEvento, $cupo]) {
                $evento = DB::table('eventos')->insertGetId(['creado_por' => $distrital,
                    'publicado_por' => $distrital, 'titulo' => $titulo, 'tipo' => $tipo,
                    'descripcion' => 'Actividad demostrativa del Distrito Scout La Paz.',
                    'fecha_inicio' => now()->addDays(15 + $i * 10), 'fecha_fin' => now()->addDays(15 + $i * 10)->addHours(4),
                    'lugar' => 'La Paz', 'costo' => 0, 'cupo' => $cupo, 'requiere_autorizacion' => $i === 2,
                    'instrucciones_autorizacion' => $i === 2 ? 'Entregar autorización firmada al dirigente.' : null,
                    'inscripcion_habilitada' => true, 'inscripcion_desde' => now()->subDay(),
                    'inscripcion_hasta' => now()->addDays(10 + $i * 10), 'estado' => 'publicado',
                    'created_at' => $ahora, 'updated_at' => $ahora]);
                foreach ($ramasEvento as $rama) {
                    DB::table('evento_rama')->insert(['evento_id' => $evento, 'rama_id' => $rama]);
                }
            }

            foreach ([['Formación', 'formacion'], ['Normativa', 'normativa'], ['Programas', 'programas']] as $orden => [$nombre, $slug]) {
                DB::table('categorias_recursos')->insert(['nombre' => $nombre, 'slug' => $slug,
                    'descripcion' => 'Documentos de '.$nombre, 'orden' => $orden + 1]);
            }
            foreach ([
                'mision' => ['Nuestra misión', 'Contribuir a la educación de jóvenes mediante el método Scout.'],
                'vision' => ['Nuestra visión', 'Una comunidad activa, segura y comprometida con La Paz.'],
            ] as $clave => [$titulo, $contenido]) {
                DB::table('contenido_institucional')->insert(compact('clave', 'titulo', 'contenido') +
                    ['actualizado_por' => $distrital, 'created_at' => $ahora, 'updated_at' => $ahora]);
            }
        });
    }
}
