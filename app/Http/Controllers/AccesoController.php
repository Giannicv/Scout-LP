<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccesoRequest;
use App\Models\GrupoScout;
use App\Models\Miembro;
use App\Models\Rama;
use App\Models\Rol;
use App\Models\User;
use App\Notifications\SolicitudCuentaRecibida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AccesoController extends Controller
{
    public function formulario(string $pantalla = 'login')
    {
        return view('auth.'.$pantalla, ['grupos' => GrupoScout::where('activo', true)->orderBy('nombre')->get(),
            'ramas' => Rama::orderBy('orden')->get()]);
    }

    public function ingresar(AccesoRequest $request)
    {
        $clave = 'login:'.hash('sha256', $request->email.'|'.$request->ip());
        if (RateLimiter::tooManyAttempts($clave, 5)) {
            throw ValidationException::withMessages(['email' => 'Espera 30 segundos antes de intentar nuevamente.']);
        }
        if (! Auth::attempt(['email' => $request->email, 'password' => $request->password, 'estado' => 'activo'])) {
            RateLimiter::hit($clave, 30);
            throw ValidationException::withMessages(['email' => 'Datos incorrectos o cuenta aún no aprobada.']);
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
            $rol = Rol::where('nombre', $request->tipo_cuenta)->firstOrFail();
            $miembro = null;
            if ($request->tipo_cuenta === 'miembro') {
                $miembro = Miembro::create($request->only('grupo_id', 'rama_id', 'nombre', 'apellido',
                    'fecha_nacimiento', 'telefono', 'email'));
            }

            return User::create([
                'rol_id' => $rol->id, 'grupo_id' => $request->grupo_id, 'miembro_id' => $miembro?->id,
                'nombre' => $request->nombre, 'apellido' => $request->apellido, 'email' => $request->email,
                'password_hash' => $request->password, 'estado' => 'pendiente',
            ]);
        });
        $revisores = $usuario->es('dirigente_grupo')
            ? User::whereHas('rol', fn ($q) => $q->where('nombre', 'dirigente_distrital'))->where('estado', 'activo')->get()
            : User::whereHas('rol', fn ($q) => $q->where('nombre', 'dirigente_grupo'))->where('grupo_id', $usuario->grupo_id)->where('estado', 'activo')->get();
        $revisores->each->notify(new SolicitudCuentaRecibida($usuario));

        return redirect()->route('login')->with('estado', 'Solicitud registrada. Podrás ingresar cuando sea aprobada.');
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
        $usuario = User::where('email', $request->email)->first();
        $enlace = null;
        if ($usuario && app()->environment('local')) {
            $token = Password::broker()->createToken($usuario);
            $enlace = route('restablecer', ['token' => $token, 'email' => $usuario->email]);
        }

        return back()->with('estado', 'Si el correo existe, se generó una solicitud de recuperación.')
            ->with('enlace_recuperacion', $enlace);
    }

    public function restablecer(AccesoRequest $request)
    {
        $estado = Password::broker()->reset($request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $usuario, string $password) {
                $usuario->forceFill(['password_hash' => Hash::make($password),
                    'version_acceso' => $usuario->version_acceso + 1])->setRememberToken(Str::random(60));
                $usuario->save();
            });
        if ($estado !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => __($estado)]);
        }

        return redirect()->route('login')->with('estado', 'Contraseña actualizada.');
    }

    public function cambiarPassword(AccesoRequest $request)
    {
        $usuario = $request->user();
        if (! Hash::check($request->input('actual', ''), $usuario->password_hash)) {
            throw ValidationException::withMessages(['actual' => 'Contraseña actual incorrecta.']);
        }
        $usuario->update(['password_hash' => $request->password, 'version_acceso' => $usuario->version_acceso + 1]);
        $request->session()->put('version_acceso', $usuario->version_acceso);

        return back()->with('estado', 'Contraseña actualizada.');
    }
}
