<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\ContenidoInstitucional;
use App\Models\Evento;
use App\Models\GrupoScout;
use App\Models\Miembro;
use App\Models\Noticia;
use App\Models\Rama;
use App\Models\Recurso;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\DatabaseNotification;
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
        $this->assertDatabaseHas('miembros', ['email' => 'nuevo@prueba.test', 'grupo_id' => $grupo->id,
            'activo' => false]);
    }

    public function test_una_contrasena_invalida_no_reserva_el_correo_y_se_puede_reintentar(): void
    {
        foreach ([
            ['abc12', 'La contraseña debe tener al menos 6 caracteres.'],
            ['abcdef', 'La contraseña debe incluir al menos un número.'],
            ['123456', 'La contraseña debe incluir al menos una letra.'],
        ] as [$clave, $mensaje]) {
            $this->post(route('registro.enviar'), $this->registro([
                'password' => $clave, 'password_confirmation' => $clave,
            ]))->assertSessionHasErrors(['password' => $mensaje]);
            $this->assertDatabaseMissing('usuarios', ['email' => 'nuevo@prueba.test']);
            $this->assertDatabaseMissing('miembros', ['email' => 'nuevo@prueba.test']);
        }

        $this->post(route('registro.enviar'), $this->registro([
            'password' => 'Clave1', 'password_confirmation' => 'Clave1',
        ]))->assertRedirect(route('login'));
        $this->assertDatabaseHas('usuarios', ['email' => 'nuevo@prueba.test', 'estado' => 'pendiente']);
        $this->post(route('registro.enviar'), $this->registro([
            'password' => 'Clave1', 'password_confirmation' => 'Clave1',
        ]))->assertSessionHasErrors(['email' => 'Este correo ya tiene una cuenta o una solicitud registrada. Si acabas de enviarla, espera su revisión.']);
        $this->assertSame(1, User::where('email', 'nuevo@prueba.test')->count());
    }

    public function test_un_envio_con_contrasenas_distintas_no_crea_una_solicitud(): void
    {
        $this->post(route('registro.enviar'), $this->registro([
            'password' => 'Clave1', 'password_confirmation' => 'Clave2',
        ]))->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('usuarios', ['email' => 'nuevo@prueba.test']);
    }

    public function test_la_solicitud_de_miembro_aparece_al_distrito_y_notifica_a_sus_revisores(): void
    {
        $grupo = GrupoScout::firstOrFail();
        $distrital = User::whereHas('rol', fn ($q) => $q->where('nombre', 'dirigente_distrital'))->firstOrFail();
        $dirigente = User::where('grupo_id', $grupo->id)
            ->whereHas('rol', fn ($q) => $q->where('nombre', 'dirigente_grupo'))->firstOrFail();

        $this->post(route('registro.enviar'), $this->registro(['grupo_id' => $grupo->id]))
            ->assertRedirect(route('login'));
        $this->assertSame(1, $distrital->unreadNotifications()->count());
        $this->assertSame(1, $dirigente->unreadNotifications()->count());
        $this->actingAs($distrital)->get(route('solicitudes.index'))
            ->assertOk()->assertSee('nuevo@prueba.test');
        $this->get(route('panel'))->assertViewHas('datos', fn ($datos) => $datos['solicitudes'] === 1);
        $this->get(route('notificaciones.index'))->assertSee('Nueva solicitud de cuenta');
    }

    public function test_aprobar_solicitud_activa_la_membresia_recien_registrada(): void
    {
        $grupo = GrupoScout::firstOrFail();
        $this->post(route('registro.enviar'), $this->registro(['grupo_id' => $grupo->id]));
        $solicitud = User::where('email', 'nuevo@prueba.test')->firstOrFail();
        $dirigente = $this->usuario('dirigente_grupo', $grupo->id);

        $this->actingAs($dirigente)->post(route('solicitudes.resolver', $solicitud), [
            'decision' => 'aprobar',
        ])->assertSessionHas('estado', 'Solicitud aprobada.');
        $this->assertDatabaseHas('usuarios', ['id' => $solicitud->id, 'estado' => 'activo']);
        $this->assertDatabaseHas('miembros', ['id' => $solicitud->miembro_id, 'activo' => true]);
    }

    public function test_el_distrital_puede_resolver_una_solicitud_de_miembro(): void
    {
        $grupo = GrupoScout::firstOrFail();
        $this->post(route('registro.enviar'), $this->registro(['grupo_id' => $grupo->id]));
        $solicitud = User::where('email', 'nuevo@prueba.test')->firstOrFail();
        $distrital = $this->usuario('dirigente_distrital');

        $this->actingAs($distrital)->post(route('solicitudes.resolver', $solicitud), [
            'decision' => 'aprobar',
        ])->assertSessionHas('estado', 'Solicitud aprobada.');
        $this->assertDatabaseHas('usuarios', ['id' => $solicitud->id, 'estado' => 'activo']);
        $this->assertDatabaseHas('miembros', ['id' => $solicitud->miembro_id, 'activo' => true]);
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

    public function test_solicitudes_muestra_la_cola_del_rol_y_busca_dentro_de_su_ambito(): void
    {
        [$grupoA, $grupoB] = GrupoScout::take(2)->get();
        $dirigente = $this->usuario('dirigente_grupo', $grupoA->id);
        $propia = $this->usuario('miembro', $grupoA->id, 'pendiente');
        $propia->update(['nombre' => 'Amalia', 'apellido' => 'Quispe']);
        $ajena = $this->usuario('miembro', $grupoB->id, 'pendiente');
        $ajena->update(['nombre' => 'Amalia', 'apellido' => 'Fuentes']);
        $dirigentePendiente = $this->usuario('dirigente_grupo', $grupoB->id, 'pendiente');

        $this->actingAs($dirigente)->get(route('solicitudes.index', ['buscar' => 'amalia quispe']))
            ->assertOk()->assertSee('Amalia Quispe')->assertSee('1 solicitud pendiente')
            ->assertDontSee('Amalia Fuentes')->assertDontSee($dirigentePendiente->email);
        $this->actingAs($this->usuario('dirigente_distrital'))->get(route('solicitudes.index'))
            ->assertOk()->assertSee($dirigentePendiente->email)
            ->assertSee($propia->email)->assertSee($ajena->email);
    }

    public function test_rechazo_exige_motivo_y_no_se_puede_resolver_dos_veces(): void
    {
        $grupo = GrupoScout::firstOrFail();
        $dirigente = $this->usuario('dirigente_grupo', $grupo->id);
        $solicitud = $this->usuario('miembro', $grupo->id, 'pendiente');

        $this->actingAs($dirigente)->post(route('solicitudes.resolver', $solicitud), [
            'decision' => 'rechazar', 'motivo' => '',
        ])->assertSessionHasErrors('motivo');
        $this->assertSame('pendiente', $solicitud->fresh()->estado);

        $this->actingAs($dirigente)->post(route('solicitudes.resolver', $solicitud), [
            'decision' => 'rechazar', 'motivo' => 'Falta completar los datos del grupo.',
        ])->assertSessionHas('estado', 'Solicitud rechazada.');
        $this->assertDatabaseHas('usuarios', ['id' => $solicitud->id, 'estado' => 'rechazado',
            'motivo_rechazo' => 'Falta completar los datos del grupo.']);
        $this->actingAs($dirigente)->post(route('solicitudes.resolver', $solicitud), [
            'decision' => 'aprobar',
        ])->assertSessionHasErrors('solicitud');
    }

    public function test_directorio_filtra_sin_mostrar_miembros_de_otro_grupo_al_dirigente(): void
    {
        [$grupoA, $grupoB] = GrupoScout::take(2)->get();
        [$ramaA, $ramaB] = Rama::take(2)->get();
        $dirigente = $this->usuario('dirigente_grupo', $grupoA->id);
        $propio = $this->usuarioMiembro($ramaA, $grupoA);
        $propio->miembro->update(['nombre' => 'Andrea', 'apellido' => 'Quispe']);
        $ajeno = $this->usuarioMiembro($ramaB, $grupoB);
        $ajeno->miembro->update(['nombre' => 'Bruno', 'apellido' => 'Flores']);

        $this->actingAs($dirigente)->get(route('miembros.index', ['buscar' => 'andrea quispe']))
            ->assertOk()->assertSee('Andrea Quispe')->assertDontSee('Bruno Flores');
        $this->actingAs($dirigente)->get(route('miembros.index', ['grupo' => $grupoB->id]))
            ->assertOk()->assertSee('Andrea Quispe')->assertDontSee('Bruno Flores');
        $this->actingAs($this->usuario('dirigente_distrital'))->get(route('miembros.index', [
            'grupo' => $grupoB->id, 'rama' => $ramaB->id, 'estado' => 'activo',
        ]))->assertOk()->assertSee('Bruno Flores')->assertDontSee('Andrea Quispe');
    }

    public function test_el_distrital_ve_dirigentes_y_puede_crear_una_cuenta_de_grupo(): void
    {
        $distrital = $this->usuario('dirigente_distrital');
        $grupo = GrupoScout::firstOrFail();
        $existente = User::whereHas('rol', fn ($q) => $q->where('nombre', 'dirigente_grupo'))
            ->where('grupo_id', $grupo->id)->firstOrFail();

        $this->actingAs($distrital)->get(route('dirigentes.index'))
            ->assertOk()->assertSee($existente->email)->assertSee($grupo->nombre)->assertSee('Crear dirigente');
        $this->get(route('dirigentes.index', ['grupo' => $grupo->id, 'buscar' => $existente->email]))
            ->assertOk()->assertSee($existente->email);
        $this->get(route('dirigentes.create'))->assertOk()->assertSee('Contraseña inicial');

        $datos = ['nombre' => 'Ana', 'apellido' => 'Rojas', 'email' => ' ANA@PRUEBA.TEST ',
            'grupo_id' => $grupo->id, 'password' => 'Clave12', 'password_confirmation' => 'Clave12'];
        $this->post(route('dirigentes.store'), $datos)->assertRedirect(route('dirigentes.index'));
        $creado = User::where('email', 'ana@prueba.test')->firstOrFail();
        $this->assertTrue($creado->es('dirigente_grupo'));
        $this->assertSame($grupo->id, $creado->grupo_id);
        $this->assertSame('activo', $creado->estado);
        $this->assertTrue(Hash::check('Clave12', $creado->password_hash));
        $this->assertDatabaseHas('auditoria', ['accion' => 'crear_dirigente', 'entidad_id' => (string) $creado->id]);

        $this->post(route('dirigentes.store'), $datos)->assertSessionHasErrors('email');
        $this->assertSame(1, User::where('email', 'ana@prueba.test')->count());
    }

    public function test_solo_el_distrital_edita_y_controla_el_acceso_de_dirigentes_de_grupo(): void
    {
        $grupoA = GrupoScout::firstOrFail();
        $grupoB = GrupoScout::where('id', '!=', $grupoA->id)->firstOrFail();
        $dirigente = $this->usuario('dirigente_grupo', $grupoA->id);
        $otro = $this->usuario('dirigente_grupo', $grupoA->id);
        $miembro = $this->usuario('miembro', $grupoA->id);
        $datos = ['nombre' => 'Nueva', 'apellido' => 'Dirigente', 'email' => 'nueva@prueba.test',
            'grupo_id' => $grupoB->id];

        $this->actingAs($otro)->get(route('dirigentes.index'))->assertForbidden();
        $this->get(route('dirigentes.create'))->assertForbidden();
        $this->post(route('dirigentes.store'), $datos + ['password' => 'Clave12',
            'password_confirmation' => 'Clave12'])->assertForbidden();
        $this->put(route('dirigentes.update', $dirigente), $datos)->assertForbidden();
        $this->post(route('dirigentes.estado', $dirigente), ['estado' => 'suspendido'])->assertForbidden();
        $this->actingAs($miembro)->get(route('dirigentes.index'))->assertForbidden();

        $distrital = $this->usuario('dirigente_distrital');
        $this->actingAs($distrital)->get(route('dirigentes.edit', $dirigente))->assertOk();
        $this->put(route('dirigentes.update', $dirigente), $datos)->assertRedirect(route('dirigentes.index'));
        $dirigente->refresh();
        $this->assertSame($grupoB->id, $dirigente->grupo_id);
        $this->assertSame('nueva@prueba.test', $dirigente->email);
        $this->assertSame(2, $dirigente->version_acceso);
        $this->post(route('dirigentes.estado', $dirigente), ['estado' => 'suspendido'])->assertRedirect();
        $this->assertSame('suspendido', $dirigente->fresh()->estado);
        $this->post(route('dirigentes.estado', $dirigente), ['estado' => 'activo'])->assertRedirect();
        $this->assertSame('activo', $dirigente->fresh()->estado);
        $this->post(route('dirigentes.estado', $miembro), ['estado' => 'suspendido'])->assertForbidden();
    }

    public function test_el_dirigente_puede_cambiar_la_contrasena_inicial_desde_mi_cuenta(): void
    {
        $dirigente = $this->usuario('dirigente_grupo', GrupoScout::firstOrFail()->id);
        $this->actingAs($dirigente)->get(route('cuenta'))->assertOk()
            ->assertSee('Cambiar contraseña')->assertSee('Contraseña actual');
        $this->post(route('password.cambiar'), ['actual' => 'Password1', 'password' => 'Nueva12',
            'password_confirmation' => 'Nueva12'])->assertRedirect();
        $this->assertTrue(Hash::check('Nueva12', $dirigente->fresh()->password_hash));
        $this->flushSession();
        $this->actingAs($this->usuario('miembro', GrupoScout::first()->id))
            ->get(route('cuenta'))->assertForbidden();
    }

    public function test_suspender_y_reactivar_miembro_actualiza_cuenta_y_membresia(): void
    {
        [$grupoA, $grupoB] = GrupoScout::take(2)->get();
        $dirigente = $this->usuario('dirigente_grupo', $grupoA->id);
        $ajeno = $this->usuario('dirigente_grupo', $grupoB->id);
        $miembro = $this->usuarioMiembro(Rama::firstOrFail(), $grupoA);
        $version = $miembro->version_acceso;

        $this->actingAs($ajeno)->post(route('usuarios.estado', $miembro), ['estado' => 'suspendido'])
            ->assertForbidden();
        $this->actingAs($dirigente)->post(route('usuarios.estado', $miembro), ['estado' => 'suspendido'])
            ->assertSessionHas('estado', 'Cuenta suspendida.');
        $this->assertDatabaseHas('usuarios', ['id' => $miembro->id, 'estado' => 'suspendido',
            'version_acceso' => $version + 1]);
        $this->assertDatabaseHas('miembros', ['id' => $miembro->miembro_id, 'activo' => false]);
        $this->actingAs($dirigente)->post(route('usuarios.estado', $miembro), ['estado' => 'suspendido'])
            ->assertSessionHasErrors('estado');
        $this->actingAs($dirigente)->post(route('usuarios.estado', $miembro), ['estado' => 'activo'])
            ->assertSessionHas('estado', 'Cuenta activada.');
        $this->assertDatabaseHas('miembros', ['id' => $miembro->miembro_id, 'activo' => true]);

        $miembro->update(['estado' => 'pendiente']);
        $this->actingAs($dirigente)->post(route('usuarios.estado', $miembro), ['estado' => 'activo'])
            ->assertForbidden();
        $this->actingAs($dirigente)->get(route('miembros.index', ['estado' => 'pendiente']))
            ->assertOk()->assertSee('Revisa esta cuenta en Solicitudes.')
            ->assertDontSee('Reactivar cuenta');
        $this->actingAs($this->usuario('dirigente_distrital'))
            ->post(route('usuarios.estado', $ajeno), ['estado' => 'suspendido'])->assertForbidden();
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

    public function test_inscripcion_repetida_con_cupo_lleno_conserva_la_confirmacion_original(): void
    {
        $rama = Rama::firstOrFail();
        $usuario = $this->usuarioMiembro($rama);
        $evento = $this->eventoPublicado([$rama->id], ['cupo' => 1]);

        $this->actingAs($usuario)->post(route('eventos.inscribir', $evento))->assertSessionHasNoErrors();
        $fechaOriginal = $evento->inscripciones()->firstOrFail()->inscrito_en;
        $this->actingAs($usuario)->post(route('eventos.inscribir', $evento))->assertSessionHasNoErrors();

        $this->assertSame(1, $evento->inscripciones()->where('estado', 'inscrito')->count());
        $this->assertTrue($fechaOriginal->equalTo($evento->inscripciones()->firstOrFail()->inscrito_en));
    }

    public function test_membresia_inactiva_no_puede_inscribirse(): void
    {
        $rama = Rama::firstOrFail();
        $usuario = $this->usuarioMiembro($rama);
        $usuario->miembro->update(['activo' => false]);
        $evento = $this->eventoPublicado([$rama->id]);

        $this->actingAs($usuario)->post(route('eventos.inscribir', $evento))->assertForbidden();
        $this->assertDatabaseMissing('inscripciones_evento', ['evento_id' => $evento->id,
            'miembro_id' => $usuario->miembro_id]);
    }

    public function test_cancelar_inscripcion_libera_el_cupo_y_permite_reinscripcion_si_hay_plaza(): void
    {
        $rama = Rama::firstOrFail();
        $primero = $this->usuarioMiembro($rama);
        $segundo = $this->usuarioMiembro($rama);
        $evento = $this->eventoPublicado([$rama->id], ['cupo' => 1]);

        $this->actingAs($primero)->post(route('eventos.inscribir', $evento))->assertSessionHasNoErrors();
        $primero->miembro->update(['activo' => false]);
        $this->actingAs($primero)->delete(route('eventos.cancelar', $evento))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('inscripciones_evento', ['evento_id' => $evento->id,
            'miembro_id' => $primero->miembro_id, 'estado' => 'cancelado']);
        $this->actingAs($segundo)->post(route('eventos.inscribir', $evento))->assertSessionHasNoErrors();
        $this->assertSame(1, $evento->inscripciones()->where('estado', 'inscrito')->count());
    }

    public function test_publicacion_y_edicion_respetan_los_inscritos(): void
    {
        $rama = Rama::firstOrFail();
        $otraRama = Rama::whereKeyNot($rama->id)->firstOrFail();
        $distrital = $this->usuario('dirigente_distrital');
        $datos = $this->datosEvento(['estado' => 'borrador']);
        $this->actingAs($distrital)->post(route('eventos.store'), $datos)->assertSessionHasNoErrors();
        $evento = Evento::latest('id')->firstOrFail();
        $this->assertNull($evento->publicado_por);

        $this->actingAs($distrital)->put(route('eventos.update', $evento), array_merge($datos, ['estado' => 'publicado']))
            ->assertSessionHasNoErrors();
        $this->assertSame($distrital->id, $evento->fresh()->publicado_por);
        $miembro = $this->usuarioMiembro($rama);
        $this->actingAs($miembro)->post(route('eventos.inscribir', $evento))->assertSessionHasNoErrors();
        $otroMiembro = $this->usuarioMiembro($rama);
        $this->actingAs($otroMiembro)->post(route('eventos.inscribir', $evento))->assertSessionHasNoErrors();

        $this->actingAs($distrital)->put(route('eventos.update', $evento), $datos)
            ->assertSessionHasErrors('estado');
        $this->actingAs($distrital)->put(route('eventos.update', $evento), $this->datosEvento(['cupo' => 1]))
            ->assertSessionHasErrors('cupo');
        $this->actingAs($distrital)->put(route('eventos.update', $evento), $this->datosEvento([
            'ramas' => [$otraRama->id],
        ]))->assertSessionHasErrors('ramas');
        $this->assertSame('publicado', $evento->fresh()->estado);
        $this->assertTrue($evento->fresh()->ramas->contains('id', $rama->id));
    }

    public function test_dirigente_puede_guardar_borrador_sin_enviar_estado(): void
    {
        $distrital = $this->usuario('dirigente_distrital');
        $datos = $this->datosEvento();
        unset($datos['estado']);

        $this->actingAs($distrital)->post(route('eventos.store'), $datos)->assertSessionHasNoErrors();
        $this->assertSame('borrador', Evento::latest('id')->firstOrFail()->estado);
    }

    public function test_propuesta_rover_solo_se_publica_mediante_revision(): void
    {
        $rover = $this->usuarioMiembro(Rama::where('slug', 'rovers')->firstOrFail());
        $grupo = $this->usuario('dirigente_grupo', $rover->grupo_id);
        $datos = $this->datosEvento(['tipo' => 'proyecto_rover', 'ramas' => [$rover->miembro->rama_id]]);
        $this->actingAs($rover)->post(route('eventos.store'), $datos)->assertSessionHasNoErrors();
        $evento = Evento::latest('id')->firstOrFail();

        $this->actingAs($grupo)->get(route('eventos.edit', $evento))->assertForbidden();
        $this->actingAs($grupo)->post(route('eventos.cancelar-evento', $evento))->assertForbidden();
        $this->actingAs($grupo)->post(route('eventos.aprobar', $evento))->assertSessionHasNoErrors();
        $this->assertSame('publicado', $evento->fresh()->estado);
        $this->actingAs($rover)->post(route('eventos.inscribir', $evento))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('inscripciones_evento', ['evento_id' => $evento->id,
            'miembro_id' => $rover->miembro_id, 'estado' => 'inscrito']);
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

    public function test_evento_sin_ventana_explicita_acepta_inscripciones_hasta_su_inicio(): void
    {
        $rama = Rama::first();
        $usuario = $this->usuarioMiembro($rama);
        $evento = $this->eventoPublicado([$rama->id], ['inscripcion_desde' => null, 'inscripcion_hasta' => null]);

        $this->actingAs($usuario)->get(route('eventos.show', $evento))
            ->assertOk()
            ->assertSee('Inscripción abierta')
            ->assertSee('Confirmar inscripción');
        $this->actingAs($usuario)->post(route('eventos.inscribir', $evento))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('inscripciones_evento', ['evento_id' => $evento->id,
            'miembro_id' => $usuario->miembro_id, 'estado' => 'inscrito']);
    }

    public function test_un_evento_publicado_explica_por_que_no_admite_inscripcion(): void
    {
        $rama = Rama::first();
        $usuario = $this->usuarioMiembro($rama);
        $evento = $this->eventoPublicado([$rama->id], ['inscripcion_desde' => now()->addDay(),
            'inscripcion_hasta' => now()->addDays(2)]);

        $this->actingAs($usuario)->get(route('eventos.show', $evento))
            ->assertOk()
            ->assertSee('Las inscripciones abren el')
            ->assertDontSee('Confirmar inscripción');
    }

    public function test_no_se_puede_inscribir_despues_de_iniciado_el_evento(): void
    {
        $rama = Rama::first();
        $usuario = $this->usuarioMiembro($rama);
        $evento = $this->eventoPublicado([$rama->id], ['fecha_inicio' => now()->subHour(),
            'fecha_fin' => now()->addHours(2), 'inscripcion_hasta' => null]);

        $this->actingAs($usuario)->post(route('eventos.inscribir', $evento))
            ->assertSessionHasErrors('evento');
        $this->assertDatabaseMissing('inscripciones_evento', ['evento_id' => $evento->id,
            'miembro_id' => $usuario->miembro_id]);
    }

    public function test_no_se_puede_crear_un_evento_con_inicio_pasado(): void
    {
        $distrital = $this->usuario('dirigente_distrital');
        $this->actingAs($distrital)->post(route('eventos.store'), $this->datosEvento([
            'fecha_inicio' => now()->subHours(2)->format('Y-m-d H:i:s'),
            'fecha_fin' => now()->subHour()->format('Y-m-d H:i:s'),
        ]))->assertSessionHasErrors('fecha_inicio');
    }

    public function test_evento_terminado_se_puede_corregir_y_archivar_sin_perder_inscripciones(): void
    {
        $rama = Rama::firstOrFail();
        $distrital = $this->usuario('dirigente_distrital');
        $miembro = $this->usuarioMiembro($rama);
        $evento = $this->eventoPublicado([$rama->id], ['titulo' => 'Actividad pasada',
            'fecha_inicio' => now()->subDays(2), 'fecha_fin' => now()->subDay()]);
        \DB::table('inscripciones_evento')->insert(['evento_id' => $evento->id,
            'miembro_id' => $miembro->miembro_id, 'estado' => 'inscrito', 'inscrito_en' => now()->subDays(3),
            'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($miembro)->get(route('eventos.index'))->assertDontSee('Actividad pasada');
        $this->get(route('eventos.index', ['vigencia' => 'finalizados']))->assertSee('Actividad pasada');
        $this->actingAs($distrital)->get(route('eventos.edit', $evento))->assertOk();
        $this->put(route('eventos.update', $evento), $this->datosEvento([
            'titulo' => 'Actividad corregida', 'fecha_inicio' => $evento->fecha_inicio->format('Y-m-d H:i:s'),
            'fecha_fin' => $evento->fecha_fin->format('Y-m-d H:i:s'),
        ]))->assertSessionHasNoErrors();
        $this->assertSame('Actividad corregida', $evento->fresh()->titulo);

        $this->post(route('eventos.archivar', $evento))->assertRedirect();
        $this->assertSame('finalizado', $evento->fresh()->estado);
        $this->assertDatabaseHas('inscripciones_evento', ['evento_id' => $evento->id,
            'miembro_id' => $miembro->miembro_id, 'estado' => 'inscrito']);
        $this->actingAs($miembro)->get(route('eventos.index'))->assertDontSee('Actividad corregida');
        $this->get(route('eventos.index', ['vigencia' => 'finalizados']))->assertSee('Actividad corregida');
        $this->get(route('eventos.show', $evento))->assertOk();
        $this->actingAs($distrital)->post(route('eventos.restaurar-archivo', $evento))->assertRedirect();
        $this->assertSame('publicado', $evento->fresh()->estado);
        $this->assertFalse($evento->fresh()->inscripcion_habilitada);
    }

    public function test_solo_el_distrital_archiva_actividades_ya_finalizadas(): void
    {
        $rama = Rama::firstOrFail();
        $grupo = GrupoScout::firstOrFail();
        $dirigente = $this->usuario('dirigente_grupo', $grupo->id);
        $evento = $this->eventoPublicado([$rama->id], ['creado_por' => $dirigente->id,
            'fecha_inicio' => now()->subDays(2), 'fecha_fin' => now()->subDay()]);
        $this->actingAs($dirigente)->get(route('eventos.edit', $evento))->assertForbidden();
        $this->post(route('eventos.archivar', $evento))->assertForbidden();
        $distrital = $this->usuario('dirigente_distrital');
        $proximo = $this->eventoPublicado([$rama->id]);
        $this->actingAs($distrital)->post(route('eventos.archivar', $proximo))->assertForbidden();
    }

    public function test_evento_admite_imagen_y_valida_el_cierre_de_inscripciones(): void
    {
        Storage::fake('public');
        $distrital = $this->usuario('dirigente_distrital');
        $inicio = now()->addDays(3);
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');
        $datos = $this->datosEvento(['fecha_inicio' => $inicio->format('Y-m-d H:i:s'),
            'fecha_fin' => $inicio->copy()->addHours(2)->format('Y-m-d H:i:s'),
            'inscripcion_hasta' => $inicio->copy()->addHour()->format('Y-m-d H:i:s'),
            'imagen' => UploadedFile::fake()->createWithContent('campamento.png', $png)]);

        $this->actingAs($distrital)->post(route('eventos.store'), $datos)
            ->assertSessionHasErrors('inscripcion_hasta');

        $datos['inscripcion_hasta'] = $inicio->copy()->subHour()->format('Y-m-d H:i:s');
        $datos['imagen'] = UploadedFile::fake()->createWithContent('campamento.png', $png);
        $this->actingAs($distrital)->post(route('eventos.store'), $datos)->assertSessionHasNoErrors();
        $evento = Evento::latest('id')->firstOrFail();
        $this->assertStringStartsWith('storage/eventos/', $evento->imagen_url);
        Storage::disk('public')->assertExists(Str::after($evento->imagen_url, 'storage/'));
    }

    public function test_el_distrital_ve_detalles_e_inscritos_del_evento(): void
    {
        $rama = Rama::first();
        $miembro = $this->usuarioMiembro($rama);
        $evento = $this->eventoPublicado([$rama->id], ['que_llevar' => 'Agua, gorra y pañoleta.',
            'indicaciones' => 'Presentarse quince minutos antes.']);
        $this->actingAs($miembro)->post(route('eventos.inscribir', $evento));

        $distrital = User::where('email', 'distrito@scoutlapaz.bo')->firstOrFail();
        $this->actingAs($distrital)->get(route('eventos.show', $evento))
            ->assertOk()
            ->assertSee('Miembros inscritos')
            ->assertSee('Agua, gorra y pañoleta.')
            ->assertSee($miembro->miembro->nombre);
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

    public function test_biblioteca_permite_publicar_a_ambos_dirigentes_pero_no_a_miembros(): void
    {
        Storage::fake('local');
        $categoria = \DB::table('categorias_recursos')->value('id');
        $dirigente = $this->usuario('dirigente_grupo', GrupoScout::first()->id);
        $this->actingAs($dirigente)->get(route('biblioteca.index'))->assertOk()->assertSee('Publicar un recurso');
        $this->actingAs($dirigente)->post(route('biblioteca.store'), ['categoria_recurso_id' => $categoria,
            'titulo' => 'Guía del grupo', 'archivo' => UploadedFile::fake()->create('grupo.pdf', 100, 'application/pdf')])
            ->assertRedirect();
        $this->assertDatabaseHas('recursos', ['titulo' => 'Guía del grupo', 'publicado_por' => $dirigente->id]);
        $distrital = $this->usuario('dirigente_distrital');
        $this->actingAs($distrital)->post(route('biblioteca.store'), ['categoria_recurso_id' => $categoria,
            'titulo' => 'Guía distrital', 'archivo' => UploadedFile::fake()->create('guia.pdf', 100, 'application/pdf')])
            ->assertRedirect();
        $miembro = $this->usuario('miembro', GrupoScout::first()->id);
        $this->actingAs($miembro)->get(route('biblioteca.index'))->assertOk()->assertDontSee('Publicar un recurso');
        $this->post(route('biblioteca.store'), ['categoria_recurso_id' => $categoria,
            'titulo' => 'Guía ajena', 'archivo' => UploadedFile::fake()->create('ajena.pdf', 100, 'application/pdf')])
            ->assertForbidden();
    }

    public function test_dirigente_de_grupo_solo_gestiona_sus_recursos_y_distrital_gestiona_todos(): void
    {
        Storage::fake('local');
        $categoria = \DB::table('categorias_recursos')->value('id');
        $grupo = GrupoScout::firstOrFail();
        $dirigenteA = $this->usuario('dirigente_grupo', $grupo->id);
        $dirigenteB = $this->usuario('dirigente_grupo', $grupo->id);
        $distrital = $this->usuario('dirigente_distrital');
        foreach ([[$dirigenteA, 'Manual propio'], [$dirigenteB, 'Manual de otro']] as [$autor, $titulo]) {
            $this->actingAs($autor)->post(route('biblioteca.store'), [
                'categoria_recurso_id' => $categoria, 'titulo' => $titulo,
                'archivo' => UploadedFile::fake()->create('manual.pdf', 100, 'application/pdf'),
            ])->assertSessionHasNoErrors();
        }
        $propio = Recurso::where('publicado_por', $dirigenteA->id)->firstOrFail();
        $ajeno = Recurso::where('publicado_por', $dirigenteB->id)->firstOrFail();

        $this->actingAs($dirigenteA)->get(route('biblioteca.show', $propio))
            ->assertOk()->assertSee('Editar información y portada');
        $this->get(route('biblioteca.show', $ajeno))->assertOk()->assertDontSee('Editar información y portada');
        $this->put(route('biblioteca.update', $propio), [
            'categoria_recurso_id' => $categoria, 'titulo' => 'Manual actualizado',
        ])->assertSessionHasNoErrors();
        $this->put(route('biblioteca.update', $ajeno), [
            'categoria_recurso_id' => $categoria, 'titulo' => 'Cambio indebido',
        ])->assertForbidden();
        $this->delete(route('biblioteca.destroy', $ajeno))->assertForbidden();

        $this->actingAs($dirigenteB)->delete(route('biblioteca.destroy', $ajeno))->assertRedirect();
        $this->actingAs($dirigenteA)->delete(route('biblioteca.destroy', $propio))->assertRedirect();
        $this->get(route('biblioteca.index', ['estado' => 'archivado']))
            ->assertOk()->assertSee('Manual actualizado')->assertDontSee('Manual de otro');
        $this->get(route('biblioteca.show', $ajeno))->assertForbidden();
        $this->post(route('biblioteca.restaurar', $ajeno))->assertForbidden();
        $this->post(route('biblioteca.restaurar', $propio))->assertRedirect(route('biblioteca.show', $propio));

        $this->actingAs($distrital)->get(route('biblioteca.index', ['estado' => 'archivado']))
            ->assertOk()->assertSee('Manual de otro');
        $this->put(route('biblioteca.update', $ajeno), [
            'categoria_recurso_id' => $categoria, 'titulo' => 'Edición distrital',
        ])->assertSessionHasErrors('recurso');
        $this->post(route('biblioteca.restaurar', $ajeno))->assertRedirect(route('biblioteca.show', $ajeno));
        $this->put(route('biblioteca.update', $ajeno), [
            'categoria_recurso_id' => $categoria, 'titulo' => 'Edición distrital',
        ])->assertSessionHasNoErrors();
    }

    public function test_miembro_explora_previsualiza_y_descarga_un_recurso_con_portada(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $distrital = $this->usuario('dirigente_distrital');
        $miembro = $this->usuario('miembro', GrupoScout::first()->id);
        $categoria = \DB::table('categorias_recursos')->value('id');
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');

        $this->actingAs($distrital)->post(route('biblioteca.store'), [
            'categoria_recurso_id' => $categoria,
            'titulo' => 'Manual de campamento',
            'descripcion' => 'Técnicas para salir al campo.',
            'archivo' => UploadedFile::fake()->create('manual.pdf', 100, 'application/pdf'),
            'imagen' => UploadedFile::fake()->createWithContent('portada.png', $png),
        ])->assertSessionHasNoErrors();
        $recurso = Recurso::latest('id')->firstOrFail();
        $this->assertNotNull($recurso->imagen_path);
        Storage::disk('public')->assertExists(Str::after($recurso->imagen_path, 'storage/'));

        $this->actingAs($miembro)->get(route('biblioteca.index', ['buscar' => 'campamento']))
            ->assertOk()->assertSee('Manual de campamento')->assertSee($recurso->imagen_path);
        $this->get(route('biblioteca.show', $recurso))->assertOk()
            ->assertSee('Técnicas para salir al campo.')->assertSee(route('biblioteca.preview', $recurso));
        $this->get(route('biblioteca.preview', $recurso))->assertOk()
            ->assertHeader('content-type', 'application/pdf');
        $this->get(route('biblioteca.download', $recurso))->assertOk()
            ->assertDownload('manual.pdf');
        $this->actingAs($miembro)->put(route('biblioteca.update', $recurso), [
            'categoria_recurso_id' => $categoria, 'titulo' => 'Cambio ajeno',
        ])->assertForbidden();
    }

    public function test_recurso_archivado_deja_de_ser_visible_y_se_puede_restaurar(): void
    {
        Storage::fake('local');
        $distrital = $this->usuario('dirigente_distrital');
        $miembro = $this->usuario('miembro', GrupoScout::first()->id);
        $categoria = \DB::table('categorias_recursos')->value('id');
        $this->actingAs($distrital)->post(route('biblioteca.store'), [
            'categoria_recurso_id' => $categoria, 'titulo' => 'Guía de seguridad',
            'archivo' => UploadedFile::fake()->create('seguridad.pdf', 100, 'application/pdf'),
        ])->assertSessionHasNoErrors();
        $recurso = Recurso::latest('id')->firstOrFail();

        $this->delete(route('biblioteca.destroy', $recurso))
            ->assertRedirect(route('biblioteca.index', ['estado' => 'archivado']));
        $this->actingAs($miembro)->get(route('biblioteca.index'))->assertDontSee('Guía de seguridad');
        $this->get(route('biblioteca.show', $recurso))->assertForbidden();
        $this->get(route('biblioteca.preview', $recurso))->assertForbidden();
        $this->get(route('biblioteca.download', $recurso))->assertForbidden();
        $this->actingAs($distrital)->get(route('biblioteca.index', ['estado' => 'archivado']))
            ->assertOk()->assertSee('Guía de seguridad');
        $this->post(route('biblioteca.restaurar', $recurso))->assertRedirect(route('biblioteca.show', $recurso));
        $this->actingAs($miembro)->get(route('biblioteca.show', $recurso))->assertOk();
    }

    public function test_distrital_puede_cambiar_y_quitar_portada_de_un_recurso(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $distrital = $this->usuario('dirigente_distrital');
        $categoria = \DB::table('categorias_recursos')->value('id');
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');
        $this->actingAs($distrital)->post(route('biblioteca.store'), [
            'categoria_recurso_id' => $categoria, 'titulo' => 'Manual original',
            'archivo' => UploadedFile::fake()->create('manual.pdf', 100, 'application/pdf'),
            'imagen' => UploadedFile::fake()->createWithContent('primera.png', $png),
        ])->assertSessionHasNoErrors();
        $recurso = Recurso::latest('id')->firstOrFail();
        $anterior = Str::after($recurso->imagen_path, 'storage/');

        $this->put(route('biblioteca.update', $recurso), [
            'categoria_recurso_id' => $categoria, 'titulo' => 'Manual corregido',
            'imagen' => UploadedFile::fake()->createWithContent('segunda.png', $png),
        ])->assertSessionHasNoErrors();
        $nueva = Str::after($recurso->fresh()->imagen_path, 'storage/');
        $this->assertNotSame($anterior, $nueva);
        Storage::disk('public')->assertMissing($anterior);
        Storage::disk('public')->assertExists($nueva);

        $this->put(route('biblioteca.update', $recurso), [
            'categoria_recurso_id' => $categoria, 'titulo' => 'Manual corregido', 'quitar_imagen' => '1',
        ])->assertSessionHasNoErrors();
        $this->assertNull($recurso->fresh()->imagen_path);
        Storage::disk('public')->assertMissing($nueva);
    }

    public function test_notificaciones_se_filtran_y_solo_se_pueden_marcar_las_propias(): void
    {
        $usuario = $this->usuario('miembro', GrupoScout::first()->id);
        $otro = $this->usuario('miembro', GrupoScout::first()->id);
        $pendiente = $this->aviso($usuario, 'Aviso pendiente', route('panel'));
        $leida = $this->aviso($usuario, 'Aviso anterior', null, true);
        $ajena = $this->aviso($otro, 'Aviso ajeno');

        $this->actingAs($usuario)->get(route('notificaciones.index', ['estado' => 'sin_leer']))
            ->assertOk()->assertSee('Aviso pendiente')->assertDontSee('Aviso anterior')
            ->assertViewHas('sinLeer', 1)->assertViewHas('total', 2);
        $this->get(route('notificaciones.index', ['estado' => 'leidas']))
            ->assertOk()->assertSee('Aviso anterior')->assertDontSee('Aviso pendiente');
        $this->post(route('notificaciones.leer', $ajena->id))->assertNotFound();
        $this->post(route('notificaciones.abrir', $ajena->id))->assertNotFound();
        $this->post(route('notificaciones.leer', $pendiente->id))->assertRedirect();
        $this->assertNotNull($pendiente->fresh()->read_at);
        $this->assertNotNull($leida->fresh()->read_at);
        $this->assertNull($ajena->fresh()->read_at);
    }

    public function test_abrir_una_notificacion_marca_lectura_y_rechaza_destinos_externos(): void
    {
        $usuario = $this->usuario('miembro', GrupoScout::first()->id);
        $interna = $this->aviso($usuario, 'Aviso interno', route('panel'));
        $externa = $this->aviso($usuario, 'Aviso externo', 'https://ejemplo.test/otro');

        $this->actingAs($usuario)->post(route('notificaciones.abrir', $interna->id))
            ->assertRedirect(route('panel'));
        $this->assertNotNull($interna->fresh()->read_at);
        $this->post(route('notificaciones.abrir', $externa->id))
            ->assertRedirect(route('notificaciones.index'));
        $this->assertNotNull($externa->fresh()->read_at);
    }

    public function test_marcar_todas_como_leidas_no_afecta_a_otros_usuarios(): void
    {
        $usuario = $this->usuario('miembro', GrupoScout::first()->id);
        $otro = $this->usuario('miembro', GrupoScout::first()->id);
        $primera = $this->aviso($usuario, 'Primera');
        $segunda = $this->aviso($usuario, 'Segunda');
        $ajena = $this->aviso($otro, 'Ajena');

        $this->actingAs($usuario)->post(route('notificaciones.leer-todas'))
            ->assertRedirect(route('notificaciones.index', ['estado' => 'leidas']))
            ->assertSessionHas('estado', '2 notificaciones marcadas como leídas.');
        $this->assertNotNull($primera->fresh()->read_at);
        $this->assertNotNull($segunda->fresh()->read_at);
        $this->assertNull($ajena->fresh()->read_at);
    }

    public function test_rutas_publicas_y_salud_responden(): void
    {
        $this->get(route('inicio'))->assertOk()
            ->assertSee('js/tema.js')
            ->assertSee('img/scout-salar.jpg', false)
            ->assertSee('img/Lobatos.png', false)
            ->assertSee('img/Exploradores.png', false)
            ->assertSee('img/Pioneros.png', false)
            ->assertSee('img/Rovers.png', false)
            ->assertSee('data-tema-toggle', false);
        $this->get(route('ramas'))->assertOk();
        $this->get(route('nosotros'))->assertOk()
            ->assertSee('img/scout antiguo bol.jpeg', false);
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

    public function test_publicacion_pasa_de_borrador_a_portada_y_puede_archivarse_y_restaurarse(): void
    {
        $distrital = $this->usuario('dirigente_distrital');
        $datos = ['titulo' => 'Jornada de servicio', 'resumen' => 'La comunidad participó.',
            'contenido' => 'Los grupos realizaron una jornada de servicio comunitario.',
            'estado' => 'borrador', 'destacada' => '1'];

        $this->actingAs($distrital)->post(route('publicaciones.noticias.store'), $datos)
            ->assertSessionHas('estado', 'Borrador guardado.');
        $noticia = Noticia::latest('id')->firstOrFail();
        $this->assertFalse($noticia->destacada);
        $this->assertNull($noticia->publicado_en);
        $this->get(route('inicio'))->assertDontSee($noticia->titulo);

        $this->actingAs($distrital)->put(route('publicaciones.noticias.update', $noticia), [
            ...$datos, 'estado' => 'publicado',
        ])->assertSessionHasNoErrors();
        $this->assertTrue($noticia->fresh()->destacada);
        $this->assertNotNull($noticia->fresh()->publicado_en);
        $this->get(route('inicio'))->assertSee($noticia->titulo);

        $this->actingAs($distrital)->delete(route('publicaciones.noticias.destroy', $noticia))
            ->assertSessionHas('estado', 'Noticia archivada sin eliminar su historial.');
        $this->get(route('inicio'))->assertDontSee($noticia->titulo);
        $this->actingAs($distrital)->put(route('publicaciones.noticias.update', $noticia), $datos)
            ->assertSessionHasErrors('noticia');
        $this->actingAs($distrital)->post(route('publicaciones.noticias.restaurar', $noticia))
            ->assertRedirect(route('publicaciones.index', ['estado' => 'borrador']))
            ->assertSessionHas('estado', 'Publicación restaurada como borrador.');
        $this->assertDatabaseHas('noticias', ['id' => $noticia->id, 'estado' => 'borrador',
            'destacada' => false, 'publicado_en' => null]);
    }

    public function test_filtros_de_publicaciones_cuentan_los_estados_dentro_de_la_busqueda(): void
    {
        $distrital = $this->usuario('dirigente_distrital');
        $categoria = Categoria::firstOrFail();
        foreach (['publicado', 'borrador'] as $estado) {
            Noticia::create([
                'autor_id' => $distrital->id,
                'categoria_id' => $categoria->id,
                'titulo' => 'Campamento especial '.$estado,
                'slug' => 'campamento-especial-'.$estado,
                'resumen' => 'Crónica del campamento especial.',
                'contenido' => 'Detalles de la actividad.',
                'estado' => $estado,
                'publicado_en' => $estado === 'publicado' ? now() : null,
            ]);
        }

        $this->actingAs($distrital)->get(route('publicaciones.index', [
            'buscar' => 'Campamento especial', 'categoria' => $categoria->id, 'estado' => 'borrador',
        ]))->assertOk()
            ->assertViewHas('conteos', fn ($conteos) => (int) $conteos['publicado'] === 1 && (int) $conteos['borrador'] === 1)
            ->assertViewHas('noticias', fn ($noticias) => $noticias->total() === 1 && $noticias->first()->estado === 'borrador');
    }

    public function test_imagen_de_noticia_se_puede_subir_reemplazar_y_quitar(): void
    {
        Storage::fake('public');
        $distrital = $this->usuario('dirigente_distrital');
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');
        $datos = ['titulo' => 'Imagen de prueba', 'resumen' => 'Resumen de prueba',
            'contenido' => 'Contenido de prueba', 'estado' => 'publicado'];

        $this->actingAs($distrital)->post(route('publicaciones.noticias.store'), $datos + [
            'imagen' => UploadedFile::fake()->createWithContent('primera.png', $png),
        ])->assertSessionHasNoErrors();
        $noticia = Noticia::latest('id')->firstOrFail();
        $primera = Str::after($noticia->imagen_url, 'storage/');
        Storage::disk('public')->assertExists($primera);

        $this->actingAs($distrital)->put(route('publicaciones.noticias.update', $noticia), $datos + [
            'imagen' => UploadedFile::fake()->createWithContent('segunda.png', $png),
        ])->assertSessionHasNoErrors();
        $segunda = Str::after($noticia->fresh()->imagen_url, 'storage/');
        $this->assertNotSame($primera, $segunda);
        Storage::disk('public')->assertMissing($primera);
        Storage::disk('public')->assertExists($segunda);

        $this->actingAs($distrital)->put(route('publicaciones.noticias.update', $noticia), $datos + [
            'quitar_imagen' => '1',
        ])->assertSessionHasNoErrors();
        $this->assertNull($noticia->fresh()->imagen_url);
        Storage::disk('public')->assertMissing($segunda);
    }

    public function test_categorias_no_aceptan_slug_duplicado_y_contenido_institucional_es_distrital(): void
    {
        $distrital = $this->usuario('dirigente_distrital');
        $this->actingAs($distrital)->post(route('publicaciones.categorias.store'), [
            'nombre' => 'Servicio comunitario',
        ])->assertSessionHasNoErrors();
        $this->actingAs($distrital)->post(route('publicaciones.categorias.store'), [
            'nombre' => 'Servicio-Comunitario',
        ])->assertSessionHasErrors('nombre');
        $this->assertSame(1, Categoria::where('slug', 'servicio-comunitario')->count());

        $contenido = ContenidoInstitucional::where('clave', 'mision')->firstOrFail();
        $dirigente = $this->usuario('dirigente_grupo', GrupoScout::first()->id);
        $this->actingAs($dirigente)->put(route('publicaciones.contenido.update', $contenido), [
            'titulo' => 'Otra misión', 'contenido' => 'Texto no autorizado',
        ])->assertForbidden();
        $this->actingAs($distrital)->put(route('publicaciones.contenido.update', $contenido), [
            'titulo' => 'Nuestra misión actual', 'contenido' => 'Educar para servir a la comunidad.',
        ])->assertSessionHasNoErrors();
        $this->get(route('nosotros'))->assertSee('Nuestra misión actual');
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
        $this->actingAs($distrital)->get(route('eventos.index'))->assertOk()
            ->assertSee('event-meta-icon', false)
            ->assertDontSee('📅')
            ->assertDontSee('📍')
            ->assertDontSee('👥')
            ->assertDontSee('💵');
        $this->actingAs($distrital)->get(route('publicaciones.index'))->assertOk();
        $dirigente = User::where('email', 'dirigente1@scoutlapaz.bo')->firstOrFail();
        $this->actingAs($dirigente)->get(route('miembros.index'))->assertOk();
        $miembro = User::where('email', 'miembro1@scoutlapaz.bo')->firstOrFail();
        $this->actingAs($miembro)->get(route('perfil'))->assertOk();
        $this->actingAs($miembro)->get(route('eventos.index'))->assertOk();
    }

    public function test_resumen_de_miembro_muestra_su_rama_y_eventos_donde_ya_esta_inscrito(): void
    {
        $lobatos = Rama::where('slug', 'lobatos')->firstOrFail();
        $rovers = Rama::where('slug', 'rovers')->firstOrFail();
        $miembro = $this->usuarioMiembro($lobatos);
        $eventoPropio = $this->eventoPublicado([$lobatos->id], [
            'titulo' => 'Campamento Lobato', 'fecha_inicio' => now()->addDay(),
            'fecha_fin' => now()->addDay()->addHours(3),
        ]);
        $eventoInscrito = $this->eventoPublicado([$rovers->id], [
            'titulo' => 'Evento Rover inscrito', 'fecha_inicio' => now()->addDays(2),
            'fecha_fin' => now()->addDays(2)->addHours(3),
        ]);
        $this->eventoPublicado([$rovers->id], [
            'titulo' => 'Encuentro Rover exclusivo', 'fecha_inicio' => now()->addDay(),
            'fecha_fin' => now()->addDay()->addHours(3),
        ]);
        $eventoPropio->inscripciones()->create([
            'miembro_id' => $miembro->miembro_id, 'estado' => 'inscrito', 'inscrito_en' => now(),
        ]);
        $eventoInscrito->inscripciones()->create([
            'miembro_id' => $miembro->miembro_id, 'estado' => 'inscrito', 'inscrito_en' => now(),
        ]);

        $this->actingAs($miembro)->get(route('panel'))->assertOk()
            ->assertSee('Campamento Lobato')->assertSee('Evento Rover inscrito')
            ->assertDontSee('Encuentro Rover exclusivo')->assertSee('Inscrito')->assertSee('eventos para ti')
            ->assertDontSee('solicitudes pendientes')
            ->assertViewHas('datos', fn ($datos) => $datos['eventos'] === 3 && $datos['miembros'] === null);
    }

    public function test_resumen_de_dirigentes_acota_pendientes_segun_su_rol(): void
    {
        [$grupoA, $grupoB] = GrupoScout::take(2)->get();
        $rama = Rama::where('slug', 'rovers')->firstOrFail();
        $dirigente = $this->usuario('dirigente_grupo', $grupoA->id);
        $distrital = $this->usuario('dirigente_distrital');
        $this->usuario('miembro', $grupoA->id, 'pendiente');
        $this->usuario('miembro', $grupoB->id, 'pendiente');
        $this->usuario('dirigente_grupo', $grupoA->id, 'pendiente');
        foreach ([$grupoA, $grupoB] as $grupo) {
            $proponente = Miembro::create(['grupo_id' => $grupo->id, 'rama_id' => $rama->id,
                'nombre' => 'Rover', 'apellido' => Str::random(5), 'fecha_nacimiento' => '2006-01-01']);
            $this->eventoPublicado([$rama->id], [
                'estado' => 'propuesto', 'propuesto_por_miembro_id' => $proponente->id,
            ]);
        }
        $this->aviso($dirigente, 'Aviso para grupo');
        $this->aviso($distrital, 'Aviso distrital');

        $this->actingAs($dirigente)->get(route('panel'))->assertOk()
            ->assertSee('Aviso para grupo')->assertDontSee('Aviso distrital')
            ->assertViewHas('datos', fn ($datos) => $datos['solicitudes'] === 1 && $datos['propuestas'] === 1);
        $this->actingAs($distrital)->get(route('panel'))->assertOk()
            ->assertViewHas('datos', fn ($datos) => $datos['solicitudes'] === 3 && $datos['propuestas'] === 2);
    }

    public function test_miembro_actualiza_su_telefono_y_sus_contactos_sin_acceder_a_los_ajenos(): void
    {
        $rama = Rama::firstOrFail();
        $miembro = $this->usuarioMiembro($rama);
        $otro = $this->usuarioMiembro($rama);
        $propio = $miembro->miembro->contactos()->create([
            'nombre' => 'Contacto inicial', 'parentesco' => 'Madre', 'telefono' => '70000001', 'tipo' => 'ambos',
        ]);
        $ajeno = $otro->miembro->contactos()->create([
            'nombre' => 'Contacto ajeno', 'parentesco' => 'Padre', 'telefono' => '70000002', 'tipo' => 'tutor',
        ]);

        $this->actingAs($miembro)->get(route('perfil'))->assertOk()
            ->assertSee('Contactos de confianza')->assertSee('Contacto inicial')
            ->assertSee($miembro->email)->assertDontSee('Contacto ajeno');
        $this->put(route('perfil.telefono.update'), ['telefono' => '76543210'])
            ->assertSessionHas('estado', 'Teléfono actualizado.');
        $this->assertSame('76543210', $miembro->miembro->fresh()->telefono);
        $this->put(route('perfil.contactos.update', $propio), [
            'nombre' => 'Contacto corregido', 'parentesco' => 'Madre', 'telefono' => '79999999', 'tipo' => 'emergencia',
        ])->assertSessionHas('estado', 'Contacto actualizado.');
        $this->assertDatabaseHas('contactos_miembro', ['id' => $propio->id, 'nombre' => 'Contacto corregido',
            'telefono' => '79999999', 'tipo' => 'emergencia']);
        $this->put(route('perfil.contactos.update', $ajeno), [
            'nombre' => 'Cambio indebido', 'telefono' => '71111111', 'tipo' => 'ambos',
        ])->assertForbidden();
        $this->delete(route('perfil.contactos.destroy', $ajeno))->assertForbidden();
        $this->assertDatabaseHas('contactos_miembro', ['id' => $ajeno->id, 'nombre' => 'Contacto ajeno']);
    }

    public function test_perfil_permite_agregar_contacto_y_cambiar_contrasena(): void
    {
        $miembro = $this->usuarioMiembro(Rama::firstOrFail());
        $this->actingAs($miembro)->post(route('perfil.contactos.store'), [
            'nombre' => 'Contacto nuevo', 'parentesco' => 'Tutor', 'telefono' => '78888888', 'tipo' => 'tutor',
        ])->assertSessionHas('estado', 'Contacto guardado.');
        $this->assertDatabaseHas('contactos_miembro', ['miembro_id' => $miembro->miembro_id,
            'nombre' => 'Contacto nuevo']);
        $this->get(route('perfil'))->assertSee('Cambiar contraseña')->assertSee('Contacto nuevo');
        $dirigente = $this->usuario('dirigente_grupo', GrupoScout::first()->id);
        $this->actingAs($dirigente)->get(route('perfil'))->assertForbidden();
        $this->put(route('perfil.telefono.update'), ['telefono' => '76543210'])->assertForbidden();

        $this->actingAs($miembro);
        $this->post(route('password.cambiar'), [
            'actual' => 'Password1', 'password' => 'ClaveNueva123', 'password_confirmation' => 'ClaveNueva123',
        ])->assertSessionHas('estado', 'Contraseña actualizada.');
        $this->assertTrue(Hash::check('ClaveNueva123', $miembro->fresh()->password_hash));
    }

    public function test_un_dirigente_puede_cancelar_su_evento_sin_borrarlo(): void
    {
        $dirigente = $this->usuario('dirigente_grupo', GrupoScout::first()->id);
        $rama = Rama::firstOrFail();
        $evento = $this->eventoPublicado([$rama->id], ['creado_por' => $dirigente->id]);
        $miembro = $this->usuarioMiembro($rama);
        $this->actingAs($miembro)->post(route('eventos.inscribir', $evento))->assertSessionHasNoErrors();
        $this->actingAs($dirigente)->post(route('eventos.cancelar-evento', $evento))->assertRedirect();
        $this->assertDatabaseHas('eventos', ['id' => $evento->id, 'estado' => 'cancelado',
            'inscripcion_habilitada' => false]);
        $this->actingAs($miembro)->get(route('eventos.index'))->assertSee($evento->titulo);
        $this->actingAs($miembro)->get(route('eventos.show', $evento))
            ->assertSee('Tu inscripción queda solo como historial.');
        $this->actingAs($miembro)->post(route('eventos.inscribir', $evento))->assertSessionHasErrors('evento');
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

    private function aviso(User $usuario, string $titulo, ?string $url = null, bool $leida = false): DatabaseNotification
    {
        return $usuario->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'AvisoPrueba',
            'data' => ['titulo' => $titulo, 'mensaje' => 'Mensaje de prueba.', 'url' => $url],
            'read_at' => $leida ? now() : null,
        ]);
    }

    private function usuarioMiembro(Rama $rama, ?GrupoScout $grupo = null): User
    {
        $grupo ??= GrupoScout::first();
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
