<h2>
    Personas que acompañan.
</h2>
<div class="table-scroll">
    <table>
        <thead>
            <tr>
                <th>
                    Dirigente
                </th>
                <th>
                    Correo
                </th>
                <th>
                    Teléfono
                </th>
                <th>
                    Unidades
                </th>
            </tr>
        </thead>
        <tbody>
            @forelse ($dirigentes as $dirigente)
                <tr>
                    <td>
                        {{ $dirigente->nombre }}
                        {{ $dirigente->apellido }}
                    </td>
                    <td>
                        {{ $dirigente->email }}
                    </td>
                    <td>
                        {{ $dirigente->telefono }}
                    </td>
                    <td>
                        {{ $unidades->where('dirigente_id', $dirigente->id)->pluck('nombre')->join(', ') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">
                        Aún no hay dirigentes.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<p>
    <a class="btn secondary" href="{{ route('coordinador.unidades') }}">
        Asignar dirigentes
    </a>
</p>
