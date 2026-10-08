/* Interacciones accesibles; los datos y los permisos se resuelven en Laravel. */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.menu-toggle, .admin-menu').forEach((botonMenu) => {
        botonMenu.addEventListener('click', () => {
            const menu = document.getElementById(botonMenu.getAttribute('aria-controls'));
            const abierto = menu.classList.toggle('open');
            botonMenu.setAttribute('aria-expanded', String(abierto));
        });
    });
    document.addEventListener('keydown', (eventoTeclado) => {
        if (eventoTeclado.key === 'Escape') {
            document.querySelectorAll('.nav-links.open, .admin-sidebar.open').forEach((menu) => {
                menu.classList.remove('open');
            });
            document.querySelectorAll('[aria-expanded="true"]').forEach((botonMenu) => {
                botonMenu.setAttribute('aria-expanded', 'false');
            });
        }
    });
    document.querySelectorAll('[data-abrir-dialogo]').forEach((botonDialogo) => {
        botonDialogo.addEventListener('click', () => {
            document.getElementById(botonDialogo.dataset.abrirDialogo).showModal();
        });
    });
    document.querySelectorAll('[data-cerrar-dialogo]').forEach((botonCerrar) => {
        botonCerrar.addEventListener('click', () => {
            botonCerrar.closest('dialog').close();
        });
    });
    document.querySelectorAll('form[data-confirmar]').forEach((formulario) => {
        formulario.addEventListener('submit', (eventoEnvio) => {
            if (!window.confirm(formulario.dataset.confirmar)) {
                eventoEnvio.preventDefault();
            }
        });
    });
    function prepararImagenes() {
        document.querySelectorAll('.media img').forEach((imagen) => {
            const actualizarImagen = () => {
                imagen.hidden = !imagen.naturalWidth;
                imagen.parentElement.classList.toggle('loaded', Boolean(imagen.naturalWidth));
                imagen.nextElementSibling.setAttribute('aria-hidden', String(Boolean(imagen.naturalWidth)));
            };
            imagen.addEventListener('load', actualizarImagen);
            imagen.addEventListener('error', actualizarImagen);
            if (imagen.complete) {
                actualizarImagen();
            }
        });
    }
    prepararImagenes();
    document.querySelector('[data-copiar-enlace]')?.addEventListener('click', async (eventoClic) => {
        const botonCopiar = eventoClic.currentTarget;
        try {
            await navigator.clipboard.writeText(location.href);
            botonCopiar.textContent = 'Enlace copiado';
        } catch (error) {
            window.prompt('Copia el enlace:', location.href);
        }
    });
    document.querySelectorAll('[data-departamento]').forEach((enlaceDepartamento) => {
        enlaceDepartamento.addEventListener('click', () => {
            document.querySelector('[name="mensaje"]').value =
                `Hola, quisiera conocer los grupos scouts de ${enlaceDepartamento.dataset.departamento}. `;
        });
    });
    document.querySelectorAll('[data-vista-calendario]').forEach((botonVista) => {
        botonVista.addEventListener('click', () => {
            const mostrarMes = botonVista.dataset.vistaCalendario === 'mes';
            document.getElementById('calendario-mes').hidden = !mostrarMes;
            document.getElementById('calendario-lista').hidden = mostrarMes;
        });
    });
    const bloqueNoticias = document.querySelector('[data-noticias-url]');
    if (bloqueNoticias) {
        let categoria = new URLSearchParams(location.search).get('categoria') || '';
        let limite = Number(document.getElementById('cargar-noticias').dataset.limite);
        let secuenciaConsulta = 0;
        async function cargarNoticias() {
            const consultaActual = ++secuenciaConsulta;
            const parametros = new URLSearchParams({ categoria, limite: String(limite) });
            try {
                const respuesta = await fetch(`${bloqueNoticias.dataset.noticiasUrl}?${parametros}`, {
                    headers: { Accept: 'application/json' },
                });
                if (!respuesta.ok) {
                    throw new Error('No se pudieron cargar las noticias.');
                }
                const resultado = await respuesta.json();
                if (consultaActual !== secuenciaConsulta) {
                    return;
                }
                // HTML producido y escapado por las plantillas Blade del servidor.
                document.getElementById('lista-noticias').innerHTML = resultado.html;
                document.getElementById('cargar-noticias').hidden = limite >= resultado.total;
                prepararImagenes();
            } catch (error) {
                window.alert(error.message);
            }
        }
        document.querySelectorAll('[data-categoria]').forEach((botonCategoria) => {
            botonCategoria.addEventListener('click', (eventoClic) => {
                eventoClic.preventDefault();
                categoria = botonCategoria.dataset.categoria;
                limite = 6;
                cargarNoticias();
            });
        });
        document.getElementById('cargar-noticias').addEventListener('click', (eventoClic) => {
            eventoClic.preventDefault();
            limite += 6;
            cargarNoticias();
        });
    }
    const formularioInscripcion = document.querySelector('[data-inscripcion]');
    if (formularioInscripcion) {
        const pasos = [...formularioInscripcion.querySelectorAll('[data-paso]')];
        const botonAnterior = formularioInscripcion.querySelector('[data-paso-anterior]');
        const botonSiguiente = formularioInscripcion.querySelector('[data-paso-siguiente]');
        const botonEnviar = formularioInscripcion.querySelector('[data-enviar-inscripcion]');
        let pasoActual = 0;
        function mostrarPaso() {
            pasos.forEach((paso, posicionPaso) => {
                paso.hidden = posicionPaso !== pasoActual;
            });
            document.querySelectorAll('[data-indicador-paso]').forEach((indicador) => {
                indicador.classList.toggle('current', Number(indicador.dataset.indicadorPaso) === pasoActual);
            });
            botonAnterior.hidden = pasoActual === 0;
            botonSiguiente.hidden = pasoActual === pasos.length - 1;
            botonEnviar.hidden = pasoActual !== pasos.length - 1;
            const edad = Number(formularioInscripcion.elements.edad.value);
            const rama = edad <= 10 ? 'Manada' : edad <= 14 ? 'Tropa' : edad <= 17 ? 'Comunidad' : 'Clan';
            formularioInscripcion.querySelector('[data-rama-sugerida]').textContent = `${rama} · ${edad} años`;
            formularioInscripcion.querySelector('[data-resumen-inscripcion]').textContent =
                `${formularioInscripcion.elements.nombre.value} ${formularioInscripcion.elements.apellido.value}, ` +
                `${edad} años. ${rama}. ${formularioInscripcion.elements.departamento.value}.`;
        }
        botonSiguiente.addEventListener('click', () => {
            for (const campo of pasos[pasoActual].querySelectorAll('input, select, textarea')) {
                if (!campo.reportValidity()) {
                    return;
                }
            }
            pasoActual += 1;
            mostrarPaso();
        });
        botonAnterior.addEventListener('click', () => {
            pasoActual -= 1;
            mostrarPaso();
        });
        mostrarPaso();
    }
    if (!matchMedia('(prefers-reduced-motion: reduce)').matches) {
        const observador = new IntersectionObserver((entradas) => {
            entradas.forEach((entrada) => {
                if (!entrada.isIntersecting) {
                    return;
                }
                const elemento = entrada.target;
                const total = Number(elemento.dataset.count);
                const inicio = performance.now();
                function animarContador(instante) {
                    const avance = Math.min((instante - inicio) / 1200, 1);
                    elemento.textContent = Math.round(total * (1 - Math.pow(1 - avance, 3))).toLocaleString('es-BO');
                    if (total === 7900) {
                        elemento.textContent += '+';
                    }
                    if (avance < 1) {
                        requestAnimationFrame(animarContador);
                    }
                }
                requestAnimationFrame(animarContador);
                observador.unobserve(elemento);
            });
        });
        document.querySelectorAll('[data-count]').forEach((contador) => {
            observador.observe(contador);
        });
    }
});
window.addEventListener('pageshow', (eventoPagina) => {
    if (eventoPagina.persisted && document.body.dataset.privado) {
        location.reload();
    }
});
