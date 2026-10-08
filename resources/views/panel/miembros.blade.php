<h2>
    Tu equipo, cerca.
</h2>
@if ($rol !== 'miembro')
    @include('panel.filtros-miembros')
    @if ($rol === 'coordinador')
        <p>
            <a class="btn secondary" href="{{ route('coordinador.exportar', request()->query()) }}">
                Exportar CSV
            </a>
        </p>
    @endif
@endif
<div class="table-scroll">
    <table>
        <thead>
            <tr>
                <th>
                    Nombre
                </th>
                <th>
                    Rama / rol
                </th>
                <th>
                    Unidad
                </th>
                <th>
                    Estado
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rol === 'miembro' ? $companeros : $miembros as $miembro)
                <tr>
                    <td>
                        {{ $miembro->nombre }}
                        {{ $miembro->apellido }}
                    </td>
                    <td>
                        {{ $rol === 'miembro' ? $miembro->rol_patrulla : $miembro->rama?->nombre }}
                    </td>
                    <td>
                        {{ $rol === 'miembro' ? $unidades->first()?->nombre : $miembro->unidad?->nombre }}
                    </td>
                    <td>
                        {{ $rol === 'miembro' ? '—' : ($miembro->activo ? 'Activo' : 'Inactivo') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
