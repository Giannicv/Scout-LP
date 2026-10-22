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
    document.querySelectorAll('form[data-confirmar]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirmar)) event.preventDefault();
        });
    });
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
