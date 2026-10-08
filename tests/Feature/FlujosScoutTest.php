<?php

namespace Tests\Feature;

use App\Models\Actividad;
use App\Models\Autorizacion;
use App\Models\Cuota;
use App\Models\Miembro;
use App\Models\Noticia;
use App\Models\Requisito;
use App\Models\Rol;
use App\Models\Unidad;
use App\Models\User;
use App\Services\AlcanceScout;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** Ejercita asistencia, firmas, progresión, permisos y cierre de sesión con rollback. */
class FlujosScoutTest extends TestCase
{
    use DatabaseTransactions;

    public function test_dirigente_familia_y_progresion(): void
    {
        $dirigente = User::where('email', 'dirigente@scouts.bo')->firstOrFail();
        $familia = User::where('email', 'familia@scouts.bo')->firstOrFail();
        $unidad = Unidad::where('dirigente_id', $dirigente->id)->firstOrFail();
        $this->actingAs($dirigente);
        $this->get(route('dirigente.gestion.registros.create', ['recurso' => 'actividades']))->assertOk();
        $this->post(route('dirigente.gestion.registros.store', ['recurso' => 'actividades']), [
            'unidad_id' => $unidad->id, 'titulo' => 'Actividad prueba Laravel', 'fecha' => '2027-01-15',
            'hora' => '09:00', 'lugar' => 'La Paz', 'descripcion' => 'Prueba transaccional',
            'requiere_autorizacion' => 1,
        ])->assertSessionHasNoErrors();
        $actividad = Actividad::where('titulo', 'Actividad prueba Laravel')->firstOrFail();
        $miembros = Miembro::where('unidad_id', $unidad->id)->get();
        $this->post(route('dirigente.operacion', ['accion' => 'asistencia']), [
            'actividad_id' => $actividad->id, 'fecha' => '2027-01-15',
            'asistencias' => $miembros->mapWithKeys(fn ($miembro) => [$miembro->id => 'presente'])->all(),
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('asistencias', ['actividad_id' => $actividad->id, 'estado' => 'presente']);
        $miembro = $miembros->first();
        $requisito = Requisito::whereNull('rama_id')->firstOrFail();
        $this->post(route('dirigente.operacion', ['accion' => 'progresiones']), [
            'miembro_id' => $miembro->id, 'requisitos' => [$requisito->id], 'fecha' => '2027-01-15',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('miembro_requisitos', ['miembro_id' => $miembro->id, 'requisito_id' => $requisito->id]);
        $autorizacion = Autorizacion::where('actividad_id', $actividad->id)
            ->where('tutor_id', $familia->tutor_id)->firstOrFail();
        $this->actingAs($familia)->post(route('familia.operacion', ['accion' => 'autorizar']), [
            'autorizacion_id' => $autorizacion->id, 'estado' => 'firmada',
        ])->assertSessionHasNoErrors();
        $this->assertSame('firmada', $autorizacion->fresh()->estado);
        $this->post(route('familia.operacion', ['accion' => 'autorizar']), [
            'autorizacion_id' => $autorizacion->id, 'estado' => 'rechazada',
        ])->assertStatus(409);
        $fueraFamilia = Miembro::whereNotIn('id', app(AlcanceScout::class)->miembros($familia)->select('miembros.id'))
            ->firstOrFail();
        $this->post(route('familia.operacion', ['accion' => 'medica']), [
            'miembro_id' => $fueraFamilia->id, 'contacto_emergencia' => 'No autorizado',
        ])->assertNotFound();
        $cuota = Cuota::whereIn('miembro_id', app(AlcanceScout::class)->miembros($familia)->select('miembros.id'))
            ->where('estado', 'pendiente')->firstOrFail();
        $this->post(route('familia.operacion', ['accion' => 'pagar']), [
            'cuota_id' => $cuota->id, 'comprobante_nombre' => 'recibo-prueba.pdf',
        ])->assertSessionHasNoErrors();
        $this->assertSame('en_revision', $cuota->fresh()->estado);
    }

    public function test_login_logout_y_ultimo_administrador(): void
    {
        $this->post(route('login.enviar'), ['email' => 'admin@scouts.bo', 'password' => 'admin123'])
            ->assertRedirect(route('panel'));
        $administrador = User::where('email', 'admin@scouts.bo')->firstOrFail();
        $this->put(route('admin.gestion.registros.update', ['recurso' => 'usuarios', 'registro' => $administrador->id]), [
            'nombre' => $administrador->nombre, 'apellido' => $administrador->apellido,
            'email' => $administrador->email, 'rol_id' => Rol::where('nombre', 'familia')->firstOrFail()->id,
            'activo' => 1,
        ])->assertSessionHasErrors('rol_id');
        $this->post(route('salir'))->assertRedirect(route('login'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_eliminar_dirigente_conserva_publicaciones(): void
    {
        $administrador = User::where('email', 'admin@scouts.bo')->firstOrFail();
        $dirigente = User::create(['nombre' => 'Dirigente', 'apellido' => 'Temporal',
            'email' => 'dirigente-temporal@ejemplo.test', 'password_hash' => Hash::make('prueba123'),
            'rol_id' => Rol::where('nombre', 'dirigente')->firstOrFail()->id]);
        $noticia = Noticia::firstOrFail()->replicate();
        $noticia->slug = 'prueba-eliminar-dirigente';
        $noticia->autor_id = $dirigente->id;
        $noticia->save();
        $this->actingAs($administrador)->delete(route('admin.gestion.registros.destroy', [
            'recurso' => 'usuarios', 'registro' => $dirigente->id,
        ]))->assertRedirect();
        $this->assertNull($noticia->fresh()->autor_id);
        $this->assertDatabaseMissing('usuarios', ['id' => $dirigente->id]);
    }
}
