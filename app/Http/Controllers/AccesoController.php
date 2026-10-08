<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccesoRequest;
use App\Models\Rol;
use App\Models\TokenRecuperacion;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** Gestiona el acceso único con la autenticación y la sesión de Laravel. */
class AccesoController extends Controller
{
    public function formulario(string $pantalla = 'login')
    {
        return view('auth.'.$pantalla);
    }

    public function ingresar(AccesoRequest $request)
    {
        $clave = 'login:'.hash('sha256', $request->email.'|'.$request->ip());
        if (RateLimiter::tooManyAttempts($clave, 5)) {
            throw ValidationException::withMessages(['email' => 'Espera 30 segundos antes de intentar nuevamente.']);
        }
        if (! Auth::attempt($request->only('email', 'password') + ['activo' => true])) {
            RateLimiter::hit($clave, 30);
            throw ValidationException::withMessages(['email' => 'Correo o contraseña incorrectos.']);
        }
        RateLimiter::clear($clave);
        $request->session()->regenerate();
        $request->session()->put('version_acceso', Auth::user()->version_acceso);
        Auth::user()->update(['ultimo_acceso' => now()]);

        return redirect()->route('panel');
    }

    public function registrar(AccesoRequest $request)
    {
        $usuario = DB::transaction(function () use ($request) {
            $tutor = Tutor::create($request->only('nombre', 'apellido', 'telefono', 'email'));

            return User::create($request->only('nombre', 'apellido', 'telefono', 'email') + [
                'rol_id' => Rol::where('nombre', 'familia')->firstOrFail()->id,
                'tutor_id' => $tutor->id, 'password_hash' => Hash::make($request->password),
            ]);
        });
        $usuario->refresh();
        Auth::login($usuario);
        $request->session()->regenerate();
        $request->session()->put('version_acceso', $usuario->version_acceso);

        return redirect()->route('panel');
    }

    public function salir(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function recuperar(AccesoRequest $request)
    {
        $clave = 'recuperar:'.$request->ip();
        abort_if(RateLimiter::tooManyAttempts($clave, 5), 429);
        RateLimiter::hit($clave, 60);
        $usuario = User::where('email', $request->email)->first();
        $enlace = null;
        if ($usuario && app()->environment('local')) {
            $token = Str::random(64);
            TokenRecuperacion::create([
                'token_hash' => hash('sha256', $token), 'usuario_id' => $usuario->id,
                'vence_en' => now()->addMinutes(10),
            ]);
            $enlace = route('restablecer', ['token' => $token]);
        }

        return back()->with('estado', 'Recuperación de demostración; no se envió correo.')
            ->with('enlace_recuperacion', $enlace);
    }

    public function restablecer(AccesoRequest $request)
    {
        DB::transaction(function () use ($request) {
            $registro = TokenRecuperacion::where('token_hash', hash('sha256', $request->string('token')))
                ->where('vence_en', '>', now())->lockForUpdate()->first();
            if (! $registro) {
                throw ValidationException::withMessages(['token' => 'Enlace inválido o vencido.']);
            }
            $usuario = User::findOrFail($registro->usuario_id);
            $usuario->update([
                'password_hash' => Hash::make($request->password),
                'version_acceso' => $usuario->version_acceso + 1,
            ]);
            TokenRecuperacion::where('usuario_id', $usuario->id)->delete();
        });

        return redirect()->route('login')->with('estado', 'Contraseña actualizada.');
    }

    public function cambiarPassword(AccesoRequest $request)
    {
        $usuario = $request->user();
        if (! Hash::check($request->input('actual', ''), $usuario->password_hash)) {
            throw ValidationException::withMessages(['actual' => 'Contraseña actual incorrecta.']);
        }
        $usuario->update([
            'password_hash' => Hash::make($request->password),
            'version_acceso' => $usuario->version_acceso + 1,
        ]);
        $request->session()->regenerate();
        $request->session()->put('version_acceso', $usuario->version_acceso);

        return back()->with('estado', 'Contraseña actualizada.');
    }
}
