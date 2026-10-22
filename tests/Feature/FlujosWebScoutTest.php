<?php

namespace Tests\Feature;

use App\Models\Evento;
use App\Models\GrupoScout;
use App\Models\Miembro;
use App\Models\Rama;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class FlujosWebScoutTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_la_base_no_contiene_tablas_legacy_y_si_el_nuevo_nucleo(): void
    {
        foreach (['eventos', 'evento_rama', 'inscripciones_evento', 'contactos_miembro', 'recursos', 'notifications'] as $tabla) {
            $this->assertTrue(\Schema::hasTable($tabla));
        }
        foreach (['actividades', 'asistencias', 'autorizaciones', 'cuotas', 'fichas_medicas', 'inscripciones', 'unidades', 'tutores'] as $tabla) {
            $this->assertFalse(\Schema::hasTable($tabla));
        }
    }

    public function test_el_registro_publico_de_miembro_queda_pendiente(): void
    {
        $grupo = GrupoScout::firstOrFail();
        $rama = Rama::firstOrFail();
        $this->post(route('registro.enviar'), $this->registro(['grupo_id' => $grupo->id, 'rama_id' => $rama->id]))
            ->assertRedirect(route('login'));
        $this->assertDatabaseHas('usuarios', ['email' => 'nuevo@prueba.test', 'estado' => 'pendiente']);
        $this->assertDatabaseHas('miembros', ['email' => 'nuevo@prueba.test', 'grupo_id' => $grupo->id]);
    }

    public function test_no_se_puede_solicitar_el_rol_distrital_desde_el_registro(): void
    {
        $this->post(route('registro.enviar'), $this->registro(['tipo_cuenta' => 'dirigente_distrital']))
            ->assertSessionHasErrors('tipo_cuenta');
    }

    public function test_la_edad_debe_corresponder_a_la_rama_solicitada(): void
    {
        $lobatos = Rama::where('slug', 'lobatos')->firstOrFail();
        $this->post(route('registro.enviar'), $this->registro(['rama_id' => $lobatos->id,
            'fecha_nacimiento' => now()->subYears(18)->toDateString()]))->assertSessionHasErrors('rama_id');
    }

    public function test_una_cuenta_pendiente_no_puede_iniciar_sesion(): void
    {
        $usuario = $this->usuario('miembro', GrupoScout::first()->id, 'pendiente');
        $this->post(route('login.enviar'), ['email' => $usuario->email, 'password' => 'Password1'])
            ->assertSessionHasErrors('email');
    }

    public function test_el_dirigente_solo_aprueba_miembros_de_su_grupo(): void
    {
        [$grupoA, $grupoB] = GrupoScout::take(2)->get();
        $dirigente = $this->usuario('dirigente_grupo', $grupoA->id);
        $propio = $this->usuario('miembro', $grupoA->id, 'pendiente');
        $ajeno = $this->usuario('miembro', $grupoB->id, 'pendiente');
        $this->actingAs($dirigente)->post(route('solicitudes.resolver', $propio), ['decision' => 'aprobar'])->assertRedirect();
        $this->assertSame('activo', $propio->fresh()->estado);
        $this->actingAs($dirigente)->post(route('solicitudes.resolver', $ajeno), ['decision' => 'aprobar'])->assertForbidden();
    }

    public function test_solo_el_dirigente_distrital_aprueba_dirigentes_de_grupo(): void
    {
        $grupo = GrupoScout::first();
        $solicitud = $this->usuario('dirigente_grupo', $grupo->id, 'pendiente');
        $otro = $this->usuario('dirigente_grupo', $grupo->id);
        $this->actingAs($otro)->post(route('solicitudes.resolver', $solicitud), ['decision' => 'aprobar'])->assertForbidden();
        $distrital = $this->usuario('dirigente_distrital');
        $this->actingAs($distrital)->post(route('solicitudes.resolver', $solicitud), ['decision' => 'aprobar'])->assertRedirect();
        $this->assertSame('activo', $solicitud->fresh()->estado);
    }

    public function test_un_evento_admite_varias_ramas(): void
    {
        $distrital = $this->usuario('dirigente_distrital');
        $ramas = Rama::take(2)->pluck('id')->all();
        $this->actingAs($distrital)->post(route('eventos.store'), $this->datosEvento(['ramas' => $ramas]))->assertRedirect(route('eventos.index'));
        $evento = Evento::latest('id')->first();
        $this->assertCount(2, $evento->ramas);
    }

    public function test_miembro_elegible_se_inscribe_y_no_duplica_inscripcion(): void
    {
        $rama = Rama::where('slug', 'lobatos')->first();
        $miembroUsuario = $this->usuarioMiembro($rama);
        $evento = $this->eventoPublicado([$rama->id]);
        $this->actingAs($miembroUsuario)->post(route('eventos.inscribir', $evento))->assertRedirect();
        $this->actingAs($miembroUsuario)->post(route('eventos.inscribir', $evento))->assertRedirect();
        $this->assertDatabaseCount('inscripciones_evento', 1);
    }

    public function test_miembro_de_otra_rama_no_puede_inscribirse(): void
    {
        $ramas = Rama::take(2)->get();
        $usuario = $this->usuarioMiembro($ramas[0]);
        $evento = $this->eventoPublicado([$ramas[1]->id]);
        $this->actingAs($usuario)->post(route('eventos.inscribir', $evento))->assertSessionHasErrors('evento');
    }

    public function test_ventana_cerrada_y_cupo_lleno_impiden_inscripcion(): void
    {
        $rama = Rama::first();
        $usuario = $this->usuarioMiembro($rama);
        $cerrado = $this->eventoPublicado([$rama->id], ['inscripcion_hasta' => now()->subMinute()]);
        $this->actingAs($usuario)->post(route('eventos.inscribir', $cerrado))->assertSessionHasErrors('evento');
        $lleno = $this->eventoPublicado([$rama->id], ['cupo' => 1]);
        $otroMiembro = Miembro::where('rama_id', $rama->id)->whereKeyNot($usuario->miembro_id)->first();
        if (! $otroMiembro) {
            $otroMiembro = Miembro::create(['grupo_id' => GrupoScout::first()->id, 'rama_id' => $rama->id,
                'nombre' => 'Otro', 'apellido' => 'Miembro', 'fecha_nacimiento' => '2015-01-01']);
        }
        \DB::table('inscripciones_evento')->insert(['evento_id' => $lleno->id, 'miembro_id' => $otroMiembro->id,
            'estado' => 'inscrito', 'inscrito_en' => now(), 'created_at' => now(), 'updated_at' => now()]);
        $this->actingAs($usuario)->post(route('eventos.inscribir', $lleno))->assertSessionHasErrors('evento');
    }

    public function test_solo_un_rover_propone_proyectos_rover(): void
    {
        $rover = $this->usuarioMiembro(Rama::where('slug', 'rovers')->first());
        $this->actingAs($rover)->post(route('eventos.store'), $this->datosEvento(['tipo' => 'proyecto_rover',
            'ramas' => [$rover->miembro->rama_id]]))->assertRedirect(route('eventos.index'));
        $this->assertDatabaseHas('eventos', ['propuesto_por_miembro_id' => $rover->miembro_id, 'estado' => 'propuesto']);
        $lobato = $this->usuarioMiembro(Rama::where('slug', 'lobatos')->first());
        $this->actingAs($lobato)->post(route('eventos.store'), $this->datosEvento(['tipo' => 'proyecto_rover',
            'ramas' => [$lobato->miembro->rama_id]]))->assertForbidden();
    }

    public function test_biblioteca_solo_permite_publicar_al_rol_distrital(): void
    {
        Storage::fake('local');
        $categoria = \DB::table('categorias_recursos')->value('id');
        $archivo = UploadedFile::fake()->create('guia.pdf', 100, 'application/pdf');
        $dirigente = $this->usuario('dirigente_grupo', GrupoScout::first()->id);
        $this->actingAs($dirigente)->post(route('biblioteca.store'), ['categoria_recurso_id' => $categoria,
            'titulo' => 'Guía', 'archivo' => $archivo])->assertForbidden();
        $distrital = $this->usuario('dirigente_distrital');
        $this->actingAs($distrital)->post(route('biblioteca.store'), ['categoria_recurso_id' => $categoria,
            'titulo' => 'Guía', 'archivo' => UploadedFile::fake()->create('guia.pdf', 100, 'application/pdf')])->assertRedirect();
        $this->assertDatabaseHas('recursos', ['titulo' => 'Guía']);
    }

    public function test_rutas_publicas_y_salud_responden(): void
    {
        $this->get(route('inicio'))->assertOk()
            ->assertSee('js/tema.js')
            ->assertSee('img/scout-salar.jpg', false)
            ->assertSee('data-tema-toggle', false);
        $this->get(route('ramas'))->assertOk();
        $this->get(route('salud'))->assertOk()->assertJson(['ok' => true]);
    }

    public function test_el_tema_esta_disponible_en_acceso_y_panel(): void
    {
        $this->get(route('login'))->assertOk()
            ->assertSee('js/tema.js')
            ->assertSee('data-tema-toggle', false);

        $distrital = User::where('email', 'distrito@scoutlapaz.bo')->firstOrFail();
        $this->actingAs($distrital)->get(route('panel'))->assertOk()
            ->assertSee('js/tema.js')
            ->assertSee('data-tema-toggle', false);
    }

    public function test_el_distrital_publica_una_historia_destacada_en_el_inicio(): void
    {
        $distrital = User::where('email', 'distrito@scoutlapaz.bo')->firstOrFail();
        $datos = ['titulo' => 'Historia de servicio', 'resumen' => 'Una experiencia que inspira.',
            'contenido' => 'El grupo colaboró con su comunidad y compartió lo aprendido.',
            'estado' => 'publicado', 'destacada' => '1'];

        $this->actingAs($distrital)->post(route('publicaciones.noticias.store'), $datos)->assertRedirect();
        $this->get(route('inicio'))->assertOk()->assertSee('Historia de servicio');

        $dirigente = User::where('email', 'dirigente1@scoutlapaz.bo')->firstOrFail();
        $this->actingAs($dirigente)->post(route('publicaciones.noticias.store'), $datos + ['titulo' => 'No autorizada'])
            ->assertForbidden();
    }

    public function test_los_eventos_operativos_no_se_exponen_en_las_noticias_publicas(): void
    {
        $evento = Evento::where('estado', 'publicado')->firstOrFail();

        $this->get(route('noticias.index'))
            ->assertOk()
            ->assertDontSee($evento->titulo)
            ->assertDontSee('AGENDA SCOUT')
            ->assertDontSee('Ver agenda');

        $distrital = User::where('email', 'distrito@scoutlapaz.bo')->firstOrFail();
        $this->actingAs($distrital)->get(route('eventos.index'))
            ->assertOk()
            ->assertSee($evento->titulo);
    }

    public function test_los_paneles_principales_renderizan_segun_el_rol(): void
    {
        $distrital = User::where('email', 'distrito@scoutlapaz.bo')->firstOrFail();
        $this->actingAs($distrital)->get(route('panel'))->assertOk();
        $this->actingAs($distrital)->get(route('publicaciones.index'))->assertOk();
        $dirigente = User::where('email', 'dirigente1@scoutlapaz.bo')->firstOrFail();
        $this->actingAs($dirigente)->get(route('miembros.index'))->assertOk();
        $miembro = User::where('email', 'miembro1@scoutlapaz.bo')->firstOrFail();
        $this->actingAs($miembro)->get(route('perfil'))->assertOk();
        $this->actingAs($miembro)->get(route('eventos.index'))->assertOk();
    }

    public function test_un_dirigente_puede_cancelar_su_evento_sin_borrarlo(): void
    {
        $dirigente = $this->usuario('dirigente_grupo', GrupoScout::first()->id);
        $evento = $this->eventoPublicado([Rama::first()->id], ['creado_por' => $dirigente->id]);
        $this->actingAs($dirigente)->post(route('eventos.cancelar-evento', $evento))->assertRedirect();
        $this->assertDatabaseHas('eventos', ['id' => $evento->id, 'estado' => 'cancelado',
            'inscripcion_habilitada' => false]);
    }

    public function test_las_respuestas_incluyen_encabezados_de_seguridad(): void
    {
        $this->get(route('inicio'))->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Content-Security-Policy');
    }

    private function registro(array $cambios = []): array
    {
        return array_merge(['tipo_cuenta' => 'miembro', 'grupo_id' => GrupoScout::first()->id,
            'rama_id' => Rama::first()->id, 'fecha_nacimiento' => now()->subYears(9)->toDateString(), 'nombre' => 'Nuevo',
            'apellido' => 'Scout', 'email' => 'nuevo@prueba.test', 'telefono' => '70000000',
            'password' => 'Password1', 'password_confirmation' => 'Password1', 'terminos' => '1'], $cambios);
    }

    private function usuario(string $rol, ?int $grupo = null, string $estado = 'activo'): User
    {
        return User::create(['rol_id' => Rol::where('nombre', $rol)->value('id'), 'grupo_id' => $grupo,
            'nombre' => 'Usuario', 'apellido' => Str::random(6), 'email' => Str::uuid().'@prueba.test',
            'password_hash' => Hash::make('Password1'), 'estado' => $estado]);
    }

    private function usuarioMiembro(Rama $rama): User
    {
        $grupo = GrupoScout::first();
        $miembro = Miembro::create(['grupo_id' => $grupo->id, 'rama_id' => $rama->id, 'nombre' => 'Miembro',
            'apellido' => Str::random(5), 'fecha_nacimiento' => '2010-01-01']);
        $usuario = $this->usuario('miembro', $grupo->id);
        $usuario->update(['miembro_id' => $miembro->id]);

        return $usuario->fresh('miembro.rama', 'rol');
    }

    private function eventoPublicado(array $ramas, array $cambios = []): Evento
    {
        $autor = $this->usuario('dirigente_distrital');
        $evento = Evento::create(array_merge(['creado_por' => $autor->id, 'publicado_por' => $autor->id,
            'titulo' => 'Evento '.Str::random(5), 'tipo' => 'evento', 'descripcion' => 'Descripción',
            'fecha_inicio' => now()->addDays(5), 'fecha_fin' => now()->addDays(5)->addHour(),
            'lugar' => 'La Paz', 'costo' => 0, 'cupo' => 10, 'inscripcion_habilitada' => true,
            'inscripcion_desde' => now()->subDay(), 'inscripcion_hasta' => now()->addDay(), 'estado' => 'publicado'], $cambios));
        $evento->ramas()->sync($ramas);

        return $evento;
    }

    private function datosEvento(array $cambios = []): array
    {
        return array_merge(['titulo' => 'Evento de prueba', 'tipo' => 'evento', 'descripcion' => 'Descripción suficiente',
            'fecha_inicio' => now()->addDays(3)->format('Y-m-d H:i:s'), 'fecha_fin' => now()->addDays(3)->addHours(2)->format('Y-m-d H:i:s'),
            'lugar' => 'La Paz', 'costo' => 0, 'cupo' => 20, 'ramas' => [Rama::first()->id],
            'inscripcion_habilitada' => '1', 'estado' => 'publicado'], $cambios);
    }
}
