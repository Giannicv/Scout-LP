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

        return view('panel.perfil', ['miembro' => $request->user()->miembro->load('grupo', 'rama', 'contactos')]);
    }

    public function guardarContacto(Request $request)
    {
        abort_unless($request->user()->es('miembro'), 403);
        $datos = $request->validate(['nombre' => 'required|string|max:150', 'parentesco' => 'nullable|string|max:80',
            'telefono' => 'required|string|max:30', 'tipo' => 'required|in:tutor,emergencia,ambos']);
        $contacto = $request->user()->miembro->contactos()->create($datos);
        $this->auditoria->registrar($request, 'crear', 'contacto_miembro', $contacto->id);

        return back()->with('estado', 'Contacto guardado.');
    }

    public function eliminarContacto(Request $request, ContactoMiembro $contacto)
    {
        abort_unless($request->user()->es('miembro') && $contacto->miembro_id === $request->user()->miembro_id, 403);
        $id = $contacto->id;
        $contacto->delete();
        $this->auditoria->registrar($request, 'eliminar', 'contacto_miembro', $id);

        return back()->with('estado', 'Contacto eliminado.');
    }
}
