<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/** Crea cuentas locales de ejemplo; nunca reemplaza contraseñas existentes. */
class UsuariosDemoSeeder extends Seeder
{
    public function run(): void
    {
        $passwords = ['admin' => 'admin123', 'coordinador' => 'coord123',
            'dirigente' => 'jefe123', 'familia' => 'familia123', 'miembro' => 'scout123'];
        foreach (ContextoDemo::datos()['sprint']['usuarios'] as $datosUsuario) {
            if (User::where('email', $datosUsuario['correo'])->exists()) {
                continue;
            }
            $partesNombre = explode(' ', $datosUsuario['nombre'], 2);
            $nombreRol = $datosUsuario['rol'] === 'admin' ? 'administrador' : $datosUsuario['rol'];
            $datos = ['nombre' => $partesNombre[0], 'apellido' => $partesNombre[1] ?? '',
                'email' => $datosUsuario['correo'], 'telefono' => $datosUsuario['telefono'] ?? ''];
            $tutor = $nombreRol === 'familia' ? Tutor::create($datos) : null;
            User::create($datos + [
                'rol_id' => Rol::where('nombre', $nombreRol)->firstOrFail()->id,
                'password_hash' => Hash::make($passwords[$datosUsuario['rol']]),
                'tutor_id' => $tutor?->id, 'departamento' => $datosUsuario['departamento'] ?? null,
            ]);
        }
    }
}
