<h2>
    Todo al día.
</h2>
<p>
    Registro de comprobantes de demostración; no se procesa dinero.
</p>
<div class="table-scroll">
    <table>
        <thead>
            <tr>
                <th>
                    Hijo/a
                </th>
                <th>
                    Concepto
                </th>
                <th>
                    Monto
                </th>
                <th>
                    Vencimiento
                </th>
                <th>
                    Estado
                </th>
                <th>
                    Comprobante
                </th>
            </tr>
        </thead>
        <tbody>
            @forelse ($cuotas as $cuota)
                <tr>
                    <td>
                        {{ $cuota->miembro->nombre }}
                    </td>
                    <td>
                        {{ $cuota->concepto }}
                    </td>
                    <td>
                        Bs
                        {{ $cuota->monto }}
                    </td>
                    <td>
                        {{ $cuota->vencimiento->format('d/m/Y') }}
                    </td>
                    <td>
                        {{ $cuota->estado }}
                    </td>
                    <td>
                        @if (in_array($cuota->estado, ['pendiente', 'vencido']))
                            <form method="POST" action="{{ route('familia.operacion', ['accion' => 'pagar']) }}">
                                @csrf
                                <input type="hidden" name="cuota_id" value="{{ $cuota->id }}">
                                <x-campo nombre="comprobante_nombre" etiqueta="Nombre del comprobante (simulado)" requerido />
                                <button>
                                    Registrar pago
                                </button>
                            </form>
                        @else
                            {{ $cuota->comprobante_nombre ?? '—' }}
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        No hay cuotas registradas.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
