<h2>
    Decisiones que cuidan.
</h2>
<p>
    Revisa la actividad antes de autorizar la participación.
</p>
<div class="table-scroll">
    <table>
        <thead>
            <tr>
                <th>
                    Hijo/a
                </th>
                <th>
                    Actividad
                </th>
                <th>
                    Estado
                </th>
                <th>
                    Firma
                </th>
                <th>
                    Acciones
                </th>
            </tr>
        </thead>
        <tbody>
            @forelse ($autorizaciones as $autorizacion)
                <tr>
                    <td>
                        {{ $autorizacion->miembro->nombre }}
                        {{ $autorizacion->miembro->apellido }}
                    </td>
                    <td>
                        {{ $autorizacion->actividad->titulo }}
                        <p>
                            {{ $autorizacion->actividad->descripcion }}
                        </p>
                        {{ $autorizacion->actividad->fecha->format('d/m/Y') }}
                        ·
                        {{ $autorizacion->actividad->lugar }}
                    </td>
                    <td>
                        {{ $autorizacion->estado }}
                    </td>
                    <td>
                        {{ $autorizacion->firmada_en?->format('d/m/Y H:i') ?? '—' }}
                    </td>
                    <td>
                        @if ($autorizacion->estado === 'pendiente')
                            <form method="POST"
                                data-confirmar="¿Confirmas esta decisión?"
                                action="{{ route('familia.operacion', ['accion' => 'autorizar']) }}">
                                @csrf
                                <input type="hidden" name="autorizacion_id" value="{{ $autorizacion->id }}">
                                <button name="estado" value="firmada">
                                    Autorizar
                                </button>
                                <button class="danger" name="estado" value="rechazada">
                                    Rechazar
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        Aún no tienes autorizaciones pendientes.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
