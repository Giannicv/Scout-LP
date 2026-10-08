<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactoRequest;
use App\Models\ContenidoInstitucional;
use App\Models\Evento;
use App\Models\MensajeContacto;
use App\Models\Noticia;
use Illuminate\Http\Request;

/** Sirve las páginas públicas con datos de PostgreSQL renderizados en Blade. */
class PublicoController extends Controller
{
    public function inicio()
    {
        $noticias = Noticia::where('estado', 'publicado')->where('destacada', true)
            ->with('categoria')->latest('publicado_en')->limit(3)->get();

        return view('inicio', compact('noticias'));
    }

    public function ramas()
    {
        return view('ramas');
    }

    public function noticias(Request $request)
    {
        $limite = min(300, max(6, $request->integer('limite', 6)));
        $consulta = Noticia::where('estado', 'publicado')->with('categoria');
        if ($request->filled('categoria')) {
            $consulta->whereHas('categoria', fn ($categorias) => $categorias->where('nombre',
                $request->categoria));
        }
        $total = $consulta->count();
        $noticias = $consulta->latest('publicado_en')->limit($limite)->get();
        $eventos = Evento::where('estado', 'publicado')->where('fecha_inicio', '>=', now())
            ->orderBy('fecha_inicio')->get();
        if ($request->wantsJson()) {
            return response()->json(['html' => view('partials.lista-noticias', compact('noticias'))->render(),
                'total' => $total, 'limite' => $limite]);
        }

        return view('noticias', compact('noticias', 'total', 'limite', 'eventos'));
    }

    public function noticia(Noticia $noticia)
    {
        abort_unless($noticia->estado === 'publicado', 404);
        $noticia->load(['autor', 'categoria']);
        $relacionadas = Noticia::where('estado', 'publicado')->where('id', '<>', $noticia->id)
            ->latest('publicado_en')->limit(3)->get();

        return view('noticia', compact('noticia', 'relacionadas'));
    }

    public function nosotros()
    {
        $contenidos = ContenidoInstitucional::get()->keyBy('clave');

        return view('nosotros', compact('contenidos'));
    }

    public function contacto(ContactoRequest $request)
    {
        MensajeContacto::create($request->safe()->except('aceptacion'));

        return redirect()->route('mensaje-enviado');
    }
}
