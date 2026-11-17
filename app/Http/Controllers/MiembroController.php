<?php

namespace App\Http\Controllers;

use App\Models\GrupoScout;
use App\Models\Miembro;
use App\Models\Rama;
use App\Models\User;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MiembroController extends Controller
{
    public function __construct(private AuditoriaService $auditoria) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);
        $usuario = $request->user()->load('rol');
        $consulta = Miembro::with(['grupo', 'rama', 'usuario.rol', 'contactos']);
        if ($usuario->es('dirigente_grupo')) {
            $consulta->where('grupo_id', $usuario->grupo_id);
        }
        $totalMiembros = (clone $consulta)->count();
        $buscar = $request->query('buscar', '');
        $buscar = is_string($buscar) ? trim(mb_substr($buscar, 0, 100)) : '';
        if ($buscar !== '') {
            $termino = '%'.mb_strtolower($buscar).'%';
            $consulta->where(fn ($q) => $q->whereRaw('LOWER(nombre) LIKE ?', [$termino])
                ->orWhereRaw('LOWER(apellido) LIKE ?', [$termino])
                ->orWhereRaw("LOWER(nombre || ' ' || apellido) LIKE ?", [$termino])
                ->orWhereRaw('LOWER(email) LIKE ?', [$termino])
                ->orWhereHas('usuario', fn ($u) => $u->whereRaw('LOWER(email) LIKE ?', [$termino])));
        }
        $rama = $request->query('rama');
        if (is_scalar($rama) && ctype_digit((string) $rama)) {
            $consulta->where('rama_id', $rama);
        }
        $grupo = $request->query('grupo');
        if ($usuario->es('dirigente_distrital') && is_scalar($grupo) && ctype_digit((string) $grupo)) {
            $consulta->where('grupo_id', $grupo);
        }
        $estado = $request->query('estado');
        if (in_array($estado, ['activo', 'suspendido', 'pendiente', 'rechazado'], true)) {
            $consulta->whereHas('usuario', fn ($q) => $q->where('estado', $estado));
        } elseif ($estado === 'sin_cuenta') {
            $consulta->whereDoesntHave('usuario');
        }

        $miembros = $consulta->orderBy('apellido')->orderBy('nombre')->paginate(20)->withQueryString();

        return view('panel.miembros', ['miembros' => $miembros, 'totalMiembros' => $totalMiembros,
            'buscar' => $buscar, 'usuario' => $usuario, 'ramas' => Rama::orderBy('orden')->get(),
            'grupos' => $usuario->es('dirigente_distrital') ? GrupoScout::orderBy('nombre')->get() : collect()]);
    }

    public function cambiarEstado(Request $request, User $usuario)
    {
        $this->authorize('updateStatus', $usuario);
        $datos = $request->validate(['estado' => ['required', Rule::in(['activo', 'suspendido'])]]);
        DB::transaction(function () use ($request, $usuario, $datos) {
            $usuario = User::with('rol')->whereKey($usuario->id)->lockForUpdate()->firstOrFail();
            $this->authorize('updateStatus', $usuario);
            if ($usuario->estado === $datos['estado']) {
                throw ValidationException::withMessages(['estado' => 'La cuenta ya tiene ese estado. Actualiza la lista.']);
            }
            $usuario->update(['estado' => $datos['estado'], 'version_acceso' => $usuario->version_acceso + 1,
                'revisado_por' => $request->user()->id, 'revisado_en' => now()]);
            $usuario->miembro()->update(['activo' => $datos['estado'] === 'activo']);
            $this->auditoria->registrar($request, 'cambiar_estado', 'usuario', $usuario->id, ['estado' => $datos['estado']]);
        });

        return back()->with('estado', $datos['estado'] === 'activo' ? 'Cuenta activada.' : 'Cuenta suspendida.');
    }
}
