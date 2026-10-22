@if (session('estado'))
    <p class="flash-success" role="status">
        {{ session('estado') }}
    </p>
@endif
@if ($errors->any())
    <div class="validation-summary" role="alert" tabindex="-1">
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
