(() => {
    const clave = 'scoutbo-tema';
    const sistemaOscuro = window.matchMedia('(prefers-color-scheme: dark)');

    const preferenciaGuardada = () => {
        try {
            return localStorage.getItem(clave);
        } catch {
            return null;
        }
    };

    const aplicar = (tema) => {
        document.documentElement.dataset.tema = tema;
        const oscuro = tema === 'oscuro';
        document.querySelector('meta[name="theme-color"]')?.setAttribute('content', oscuro ? '#151119' : '#52307f');
        document.querySelectorAll('[data-tema-toggle]').forEach((boton) => {
            boton.setAttribute('aria-pressed', String(oscuro));
            boton.setAttribute('aria-label', oscuro ? 'Usar tema claro' : 'Usar tema oscuro');
            boton.setAttribute('title', oscuro ? 'Usar tema claro' : 'Usar tema oscuro');
        });
    };

    aplicar(preferenciaGuardada() || (sistemaOscuro.matches ? 'oscuro' : 'claro'));

    document.addEventListener('DOMContentLoaded', () => {
        aplicar(document.documentElement.dataset.tema);
        document.querySelectorAll('[data-tema-toggle]').forEach((boton) => boton.addEventListener('click', () => {
            const tema = document.documentElement.dataset.tema === 'oscuro' ? 'claro' : 'oscuro';
            try {
                localStorage.setItem(clave, tema);
            } catch {
                // El tema sigue funcionando aunque el navegador bloquee el almacenamiento.
            }
            aplicar(tema);
        }));
    });

    sistemaOscuro.addEventListener('change', (evento) => {
        if (!preferenciaGuardada()) aplicar(evento.matches ? 'oscuro' : 'claro');
    });
})();
