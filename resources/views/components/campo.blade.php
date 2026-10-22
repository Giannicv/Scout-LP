@props(['nombre', 'etiqueta', 'tipo' => 'text', 'valor' => '', 'requerido' => false, 'opciones' => []])
@php
    $valorCampo = old($nombre, $valor);
    if ($valorCampo instanceof \DateTimeInterface) {
        $valorCampo = $valorCampo->format($tipo === 'datetime-local' ? 'Y-m-d\TH:i' : 'Y-m-d');
    }
    $esLista = in_array($tipo, ['ramas', 'categorias', 'publicacion', 'mensaje', 'select']);
@endphp
<div class="field">
    <label for="campo-{{ $nombre }}">
        {{ $etiqueta }}
        {{ $requerido ? ' *' : '' }}
    </label>
    @if ($tipo === 'checkbox')
        <input type="hidden" name="{{ $nombre }}" value="0">
        <input
            type="checkbox" id="campo-{{ $nombre }}"
            name="{{ $nombre }}"
            value="1"
            @checked($valorCampo) @required($requerido)>
    @elseif ($tipo === 'textarea')
        <textarea id="campo-{{ $nombre }}" name="{{ $nombre }}" @required($requerido)>{{ $valorCampo }}</textarea>
    @elseif ($esLista)
        <select id="campo-{{ $nombre }}" name="{{ $nombre }}" @required($requerido)>
            <option value="">
                Seleccionar
            </option>
            @foreach ($opcionesCampo as $identificador => $textoOpcion)
                <option value="{{ $identificador }}" @selected((string) $valorCampo === (string) $identificador)>
                    {{ $textoOpcion }}
                </option>
            @endforeach
        </select>
    @else
        <input id="campo-{{ $nombre }}"
            name="{{ $nombre }}"
            type="{{ $tipo }}"
            value="{{ $tipo === 'password' ? '' : $valorCampo }}" @required($requerido)>
    @endif
    @error($nombre)
        <span class="error" role="alert">
            {{ $message }}
        </span>
    @enderror
</div>
