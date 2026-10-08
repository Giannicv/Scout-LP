<?php

use App\Http\Controllers\AccesoController;
use App\Http\Controllers\OperacionController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\PublicoController;
use App\Http\Controllers\RecursoController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::pattern('registro', '[0-9]+');

Route::get('/', [PublicoController::class, 'inicio'])->name('inicio');
Route::get('/ramas', [PublicoController::class, 'ramas'])->name('ramas');
Route::get('/noticias', [PublicoController::class, 'noticias'])->name('noticias.index');
Route::get('/noticia/{noticia}', [PublicoController::class, 'noticia'])->name('noticias.show');
Route::get('/nosotros', [PublicoController::class, 'nosotros'])->name('nosotros');
Route::get('/contacto', [PublicoController::class, 'nosotros'])->name('contacto');
Route::post('/contacto', [PublicoController::class, 'contacto'])->middleware('throttle:10,1')->name('contacto.enviar');
Route::view('/mensaje-enviado', 'mensaje-enviado')->name('mensaje-enviado');
Route::get('/salud', function () {
    return response()->json(['ok' => true, 'base' => DB::selectOne('select current_database() nombre')->nombre]);
})->name('salud');
Route::middleware('guest')->group(function () {
    foreach (['login', 'registro', 'recuperar'] as $pantalla) {
        Route::get('/'.$pantalla, [AccesoController::class, 'formulario'])
            ->defaults('pantalla', $pantalla)->name($pantalla);
    }
    Route::post('/login', [AccesoController::class, 'ingresar'])->name('login.enviar');
    Route::post('/registro', [AccesoController::class, 'registrar'])->middleware('throttle:10,1')->name('registro.enviar');
});
Route::post('/recuperar', [AccesoController::class, 'recuperar'])->name('recuperar.enviar');
Route::view('/restablecer/{token}', 'auth.restablecer')->name('restablecer');
Route::post('/restablecer', [AccesoController::class, 'restablecer'])->name('restablecer.enviar');
Route::middleware('auth')->group(function () {
    Route::post('/salir', [AccesoController::class, 'salir'])->name('salir');
    Route::post('/password', [AccesoController::class, 'cambiarPassword'])->name('password.cambiar');
    Route::get('/panel', function () {
        $rol = auth()->user()->rol->nombre;

        return redirect()->route(($rol === 'administrador' ? 'admin' : $rol).'.dashboard');
    })->name('panel');
    foreach (config('scouts.menus') as $prefijo => $paginas) {
        $rol = $prefijo === 'admin' ? 'administrador' : $prefijo;
        Route::prefix($prefijo)->name($prefijo.'.')->middleware('rol:'.$rol)
            ->group(function () use ($paginas, $prefijo) {
                foreach ($paginas as $pagina => $titulo) {
                    Route::get('/'.$pagina, [PanelController::class, 'mostrar'])
                        ->defaults('pagina', $pagina)->name($pagina);
                }
                if (in_array($prefijo, ['admin', 'dirigente'])) {
                    Route::prefix('gestion/{recurso}')->name('gestion.')->group(function () {
                        Route::resource('registros', RecursoController::class)
                            ->parameters(['registros' => 'registro'])->except(['index', 'show']);
                    });
                }
                if (in_array($prefijo, ['admin', 'coordinador', 'dirigente'])) {
                    Route::get('/exportar/miembros', [PanelController::class, 'exportar'])->name('exportar');
                }
                Route::post('/operaciones/{accion}', [OperacionController::class, 'ejecutar'])->name('operacion');
            });
    }
});
