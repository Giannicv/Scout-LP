<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div>
                <x-marca />
                <p>
                    Un mundo mejor empieza
                    <br>
                    con una pequeña aventura.
                </p>
                <div class="socials">
                    @foreach (['facebook', 'instagram', 'youtube'] as $redSocial)
                        <a href="{{ route('contacto') }}#contacto" aria-label="Consultar nuestra red {{ $redSocial }}">
                            <x-icono :nombre="$redSocial" />
                        </a>
                    @endforeach
                </div>
            </div>
            <div>
                <h3>
                    Explorar
                </h3>
                <a href="{{ route('inicio') }}">
                    Inicio
                </a>
                <a href="{{ route('nosotros') }}">
                    Nosotros
                </a>
                <a href="{{ route('noticias.index') }}">
                    Noticias y eventos
                </a>
                <a href="{{ route('login') }}">
                    Acceso administrador
                </a>
            </div>
            <div>
                <h3>
                    Tu próxima aventura
                </h3>
                @foreach ($ramasPublicas as $rama)
                    <a href="{{ route('ramas') }}#{{ $rama->slug }}">
                        {{ $rama->nombre }}
                    </a>
                @endforeach
            </div>
            <div>
                <h3>
                    Hablemos
                </h3>
                <a href="{{ route('contacto') }}#contacto">
                    La Paz, Bolivia
                </a>
                <span>
                    Correo y teléfono: por confirmar
                </span>
                <p>
                    Acércate al movimiento.
                    <br>
                    Encuentra tu lugar.
                </p>
            </div>
        </div>
        <div class="footer-bottom">
            <span>
                © 2026 Asociación de Scouts de Bolivia. Todos los derechos reservados.
            </span>
            <span>
                <button data-abrir-dialogo="privacidad">
                    Privacidad
                </button>
                <button data-abrir-dialogo="terminos">
                    Términos
                </button>
            </span>
        </div>
    </div>
</footer>
<dialog id="privacidad">
    <h2>
        Privacidad
    </h2>
    <p>
        El formulario guarda la consulta en la base PostgreSQL del servidor.
    </p>
    <button data-cerrar-dialogo>
        Cerrar
    </button>
</dialog>
<dialog id="terminos">
    <h2>
        Términos
    </h2>
    <p>
        Sitio de demostración. Verifica noticias, eventos y datos de contacto antes de una publicación oficial.
    </p>
    <button data-cerrar-dialogo>
        Cerrar
    </button>
</dialog>
