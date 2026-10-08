<h2>
    Así soy yo.
</h2>
<p>
    Actualiza tu contacto y tu lema personal.
</p>
@php($miembroPerfil = $miembros->first())
@if ($miembroPerfil)
    <div class="grid two">
        <section class="panel">
            <form method="POST" action="{{ route('miembro.operacion', ['accion' => 'perfil']) }}">
                @csrf
                <x-imagen :ruta="$miembroPerfil->foto" :descripcion="$usuario->nombre" clase="profile-photo" />
                <x-campo nombre="nombre" etiqueta="Nombre" :valor="$usuario->nombre" requerido />
                <x-campo nombre="apellido" etiqueta="Apellidos" :valor="$usuario->apellido" requerido />
                <x-campo nombre="email" etiqueta="Correo electrónico" tipo="email"
                    :valor="$usuario->email" requerido />
                <x-campo nombre="telefono" etiqueta="Teléfono" :valor="$usuario->telefono" />
                <x-campo nombre="foto" etiqueta="Foto (URL https:// o ruta img/)" :valor="$miembroPerfil->foto" />
                <x-campo nombre="lema" etiqueta="Mi lema personal" :valor="$miembroPerfil->lema" />
                <button>
                    Guardar perfil
                </button>
            </form>
        </section>
        <section class="panel">
            <h2>
                Cambiar contraseña
            </h2>
            <form method="POST" action="{{ route('password.cambiar') }}">
                @csrf
                <x-campo nombre="actual" etiqueta="Contraseña actual" tipo="password" requerido />
                <x-campo nombre="password" etiqueta="Nueva contraseña" tipo="password" requerido />
                <x-campo nombre="password_confirmation" etiqueta="Confirmar contraseña" tipo="password" requerido />
                <button>
                    Cambiar contraseña
                </button>
            </form>
        </section>
    </div>
@else
    <p>
        Pide al administrador que vincule tu ficha de miembro.
    </p>
@endif
