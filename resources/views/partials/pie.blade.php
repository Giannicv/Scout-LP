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
                    Historias y noticias
                </a>
                <a href="{{ route('login') }}">
                    Acceso Scout
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
                <h3>Distrito La Paz</h3>
                <span>Información institucional e historias del movimiento.</span>
                <p>
                    Siempre listos para servir.
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
        El sitio protege los datos de acceso y la información interna de sus miembros.
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
        Sitio de demostración. Verifica las noticias antes de una publicación oficial.
    </p>
    <button data-cerrar-dialogo>
        Cerrar
    </button>
</dialog>
