<?php

namespace App\Http\Controllers;

use App\Enums\EstadoEvento;
use App\Enums\TipoEvento;
use App\Http\Requests\EventoRequest;
use App\Models\Evento;
use App\Models\InscripcionEvento;
use App\Models\Rama;
use App\Notifications\EventoResuelto;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EventoController extends Controller
{
    public function __construct(private AuditoriaService $auditoria) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Evento::class);
        $usuario = $request->user()->load('rol', 'miembro.rama');
        $consulta = Evento::with(['ramas', 'creador', 'inscripciones'])
            ->withCount(['inscripciones as inscritos_count' => fn ($q) => $q->where('estado', 'inscrito')]);
        if ($usuario->es('miembro')) {
            $consulta->where(function ($q) use ($usuario) {
                $q->where(fn ($p) => $p->where('estado', 'publicado')->whereHas('ramas', fn ($r) => $r->whereKey($usuario->miembro->rama_id)))
                    ->orWhere('propuesto_por_miembro_id', $usuario->miembro_id);
            });
        } elseif ($usuario->es('dirigente_grupo')) {
            $consulta->where(fn ($q) => $q->where('creado_por', $usuario->id)->orWhere('estado', 'publicado')
                ->orWhereHas('proponente', fn ($m) => $m->where('grupo_id', $usuario->grupo_id)));
        }
        if ($request->filled('buscar')) {
            $termino = '%'.mb_strtolower($request->buscar).'%';
            $consulta->where(fn ($q) => $q->whereRaw('LOWER(titulo) LIKE ?', [$termino])
                ->orWhereRaw('LOWER(lugar) LIKE ?', [$termino]));
        }
        if ($request->filled('estado')) {
            $consulta->where('estado', $request->estado);
        }
        if ($request->vigencia === 'proximos') {
            $consulta->where('fecha_fin', '>=', now());
        } elseif ($request->vigencia === 'finalizados') {
            $consulta->where('fecha_fin', '<', now());
        }
        $eventos = $consulta->orderByRaw('CASE WHEN fecha_fin >= CURRENT_TIMESTAMP THEN 0 ELSE 1 END')
            ->orderBy('fecha_inicio')->paginate(12)->withQueryString();

        return view('panel.eventos', compact('eventos', 'usuario'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', Evento::class);
        $inicio = now()->addWeek()->startOfHour();
        $evento = new Evento(['fecha_inicio' => $inicio, 'fecha_fin' => $inicio->copy()->addHours(4),
            'inscripcion_desde' => now()->startOfHour(), 'inscripcion_hasta' => $inicio->copy()->subDay(),
            'estado' => EstadoEvento::BORRADOR->value, 'inscripcion_habilitada' => true]);

        return view('panel.evento-form', ['evento' => $evento, 'ramas' => Rama::orderBy('orden')->get()]);
    }

    public function show(Request $request, Evento $evento)
    {
        $this->authorize('view', $evento);
        $usuario = $request->user()->load('rol', 'miembro.rama');
        $evento->load(['ramas', 'creador.grupo', 'publicador', 'proponente.grupo',
            'inscripciones.miembro.rama', 'inscripciones.miembro.grupo']);
        $evento->loadCount(['inscripciones as inscritos_count' => fn ($q) => $q->where('estado', 'inscrito')]);
        $inscripcion = $usuario->es('miembro')
            ? $evento->inscripciones->firstWhere('miembro_id', $usuario->miembro_id) : null;
        $motivoNoInscripcion = $usuario->es('miembro') && $inscripcion?->estado !== 'inscrito'
            ? $evento->motivoNoInscripcion($usuario->miembro) : null;
        $puedeGestionar = $usuario->es('dirigente_distrital') || $evento->creado_por === $usuario->id;

        return view('panel.evento-detalle', compact('evento', 'usuario', 'inscripcion',
            'motivoNoInscripcion', 'puedeGestionar'));
    }

    public function store(EventoRequest $request)
    {
        $this->authorize('create', Evento::class);
        $usuario = $request->user()->load('rol', 'miembro.rama');
        if ($usuario->es('miembro')) {
            abort_unless($usuario->miembro?->rama?->slug === 'rovers' && $request->tipo === TipoEvento::PROYECTO_ROVER->value, 403);
        }
        $evento = DB::transaction(function () use ($request, $usuario) {
            $datos = $request->safe()->except(['ramas', 'imagen']);
            $datos['creado_por'] = $usuario->id;
            $datos['requiere_autorizacion'] = $request->boolean('requiere_autorizacion');
            $datos['inscripcion_habilitada'] = $request->boolean('inscripcion_habilitada');
            if ($usuario->es('miembro')) {
                $datos['tipo'] = TipoEvento::PROYECTO_ROVER->value;
                $datos['estado'] = EstadoEvento::PROPUESTO->value;
                $datos['propuesto_por_miembro_id'] = $usuario->miembro_id;
            } elseif ($datos['estado'] === EstadoEvento::PUBLICADO->value) {
                $datos['publicado_por'] = $usuario->id;
            }
            if ($imagen = $this->guardarImagen($request)) {
                $datos['imagen_url'] = $imagen;
            }
            $evento = Evento::create($datos);
            $evento->ramas()->sync($usuario->es('miembro') ? [$usuario->miembro->rama_id] : $request->ramas);
            $this->auditoria->registrar($request, 'crear', 'evento', $evento->id, ['estado' => $evento->estado]);

            return $evento;
        });

        return redirect()->route('eventos.index')->with('estado', 'Evento guardado como '.$evento->estado.'.');
    }

    public function edit(Request $request, Evento $evento)
    {
        $this->authorize('update', $evento);

        return view('panel.evento-form', ['evento' => $evento->load('ramas'), 'ramas' => Rama::orderBy('orden')->get()]);
    }

    public function update(EventoRequest $request, Evento $evento)
    {
        $this->authorize('update', $evento);
        $imagenAnterior = $evento->imagen_url;
        $imagenNueva = $this->guardarImagen($request);
        DB::transaction(function () use ($request, $evento, $imagenNueva) {
            $datos = $request->safe()->except(['ramas', 'imagen']);
            $datos['requiere_autorizacion'] = $request->boolean('requiere_autorizacion');
            $datos['inscripcion_habilitada'] = $request->boolean('inscripcion_habilitada');
            if ($imagenNueva) {
                $datos['imagen_url'] = $imagenNueva;
            }
            if (($datos['estado'] ?? null) === EstadoEvento::PUBLICADO->value) {
                $datos['publicado_por'] = $request->user()->id;
            }
            $evento->update($datos);
            $evento->ramas()->sync($request->ramas);
            $this->auditoria->registrar($request, 'actualizar', 'evento', $evento->id, ['estado' => $evento->estado]);
        });
        if ($imagenNueva && str_starts_with((string) $imagenAnterior, 'storage/')) {
            Storage::disk('public')->delete(Str::after($imagenAnterior, 'storage/'));
        }

        return redirect()->route('eventos.show', $evento)->with('estado', 'Evento actualizado.');
    }

    public function aprobar(Request $request, Evento $evento)
    {
        $this->authorize('review', $evento);
        DB::transaction(function () use ($request, $evento) {
            $bloqueado = Evento::whereKey($evento->id)->lockForUpdate()->firstOrFail();
            abort_unless($bloqueado->estado === EstadoEvento::PROPUESTO->value, 422);
            if ($bloqueado->fecha_inicio->lte(now())) {
                throw ValidationException::withMessages(['evento' => 'No se puede publicar una propuesta cuyo inicio ya pasó.']);
            }
            $bloqueado->update(['estado' => EstadoEvento::PUBLICADO->value,
                'publicado_por' => $request->user()->id, 'motivo_rechazo' => null]);
            $this->auditoria->registrar($request, 'aprobar', 'evento', $bloqueado->id);
        });
        $evento->refresh()->proponente?->usuario?->notify(new EventoResuelto($evento));

        return back()->with('estado', 'Proyecto Rover publicado.');
    }

    public function rechazar(Request $request, Evento $evento)
    {
        $this->authorize('review', $evento);
        $request->validate(['motivo' => 'required|string|max:500']);
        DB::transaction(function () use ($request, $evento) {
            $bloqueado = Evento::whereKey($evento->id)->lockForUpdate()->firstOrFail();
            abort_unless($bloqueado->estado === EstadoEvento::PROPUESTO->value, 422);
            $bloqueado->update(['estado' => EstadoEvento::RECHAZADO->value, 'motivo_rechazo' => $request->motivo]);
            $this->auditoria->registrar($request, 'rechazar', 'evento', $bloqueado->id, ['motivo' => $request->motivo]);
        });
        $evento->refresh()->proponente?->usuario?->notify(new EventoResuelto($evento));

        return back()->with('estado', 'Propuesta rechazada.');
    }

    public function inscribir(Request $request, Evento $evento)
    {
        $this->authorize('register', $evento);
        $usuario = $request->user()->load('miembro');
        abort_unless($usuario->es('miembro') && $usuario->miembro, 403);
        DB::transaction(function () use ($request, $evento, $usuario) {
            $evento = Evento::whereKey($evento->id)->lockForUpdate()->firstOrFail();
            if ($motivo = $evento->motivoNoInscripcion($usuario->miembro)) {
                throw ValidationException::withMessages(['evento' => $motivo]);
            }
            InscripcionEvento::updateOrCreate(['evento_id' => $evento->id, 'miembro_id' => $usuario->miembro_id],
                ['estado' => 'inscrito', 'inscrito_en' => now(), 'cancelado_en' => null]);
            $this->auditoria->registrar($request, 'inscribir', 'evento', $evento->id);
        });

        return back()->with('estado', 'Inscripción confirmada.');
    }

    public function cancelar(Request $request, Evento $evento)
    {
        $this->authorize('register', $evento);
        if ($evento->fecha_inicio->lte(now())) {
            throw ValidationException::withMessages(['evento' => 'No puedes cancelar una inscripción después de iniciado el evento.']);
        }
        $inscripcion = InscripcionEvento::where('evento_id', $evento->id)
            ->where('miembro_id', $request->user()->miembro_id)->firstOrFail();
        $inscripcion->update(['estado' => 'cancelado', 'cancelado_en' => now()]);
        $this->auditoria->registrar($request, 'cancelar_inscripcion', 'evento', $evento->id);

        return back()->with('estado', 'Inscripción cancelada.');
    }

    public function cancelarEvento(Request $request, Evento $evento)
    {
        $this->authorize('update', $evento);
        abort_if(in_array($evento->estado, [EstadoEvento::CANCELADO->value, EstadoEvento::FINALIZADO->value], true), 422);
        $evento->update(['estado' => EstadoEvento::CANCELADO->value, 'inscripcion_habilitada' => false]);
        $this->auditoria->registrar($request, 'cancelar', 'evento', $evento->id);

        return back()->with('estado', 'Evento cancelado y cerrado a nuevas inscripciones.');
    }

    private function guardarImagen(EventoRequest $request): ?string
    {
        if (! $request->hasFile('imagen')) {
            return null;
        }

        return 'storage/'.$request->file('imagen')->store('eventos', 'public');
    }
}
