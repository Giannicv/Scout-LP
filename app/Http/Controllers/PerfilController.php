<?php

namespace App\Http\Controllers;

use App\Models\ContactoMiembro;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;

class PerfilController extends Controller
{
    public function __construct(private AuditoriaService $auditoria) {}

    public function show(Request $request)
    {
        abort_unless($request->user()->es('miembro'), 403);
        $miembro = $request->user()->miembro;
        abort_unless($miembro, 404);

        return view('panel.perfil', [
            'miembro' => $miembro->load(['grupo', 'rama', 'contactos' => fn ($q) => $q->orderBy('nombre')]),
            'usuario' => $request->user(),
        ]);
    }

    public function actualizarTelefono(Request $request)
    {
        abort_unless($request->user()->es('miembro') && $request->user()->miembro_id, 403);
        $datos = $request->validate(['telefono' => 'nullable|string|max:30']);
        $request->user()->miembro->update(['telefono' => $datos['telefono'] ?? null]);
        $this->auditoria->registrar($request, 'actualizar_telefono', 'miembro', $request->user()->miembro_id);

        return back()->with('estado', 'Teléfono actualizado.');
    }

    public function guardarContacto(Request $request)
    {
        abort_unless($request->user()->es('miembro') && $request->user()->miembro_id, 403);
        $datos = $this->validarContacto($request);
        $contacto = $request->user()->miembro->contactos()->create($datos);
        $this->auditoria->registrar($request, 'crear', 'contacto_miembro', $contacto->id);

        return back()->with('estado', 'Contacto guardado.');
    }

    public function actualizarContacto(Request $request, ContactoMiembro $contacto)
    {
        abort_unless($request->user()->es('miembro') && $contacto->miembro_id === $request->user()->miembro_id, 403);
        $datos = $this->validarContacto($request);
        $contacto->update($datos);
        $this->auditoria->registrar($request, 'actualizar', 'contacto_miembro', $contacto->id);

        return back()->with('estado', 'Contacto actualizado.');
    }

    public function eliminarContacto(Request $request, ContactoMiembro $contacto)
    {
        abort_unless($request->user()->es('miembro') && $contacto->miembro_id === $request->user()->miembro_id, 403);
        $id = $contacto->id;
        $contacto->delete();
        $this->auditoria->registrar($request, 'eliminar', 'contacto_miembro', $id);

        return back()->with('estado', 'Contacto eliminado.');
    }

    private function validarContacto(Request $request): array
    {
        return $request->validate(['nombre' => 'required|string|max:150', 'parentesco' => 'nullable|string|max:80',
            'telefono' => 'required|string|max:30', 'tipo' => 'required|in:tutor,emergencia,ambos']);
    }
}
