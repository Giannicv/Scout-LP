<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\ContenidoInstitucional;
use App\Models\Evento;
use App\Models\MensajeContacto;
use App\Models\Noticia;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/** Conserva las historias, eventos y textos públicos del prototipo. */
class ContenidoSeeder extends Seeder
{
    public function run(): void
    {
        $datos = ContextoDemo::datos()['publico'];
        foreach ($datos['noticias'] as $noticia) {
            $categoria = Categoria::firstOrCreate(['nombre' => $noticia['categoria']],
                ['slug' => Str::slug($noticia['categoria'])]);
            Noticia::firstOrCreate(['slug' => 'demo-'.$noticia['id']], [
                'autor_id' => ContextoDemo::usuario('u-admin')->id, 'categoria_id' => $categoria->id,
                'titulo' => $noticia['titulo'], 'resumen' => $noticia['extracto'],
                'contenido' => $noticia['contenido'], 'imagen_url' => $noticia['imagen'],
                'estado' => 'publicado', 'publicado_en' => $noticia['fecha'], 'destacada' => $noticia['destacada'],
            ]);
        }
        foreach ($datos['eventos'] as $posicionEvento => $evento) {
            Evento::firstOrCreate(['titulo' => $evento['titulo']], [
                'autor_id' => ContextoDemo::usuario('u-admin')->id,
                'rama_id' => ContextoDemo::rama($evento['rama'])->id,
                'fecha_inicio' => today()->addDays(7 + $posicionEvento * 12)->format('Y-m-d').' '.$evento['hora'],
                'lugar' => $evento['lugar'], 'descripcion' => $evento['descripcion'], 'estado' => 'publicado',
            ]);
        }
        foreach ($datos['mensajes'] as $mensaje) {
            MensajeContacto::firstOrCreate(['email' => $mensaje['correo'], 'asunto' => $mensaje['asunto']], [
                'nombre' => $mensaje['nombre'], 'telefono' => $mensaje['telefono'],
                'mensaje' => $mensaje['mensaje'],
                'rama_id' => ContextoDemo::rama($mensaje['rama'])->id,
                'estado' => $mensaje['estado'] === 'Nuevo' ? 'nuevo' : 'leido',
            ]);
        }
        $textos = [
            'mision' => 'Contribuir a la educación de niñas, niños y jóvenes a través de valores, experiencias y servicio, para que participen activamente en la construcción de un mundo mejor.',

            'vision' => 'Un movimiento cercano e inclusivo, donde cada joven encuentre oportunidades para desarrollar su potencial y transformar positivamente su comunidad.',

            'historia' => 'Más de un siglo de escultismo boliviano. Fundación en 1911. Los hitos de 1950 y 1969 son ejemplos pendientes de verificación oficial.',

        ];
        foreach ($textos as $clave => $contenido) {
            ContenidoInstitucional::firstOrCreate(['clave' => $clave],
                ['titulo' => 'Nuestra '.$clave, 'contenido' => $contenido]);
        }
    }
}
