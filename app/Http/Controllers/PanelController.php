<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\Asistencia;
use App\Models\Autorizacion;
use App\Models\Comunicado;
use App\Models\Cuota;
use App\Models\Evento;
use App\Models\Inscripcion;
use App\Models\MensajeContacto;
use App\Models\Miembro;
use App\Models\MiembroRequisito;
use App\Models\Noticia;
use App\Models\Rama;
use App\Models\Requisito;
use App\Models\User;
use App\Services\AlcanceScout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Prepara datos del panel mediante consultas Eloquent limitadas por usuario. */
class PanelController extends Controller
{
    public function mostrar(Request $request, AlcanceScout $alcance, string $pagina = 'dashboard')
    {
        $usuario = $request->user();
        $rol = $usuario->rol->nombre;
        $rolRuta = $rol === 'administrador' ? 'admin' : $rol;
        abort_unless(isset(config('scouts.menus')[$rolRuta][$pagina]), 404);
        $consultaMiembros = $alcance->miembros($usuario);
        if ($busqueda = $request->input('buscar')) {
            $consultaMiembros->whereRaw("(nombre || ' ' || apellido) ILIKE ?", ['%'.$busqueda.'%']);
        }
        if ($request->filled('rama_id')) {
            $consultaMiembros->where('rama_id', $request->integer('rama_id'));
        }
        if ($request->filled('activo')) {
            $consultaMiembros->where('activo', $request->boolean('activo'));
        }
        $miembros = $consultaMiembros->with(['rama', 'grupo.distrito', 'unidad', 'familias'])->get();
        $unidades = $alcance->unidades($usuario)->with(['rama', 'dirigente', 'grupo.distrito'])->get();
        $actividades = Actividad::whereIn('unidad_id', $unidades->pluck('id'))->orderBy('fecha')->get();
        $asistencias = Asistencia::whereIn('miembro_id', $miembros->pluck('id'))->with('actividad')->get();
        $progresiones = MiembroRequisito::whereIn('miembro_id', $miembros->pluck('id'))->with('requisito')->get();
        $requisitos = Requisito::orderBy('id')->get();
        $autorizaciones = Autorizacion::whereIn('miembro_id', $miembros->pluck('id'))
            ->when($rol === 'familia', fn ($consulta) => $consulta->where('tutor_id', $usuario->tutor_id))
            ->with(['actividad', 'miembro'])->get();
        $cuotas = in_array($rol, ['familia', 'administrador'])
            ? Cuota::whereIn('miembro_id', $miembros->pluck('id'))->with('miembro')->get() : collect();
        $comunicados = Comunicado::whereIn('unidad_id', $unidades->pluck('id'))
            ->when(in_array($rol, ['familia', 'miembro']), fn ($consulta) => $consulta->whereIn('destino',
                ['ambos', $rol]))
            ->latest('creado_en')->get();
        $lecturas = DB::table('comunicado_lecturas')->where('usuario_id', $usuario->id)->pluck('comunicado_id');
        $inscripciones = Inscripcion::query()
            ->when($rol === 'familia', fn ($consulta) => $consulta->where('tutor_id', $usuario->tutor_id))
            ->when($rol === 'coordinador', fn ($consulta) => $consulta->where('departamento',
                $usuario->departamento))
            ->when(! in_array($rol, ['familia', 'coordinador', 'administrador']),
                fn ($consulta) => $consulta->whereRaw('false'))
            ->with(['rama', 'unidad'])->orderByDesc('id')->get();
        $dirigentes = User::whereHas('rol', fn ($consulta) => $consulta->where('nombre', 'dirigente'))
            ->where('departamento', $usuario->departamento)->where('activo', true)->get();
        $ramas = Rama::orderBy('orden')->get();
        $registros = null;
        $configuracion = config('recursos.'.$pagina);
        if ($configuracion && ($rol === 'administrador' || $rol === 'dirigente')) {
            $consulta = app(RecursoController::class)->consulta($request, $pagina);
            if ($pagina === 'miembros') {
                $consulta = $consultaMiembros;
            }
            $registros = $consulta->orderByDesc('id')->paginate(10)->withQueryString();
        }
        $companeros = collect();
        if ($rol === 'miembro' && $miembros->first()?->unidad_id) {
            $companeros = Miembro::where('unidad_id', $miembros->first()->unidad_id)
                ->get(['nombre', 'apellido', 'rol_patrulla']);
        }
        $noticias = Noticia::where('estado', 'publicado')->latest('publicado_en')->limit(3)->get();
        $estadisticas = [];
        if ($rol === 'administrador') {
            $estadisticas = [
                'total' => Miembro::count(),
                'activos' => Miembro::where('activo', true)->count(),
                'noticias' => Noticia::where('estado', 'publicado')->count(),
                'mensajes' => MensajeContacto::where('estado', 'nuevo')->count(),
                'roles' => DB::table('roles')->leftJoin('usuarios', 'usuarios.rol_id', '=', 'roles.id')
                    ->selectRaw('roles.nombre, count(usuarios.id) total')->groupBy('roles.nombre')->get(),
                'ultimos_mensajes' => MensajeContacto::latest('creado_en')->limit(4)->get(),
                'eventos' => Evento::where('fecha_inicio',
                    '>=', now())->orderBy('fecha_inicio')->limit(4)->get(),
            ];
        }
        $titulo = config('scouts.menus')[$rolRuta][$pagina];

        return view($rolRuta.'.'.$pagina, compact(
            'usuario', 'rol', 'rolRuta', 'pagina', 'titulo', 'miembros', 'unidades', 'actividades',
            'asistencias', 'progresiones', 'requisitos', 'autorizaciones', 'cuotas', 'comunicados',
            'lecturas', 'inscripciones', 'dirigentes', 'ramas', 'registros',
            'configuracion', 'companeros', 'noticias', 'estadisticas'
        ));
    }

    public function exportar(Request $request, AlcanceScout $alcance)
    {
        $consulta = $alcance->miembros($request->user())->with(['rama', 'grupo.distrito']);
        if ($request->filled('buscar')) {
            $consulta->whereRaw("(nombre || ' ' || apellido) ILIKE ?", ['%'.$request->input('buscar').'%']);
        }

        return response()->streamDownload(function () use ($consulta) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, ['Nombre', 'Rama', 'Grupo', 'Departamento', 'Estado']);
            foreach ($consulta->cursor() as $miembro) {
                $valores = [$miembro->nombre.' '.$miembro->apellido, $miembro->rama->nombre,
                    $miembro->grupo->nombre, $miembro->grupo->distrito->departamento,
                    $miembro->activo ? 'Activo' : 'Inactivo'];
                fputcsv($salida, array_map(fn ($valor) => preg_match('/^[=+@\-\t\r]/',
                    $valor) ? "'".$valor : $valor, $valores));
            }
            fclose($salida);
        }, 'miembros-scouts.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
