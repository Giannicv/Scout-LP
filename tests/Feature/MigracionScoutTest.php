<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Inscripcion;
use App\Models\Noticia;
use App\Models\Rol;
use App\Models\Tutor;
use App\Models\Unidad;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** Comprueba PostgreSQL real con transacciones revertidas; nunca ejecuta migraciones. */
class MigracionScoutTest extends TestCase
{
    use DatabaseTransactions;

    public function test_paginas_publicas_y_paneles(): void
    {
        foreach (['/', '/ramas', '/noticias', '/nosotros', '/contacto', '/login', '/registro', '/recuperar'] as $ruta) {
            $this->get($ruta)->assertOk();
        }
        $noticia = Noticia::where('estado', 'publicado')->firstOrFail();
        $this->get(route('noticias.show', $noticia))->assertOk();
        foreach (config('scouts.menus') as $prefijo => $paginas) {
            $usuario = User::where('email', $prefijo.'@scouts.bo')->firstOrFail();
            $this->actingAs($usuario);
            foreach ($paginas as $pagina => $titulo) {
                $this->get(route($prefijo.'.'.$pagina))->assertOk();
            }
        }
        $this->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_registro_contacto_inscripcion_y_aprobacion(): void
    {
        $this->post(route('contacto.enviar'), ['nombre' => 'Prueba Laravel', 'email' => 'qa@ejemplo.test',
            'asunto' => 'Consulta general', 'mensaje' => 'Mensaje de verificación Laravel.', 'aceptacion' => 1])
            ->assertRedirect(route('mensaje-enviado'));
        $this->assertDatabaseHas('mensajes_contacto', ['email' => 'qa@ejemplo.test']);
        $this->post(route('registro.enviar'), ['nombre' => 'Familia', 'apellido' => 'Laravel',
            'email' => 'familia-laravel@ejemplo.test', 'telefono' => '70000000', 'password' => 'prueba123',
            'password_confirmation' => 'prueba123', 'terminos' => 1])->assertSessionHasNoErrors();
        $familia = User::where('email', 'familia-laravel@ejemplo.test')->firstOrFail();
        $this->assertNotNull($familia->tutor_id);
        $this->assertTrue(Hash::check('prueba123', $familia->password_hash));
        $this->get(route('familia.dashboard'))->assertOk();
        $this->actingAs($familia)->post(route('familia.operacion', ['accion' => 'inscribir']), [
            'nombre' => 'Joven', 'apellido' => 'Laravel', 'edad' => 12, 'departamento' => 'La Paz',
            'emergencia' => 'Tutor 70000000', 'confirmacion' => 1,
        ])->assertSessionHasNoErrors();
        $inscripcion = Inscripcion::where('tutor_id', $familia->tutor_id)->firstOrFail();
        $this->assertSame('pendiente', $inscripcion->estado);
        $coordinador = User::where('email', 'coordinador@scouts.bo')->firstOrFail();
        $unidad = Unidad::where('rama_id', $inscripcion->rama_id)->whereHas('grupo.distrito',
            fn ($distritos) => $distritos->where('departamento', 'La Paz'))->firstOrFail();
        $this->actingAs($coordinador)->post(route('coordinador.operacion', ['accion' => 'resolver']),
            ['inscripcion_id' => $inscripcion->id, 'estado' => 'aprobada', 'unidad_id' => $unidad->id])
            ->assertSessionHasNoErrors();
        $inscripcion->refresh();
        $this->assertSame('aprobada', $inscripcion->estado);
        $this->assertDatabaseHas('miembros', ['id' => $inscripcion->miembro_id, 'unidad_id' => $unidad->id]);
        $this->actingAs($familia)->get(route('coordinador.inscripciones'))->assertForbidden();
    }

    public function test_noticias_crud_y_borrado_protegido(): void
    {
        $administrador = User::where('email', 'admin@scouts.bo')->firstOrFail();
        $this->actingAs($administrador);
        foreach (['noticias', 'eventos', 'miembros', 'usuarios'] as $recurso) {
            $this->get(route('admin.gestion.registros.create', ['recurso' => $recurso]))->assertOk();
        }
        $datos = ['titulo' => 'Prueba Laravel', 'categoria_id' => Categoria::firstOrFail()->id,
            'publicado_en' => today()->toDateString(), 'resumen' => 'Resumen de prueba',
            'contenido' => 'Contenido de prueba', 'estado' => 'publicado', 'destacada' => 1];
        $this->post(route('admin.gestion.registros.store', ['recurso' => 'noticias']), $datos)
            ->assertSessionHasNoErrors();
        $noticia = Noticia::where('titulo', 'Prueba Laravel')->firstOrFail();
        $this->get(route('admin.gestion.registros.edit', ['recurso' => 'noticias', 'registro' => $noticia->id]))->assertOk();
        $datos['titulo'] = 'Prueba editada';
        $this->put(route('admin.gestion.registros.update', ['recurso' => 'noticias', 'registro' => $noticia->id]), $datos)
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('noticias', ['id' => $noticia->id, 'titulo' => 'Prueba editada']);
        $this->delete(route('admin.gestion.registros.destroy', ['recurso' => 'noticias', 'registro' => $noticia->id]))
            ->assertRedirect();
        $this->assertDatabaseMissing('noticias', ['id' => $noticia->id]);
        $this->delete(route('admin.gestion.registros.destroy', ['recurso' => 'usuarios', 'registro' => $administrador->id]))
            ->assertStatus(409);
        $tutor = Tutor::create(['nombre' => 'Tutor', 'apellido' => 'Prueba', 'telefono' => '70000000']);
        $familia = User::create(['nombre' => 'Tutor', 'apellido' => 'Prueba', 'email' => 'borrar@ejemplo.test',
            'rol_id' => Rol::where('nombre', 'familia')->firstOrFail()->id, 'tutor_id' => $tutor->id,
            'password_hash' => Hash::make('prueba123')]);
        $this->delete(route('admin.gestion.registros.destroy', ['recurso' => 'usuarios', 'registro' => $familia->id]))
            ->assertRedirect();
        $this->assertDatabaseMissing('usuarios', ['id' => $familia->id]);
        $this->assertDatabaseMissing('tutores', ['id' => $tutor->id]);
        $this->assertDatabaseHas('auditoria', ['accion' => 'eliminar_usuarios']);
    }
}
