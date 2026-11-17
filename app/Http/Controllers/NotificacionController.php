<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    public function index(Request $request)
    {
        $filtro = in_array($request->query('estado'), ['sin_leer', 'leidas'], true)
            ? $request->query('estado') : 'todas';
        $usuario = $request->user();
        $consulta = $usuario->notifications();
        if ($filtro === 'sin_leer') {
            $consulta->whereNull('read_at');
        } elseif ($filtro === 'leidas') {
            $consulta->whereNotNull('read_at');
        }

        return view('panel.notificaciones', [
            'notificaciones' => $consulta->orderByDesc('created_at')->paginate(15)->withQueryString(),
            'total' => $usuario->notifications()->count(),
            'sinLeer' => $usuario->unreadNotifications()->count(),
            'filtro' => $filtro,
        ]);
    }

    public function leer(Request $request, string $notificacion)
    {
        $registro = $request->user()->notifications()->findOrFail($notificacion);
        $registro->markAsRead();

        return back()->with('estado', 'Notificación marcada como leída.');
    }

    public function leerTodas(Request $request)
    {
        $cantidad = $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return redirect()->route('notificaciones.index', ['estado' => 'leidas'])
            ->with('estado', match (true) {
                $cantidad === 0 => 'No había notificaciones pendientes.',
                $cantidad === 1 => '1 notificación marcada como leída.',
                default => "$cantidad notificaciones marcadas como leídas.",
            });
    }

    public function abrir(Request $request, string $notificacion)
    {
        $registro = $request->user()->notifications()->findOrFail($notificacion);
        $registro->markAsRead();
        $destino = $registro->data['url'] ?? null;

        if (! is_string($destino) || ! $this->esDestinoInterno($request, $destino)) {
            return redirect()->route('notificaciones.index');
        }

        return redirect()->to($destino);
    }

    private function esDestinoInterno(Request $request, string $destino): bool
    {
        if (str_contains($destino, '\\') || preg_match('/[\x00-\x1F\x7F]/', $destino)) {
            return false;
        }
        if (str_starts_with($destino, '/') && ! str_starts_with($destino, '//')) {
            return true;
        }
        $partes = parse_url($destino);
        if (! is_array($partes) || ! in_array($partes['scheme'] ?? null, ['http', 'https'], true)) {
            return false;
        }

        return ($partes['host'] ?? null) === $request->getHost()
            && (int) ($partes['port'] ?? ($partes['scheme'] === 'https' ? 443 : 80)) === (int) ($request->getPort());
    }
}
