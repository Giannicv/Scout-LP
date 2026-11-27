<?php

namespace App\Http\Controllers;

use App\Http\Requests\DirigenteRequest;
use App\Models\GrupoScout;
use App\Models\Rol;
use App\Models\User;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DirigenteController extends Controller
{
    public function __construct(private AuditoriaService $auditoria) {}

    public function index(Request $request)
    {
        $this->authorize('manageLeaders', User::class);

        $consulta = User::with(['grupo', 'rol'])->whereHas('rol', fn ($q) => $q->where('nombre', 'dirigente_grupo'));
        $total = (clone $consulta)->count();
        $activos = (clone $consulta)->where('estado', 'activo')->count();
        $pendientes = (clone $consulta)->where('estado', 'pendiente')->count();
        $buscar = $request->query('buscar', '');
        $buscar = is_string($buscar) ? trim(mb_substr($buscar, 0, 100)) : '';
        if ($buscar !== '') {
            $termino = '%'.mb_strtolower($buscar).'%';
            $consulta->where(fn ($q) => $q->whereRaw('LOWER(nombre) LIKE ?', [$termino])
                ->orWhereRaw('LOWER(apellido) LIKE ?', [$termino])
                ->orWhereRaw("LOWER(nombre || ' ' || apellido) LIKE ?", [$termino])
                ->orWhereRaw('LOWER(email) LIKE ?', [$termino]));
        }
        $grupo = $request->query('grupo');
        if (is_scalar($grupo) && ctype_digit((string) $grupo)) {
            $consulta->where('grupo_id', $grupo);
        }
        $estado = $request->query('estado');
        if (in_array($estado, ['activo', 'suspendido', 'pendiente', 'rechazado'], true)) {
            $consulta->where('estado', $estado);
        }

        return view('panel.dirigentes', [
            'dirigentes' => $consulta->orderBy('apellido')->orderBy('nombre')->paginate(15)->withQueryString(),
            'grupos' => GrupoScout::withCount(['usuarios as dirigentes_activos_count' => fn ($q) => $q
                ->where('estado', 'activo')->whereHas('rol', fn ($roles) => $roles->where('nombre', 'dirigente_grupo'))])
                ->orderBy('nombre')->get(),
            'total' => $total, 'activos' => $activos, 'pendientes' => $pendientes, 'buscar' => $buscar,
        ]);
    }

    public function create()
    {
        $this->authorize('manageLeaders', User::class);

        return view('panel.dirigente-form', ['dirigente' => null,
            'grupos' => GrupoScout::where('activo', true)->orderBy('nombre')->get()]);
    }

    public function store(DirigenteRequest $request)
    {
        $datos = $request->validated();
        DB::transaction(function () use ($request, $datos) {
            $dirigente = User::create([
                'rol_id' => Rol::where('nombre', 'dirigente_grupo')->firstOrFail()->id,
                'grupo_id' => $datos['grupo_id'], 'nombre' => $datos['nombre'], 'apellido' => $datos['apellido'],
                'email' => $datos['email'], 'password_hash' => $datos['password'], 'estado' => 'activo',
                'revisado_por' => $request->user()->id, 'revisado_en' => now(),
            ]);
            $this->auditoria->registrar($request, 'crear_dirigente', 'usuario', $dirigente->id,
                ['grupo_id' => $dirigente->grupo_id]);
        });

        return redirect()->route('dirigentes.index')->with('estado', 'Dirigente de grupo creado y activo.');
    }

    public function edit(User $dirigente)
    {
        $this->authorize('updateLeader', $dirigente);

        return view('panel.dirigente-form', ['dirigente' => $dirigente,
            'grupos' => GrupoScout::where('activo', true)->orWhere('id', $dirigente->grupo_id)->orderBy('nombre')->get()]);
    }

    public function update(DirigenteRequest $request, User $dirigente)
    {
        $datos = $request->validated();
        DB::transaction(function () use ($request, $dirigente, $datos) {
            $dirigente = User::with('rol')->whereKey($dirigente->id)->lockForUpdate()->firstOrFail();
            $this->authorize('updateLeader', $dirigente);
            $grupoAnterior = $dirigente->grupo_id;
            $dirigente->update([
                'nombre' => $datos['nombre'], 'apellido' => $datos['apellido'],
                'email' => $datos['email'], 'grupo_id' => $datos['grupo_id'],
                'version_acceso' => $dirigente->version_acceso + ($grupoAnterior != $datos['grupo_id'] ? 1 : 0),
            ]);
            $this->auditoria->registrar($request, 'actualizar_dirigente', 'usuario', $dirigente->id,
                ['grupo_anterior' => $grupoAnterior, 'grupo_id' => $dirigente->grupo_id]);
        });

        return redirect()->route('dirigentes.index')->with('estado', 'Datos del dirigente actualizados.');
    }

    public function cambiarEstado(Request $request, User $dirigente)
    {
        $this->authorize('updateLeader', $dirigente);
        $datos = $request->validate(['estado' => ['required', Rule::in(['activo', 'suspendido'])]]);
        DB::transaction(function () use ($request, $dirigente, $datos) {
            $dirigente = User::with('rol')->whereKey($dirigente->id)->lockForUpdate()->firstOrFail();
            $this->authorize('updateLeader', $dirigente);
            if ($dirigente->estado === $datos['estado']) {
                throw ValidationException::withMessages(['estado' => 'La cuenta ya tiene ese estado. Actualiza la lista.']);
            }
            $dirigente->update(['estado' => $datos['estado'], 'version_acceso' => $dirigente->version_acceso + 1,
                'revisado_por' => $request->user()->id, 'revisado_en' => now()]);
            $this->auditoria->registrar($request, 'cambiar_estado_dirigente', 'usuario', $dirigente->id,
                ['estado' => $datos['estado']]);
        });

        return back()->with('estado', $datos['estado'] === 'activo' ? 'Dirigente reactivado.' : 'Dirigente suspendido.');
    }
}
