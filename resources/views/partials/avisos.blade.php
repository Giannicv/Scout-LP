@if (session('estado'))
    <p class="panel" role="status">
        {{ session('estado') }}
    </p>
@endif
@if ($errors->any())
    <div class="panel" role="alert">
        <p class="error">
            Revisa los datos del formulario.
        </p>
        @foreach ($errors->all() as $mensajeError)
            <p class="error">
                {{ $mensajeError }}
            </p>
        @endforeach
    </div>
@endif
