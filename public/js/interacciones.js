document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.menu-toggle, .admin-menu').forEach((button) => {
        button.addEventListener('click', () => {
            const menu = document.getElementById(button.getAttribute('aria-controls'));
            if (!menu) return;
            const open = menu.classList.toggle('open');
            button.setAttribute('aria-expanded', String(open));
        });
    });
    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        document.querySelectorAll('.nav-links.open, .admin-sidebar.open').forEach((menu) => menu.classList.remove('open'));
        document.querySelectorAll('[aria-expanded="true"]').forEach((button) => button.setAttribute('aria-expanded', 'false'));
    });
    const confirmation = document.getElementById('confirmacion-accion');
    if (confirmation && typeof confirmation.showModal === 'function') {
        const title = confirmation.querySelector('[data-confirmacion-titulo]');
        const description = confirmation.querySelector('[data-confirmacion-descripcion]');
        const accept = confirmation.querySelector('[data-confirmacion-aceptar]');
        const cancel = confirmation.querySelector('[data-confirmacion-cancelar]');
        let pendingForm = null;
        let pendingSubmitter = null;
        let previousFocus = null;

        document.querySelectorAll('form[data-confirmar]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                if (form.dataset.confirmacionLista === '1') {
                    delete form.dataset.confirmacionLista;
                    return;
                }

                event.preventDefault();
                pendingForm = form;
                pendingSubmitter = event.submitter || form.querySelector('button[type="submit"], button:not([type]), input[type="submit"]');
                previousFocus = pendingSubmitter || document.activeElement;
                const buttonLabel = pendingSubmitter?.textContent?.trim() || pendingSubmitter?.value || 'Confirmar';
                title.textContent = buttonLabel.replace(/\s+/g, ' ');
                description.textContent = form.dataset.confirmar;
                accept.textContent = title.textContent;
                confirmation.dataset.variante = form.dataset.confirmarVariante || 'normal';
                confirmation.showModal();
                cancel.focus();
            });
        });

        cancel.addEventListener('click', () => confirmation.close());
        confirmation.addEventListener('close', () => {
            const focusTarget = previousFocus;
            pendingForm = null;
            pendingSubmitter = null;
            previousFocus = null;
            if (focusTarget?.isConnected) focusTarget.focus();
        });
        accept.addEventListener('click', () => {
            if (!pendingForm) return;
            const form = pendingForm;
            const submitter = pendingSubmitter;
            pendingForm = null;
            pendingSubmitter = null;
            confirmation.close();
            form.dataset.confirmacionLista = '1';
            if (typeof form.requestSubmit === 'function') {
                if (submitter?.form === form) form.requestSubmit(submitter);
                else form.requestSubmit();
                queueMicrotask(() => delete form.dataset.confirmacionLista);
            } else {
                form.submit();
            }
        });
    }
    const accountType = document.getElementById('tipo_cuenta');
    if (accountType) {
        const updateRegistration = () => document.querySelectorAll('[data-solo-miembro]').forEach((field) => {
            const member = accountType.value === 'miembro';
            field.hidden = !member;
            field.querySelector('input,select').required = member;
        });
        accountType.addEventListener('change', updateRegistration);
        updateRegistration();
    }
    document.querySelectorAll('[data-publicacion-form]').forEach((form) => {
        const state = form.querySelector('[data-publicacion-estado]');
        const featured = form.querySelector('[data-publicacion-destacada]');
        const save = form.querySelector('[data-publicacion-guardar]');
        const remove = form.querySelector('[data-publicacion-quitar]');
        const file = form.querySelector('[data-publicacion-imagen]');
        const url = form.querySelector('[data-publicacion-imagen-url]');
        const syncState = () => {
            const published = state.value === 'publicado';
            featured.disabled = !published;
            if (!published) featured.checked = false;
            save.textContent = published ? 'Guardar y publicar' : 'Guardar borrador';
        };
        state.addEventListener('change', syncState);
        syncState();
        if (remove) {
            file.addEventListener('change', () => { if (file.files.length) remove.checked = false; });
            url.addEventListener('input', () => { if (url.value.trim()) remove.checked = false; });
        }
    });
    const prepareImages = () => document.querySelectorAll('.media img').forEach((image) => {
        const update = () => {
            image.hidden = !image.naturalWidth;
            image.parentElement.classList.toggle('loaded', Boolean(image.naturalWidth));
            image.nextElementSibling?.setAttribute('aria-hidden', String(Boolean(image.naturalWidth)));
        };
        image.addEventListener('load', update);
        image.addEventListener('error', update);
        if (image.complete) update();
    });
    prepareImages();
    const news = document.querySelector('[data-noticias-url]');
    if (news) {
        let category = new URLSearchParams(location.search).get('categoria') || '';
        let limit = Number(document.getElementById('cargar-noticias')?.dataset.limite || 6);
        const load = async () => {
            const response = await fetch(`${news.dataset.noticiasUrl}?${new URLSearchParams({categoria: category, limite: String(limit)})}`, {headers: {Accept: 'application/json'}});
            if (!response.ok) return;
            const result = await response.json();
            document.getElementById('lista-noticias').innerHTML = result.html;
            document.getElementById('cargar-noticias').hidden = limit >= result.total;
            prepareImages();
        };
        document.querySelectorAll('[data-categoria]').forEach((button) => button.addEventListener('click', (event) => {
            event.preventDefault();
            category = button.dataset.categoria;
            limit = 6;
            document.querySelectorAll('[data-categoria]').forEach((item) => item.classList.toggle('active', item === button));
            load();
        }));
        document.getElementById('cargar-noticias')?.addEventListener('click', (event) => {
            event.preventDefault(); limit += 6; load();
        });
    }
});
window.addEventListener('pageshow', (event) => {
    if (event.persisted && document.body.dataset.privado) location.reload();
});
