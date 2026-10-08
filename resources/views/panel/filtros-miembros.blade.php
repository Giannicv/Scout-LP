<form class="toolbar" method="GET">
    <input
        type="search"
        name="buscar"
        aria-label="Buscar por nombre" placeholder="Buscar por nombre…"
        value="{{ request('buscar') }}">
    <select name="rama_id" aria-label="Filtrar rama">
        <option value="">
            Todas las ramas
        </option>
        @foreach ($ramas as $rama)
            <option value="{{ $rama->id }}" @selected(request('rama_id') == $rama->id)>
                {{ $rama->nombre }}
            </option>
        @endforeach
    </select>
    <select name="activo" aria-label="Filtrar estado">
        <option value="">
            Todos los estados
        </option>
        <option value="1" @selected(request('activo') === '1')>
            Activo
        </option>
        <option value="0" @selected(request('activo') === '0')>
            Inactivo
        </option>
    </select>
    <button>
        Filtrar
    </button>
</form>
