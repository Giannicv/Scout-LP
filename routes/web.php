<?php

use App\Http\Controllers\AccesoController;
use App\Http\Controllers\BibliotecaController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\MiembroController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\PublicacionController;
use App\Http\Controllers\PublicoController;
use App\Http\Controllers\SolicitudController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicoController::class, 'inicio'])->name('inicio');
Route::get('/ramas', [PublicoController::class, 'ramas'])->name('ramas');
Route::get('/noticias', [PublicoController::class, 'noticias'])->name('noticias.index');
Route::get('/noticia/{noticia}', [PublicoController::class, 'noticia'])->name('noticias.show');
Route::get('/nosotros', [PublicoController::class, 'nosotros'])->name('nosotros');
Route::get('/salud', fn () => response()->json(['ok' => true]))->name('salud');

Route::middleware('guest')->group(function () {
    foreach (['login', 'registro', 'recuperar'] as $pantalla) {
        Route::get('/'.$pantalla, [AccesoController::class, 'formulario'])->defaults('pantalla', $pantalla)->name($pantalla);
    }
    Route::post('/login', [AccesoController::class, 'ingresar'])->name('login.enviar');
    Route::post('/registro', [AccesoController::class, 'registrar'])->middleware('throttle:10,1')->name('registro.enviar');
    Route::post('/recuperar', [AccesoController::class, 'recuperar'])->middleware('throttle:5,1')->name('recuperar.enviar');
    Route::get('/restablecer/{token}', [AccesoController::class, 'formulario'])->defaults('pantalla', 'restablecer')->name('restablecer');
    Route::post('/restablecer', [AccesoController::class, 'restablecer'])->name('restablecer.enviar');
});

Route::middleware('auth')->group(function () {
    Route::post('/salir', [AccesoController::class, 'salir'])->name('salir');
    Route::post('/password', [AccesoController::class, 'cambiarPassword'])->name('password.cambiar');
    Route::get('/panel', [PanelController::class, 'dashboard'])->name('panel');
    Route::resource('/panel/eventos', EventoController::class)->except(['show', 'destroy']);
    Route::post('/panel/eventos/{evento}/aprobar', [EventoController::class, 'aprobar'])->name('eventos.aprobar');
    Route::post('/panel/eventos/{evento}/rechazar', [EventoController::class, 'rechazar'])->name('eventos.rechazar');
    Route::post('/panel/eventos/{evento}/cancelar', [EventoController::class, 'cancelarEvento'])->name('eventos.cancelar-evento');
    Route::post('/panel/eventos/{evento}/inscripcion', [EventoController::class, 'inscribir'])->name('eventos.inscribir');
    Route::delete('/panel/eventos/{evento}/inscripcion', [EventoController::class, 'cancelar'])->name('eventos.cancelar');
    Route::get('/panel/solicitudes', [SolicitudController::class, 'index'])->name('solicitudes.index');
    Route::post('/panel/solicitudes/{solicitud}', [SolicitudController::class, 'resolver'])->name('solicitudes.resolver');
    Route::get('/panel/biblioteca', [BibliotecaController::class, 'index'])->name('biblioteca.index');
    Route::post('/panel/biblioteca', [BibliotecaController::class, 'store'])->name('biblioteca.store');
    Route::get('/panel/biblioteca/{recurso}', [BibliotecaController::class, 'download'])->name('biblioteca.download');
    Route::delete('/panel/biblioteca/{recurso}', [BibliotecaController::class, 'destroy'])->name('biblioteca.destroy');
    Route::get('/panel/notificaciones', [NotificacionController::class, 'index'])->name('notificaciones.index');
    Route::post('/panel/notificaciones/{notificacion}/leer', [NotificacionController::class, 'leer'])->name('notificaciones.leer');
    Route::get('/panel/miembros', [MiembroController::class, 'index'])->name('miembros.index');
    Route::post('/panel/usuarios/{usuario}/estado', [MiembroController::class, 'cambiarEstado'])->name('usuarios.estado');
    Route::get('/panel/perfil', [PerfilController::class, 'show'])->name('perfil');
    Route::post('/panel/perfil/contactos', [PerfilController::class, 'guardarContacto'])->name('perfil.contactos.store');
    Route::delete('/panel/perfil/contactos/{contacto}', [PerfilController::class, 'eliminarContacto'])->name('perfil.contactos.destroy');
    Route::get('/panel/publicaciones', [PublicacionController::class, 'index'])->name('publicaciones.index');
    Route::post('/panel/publicaciones/noticias', [PublicacionController::class, 'store'])->name('publicaciones.noticias.store');
    Route::put('/panel/publicaciones/noticias/{noticia}', [PublicacionController::class, 'update'])->name('publicaciones.noticias.update');
    Route::delete('/panel/publicaciones/noticias/{noticia}', [PublicacionController::class, 'destroy'])->name('publicaciones.noticias.destroy');
    Route::post('/panel/publicaciones/categorias', [PublicacionController::class, 'categoria'])->name('publicaciones.categorias.store');
    Route::put('/panel/publicaciones/contenido/{contenido}', [PublicacionController::class, 'contenido'])->name('publicaciones.contenido.update');
});
