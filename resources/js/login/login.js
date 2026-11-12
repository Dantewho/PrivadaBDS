let activeNavigationUrl = null;
let lastNavigationUrl = null;
let lastNavigationAt = 0;

const navigateTo = async (url, { addHistory = true } = {}) => {
    const content = document.querySelector('#pageContent');
    if (!content) return window.location.assign(url);

    const destination = new URL(url, location.href).href;
    const now = Date.now();
    if (activeNavigationUrl || (lastNavigationUrl === destination && now - lastNavigationAt < 750)) return;

    activeNavigationUrl = destination;
    lastNavigationUrl = destination;
    lastNavigationAt = now;
    content.classList.add('is-loading');
    try {
        const response = await fetch(destination, { headers: { Accept: 'text/html', 'X-Requested-With': 'XMLHttpRequest' } });
        if (!response.ok) throw new Error('Navigation failed');
        const doc = new DOMParser().parseFromString(await response.text(), 'text/html');
        const next = doc.querySelector('#pageContent');
        if (!next) throw new Error('Page content missing');
        content.innerHTML = next.innerHTML;
        document.title = doc.title;
        if (addHistory) history.pushState({}, '', destination);
        document.querySelectorAll('.admin-nav a').forEach((a) => a.classList.toggle('active', new URL(a.href).pathname === new URL(destination).pathname));
        document.querySelector('#adminSidebar')?.classList.remove('is-open');
        scrollTo({ top: 0, behavior: 'smooth' });
    } catch {
        location.assign(destination);
    } finally {
        if (activeNavigationUrl === destination) activeNavigationUrl = null;
        content.classList.remove('is-loading');
    }
};

const closeModal = (modal) => {
    if (!modal) return;
    modal.classList.remove('is-open'); modal.setAttribute('aria-hidden', 'true');
    if (!document.querySelector('.modal-backdrop.is-open')) document.body.classList.remove('modal-open');
};

document.addEventListener('click', (event) => {
    const clearFilters = event.target.closest('[data-clear-filters]');
    if (clearFilters) {
        const form = clearFilters.closest('[data-admin-filter]');
        if (form) navigateTo(new URL(form.action, location.origin).href);
        return;
    }

    const link = event.target.closest('a[href]');
    if (link && document.querySelector('#pageContent')) {
        const dest = new URL(link.href, location.href);
        if (event.button === 0 && !event.metaKey && !event.ctrlKey && !event.shiftKey && dest.origin === location.origin && dest.pathname.startsWith('/administrador/')) { event.preventDefault(); navigateTo(dest.href); return; }
    }
    const toggle = event.target.closest('[data-password-toggle]');
    if (toggle) { const input = document.querySelector('#password'); if (input) input.type = input.type === 'password' ? 'text' : 'password'; return; }
    const opener = event.target.closest('[data-modal-open]');
    if (opener) {
        const modal = document.getElementById(opener.dataset.modalOpen); if (!modal) return;
        const uf = modal.querySelector('[data-edit-user-form]');
        if (uf) { uf.action = opener.dataset.url; ['name','email','rol','estado'].forEach(k => uf.elements[k].value = opener.dataset[k] || ''); uf.elements.vivienda_id.value = opener.dataset.vivienda || ''; uf.elements.es_propietario.checked = opener.dataset.owner === '1'; const resetForm = modal.querySelector('[data-reset-password-form]'); if (resetForm) resetForm.action = opener.dataset.resetUrl || ''; }
        const hf = modal.querySelector('[data-edit-home-form]');
        if (hf) { hf.action = opener.dataset.url; ['numero','direccion','estado'].forEach(k => hf.elements[k].value = opener.dataset[k] || ''); }
        const cf = modal.querySelector('[data-edit-card-form]');
        if (cf) { cf.action = opener.dataset.url; cf.elements.numero_tarjeta.value = opener.dataset.number || ''; cf.elements.estado.value = opener.dataset.state || 'pendiente'; }
        modal.classList.add('is-open'); modal.setAttribute('aria-hidden','false'); document.body.classList.add('modal-open'); modal.querySelector('input,select,button')?.focus(); return;
    }
    const closer = event.target.closest('[data-modal-close]'); if (closer) { closeModal(closer.closest('.modal-backdrop')); return; }
    if (event.target.classList?.contains('modal-backdrop')) closeModal(event.target);
    if (event.target.closest('[data-sidebar-toggle]')) document.querySelector('#adminSidebar')?.classList.toggle('is-open');
});

document.addEventListener('submit', (event) => {
    if (event.defaultPrevented) return;
    const form = event.target.closest('[data-admin-filter]');
    if (form && document.querySelector('#pageContent')) { event.preventDefault(); const url = new URL(form.action, location.origin); new FormData(form).forEach((v,k) => v && url.searchParams.set(k,v)); navigateTo(url.href); }
});

document.addEventListener('submit', (event) => {
    if (event.defaultPrevented || !(event.target instanceof HTMLFormElement)) return;

    const form = event.target;
    if (form.dataset.requestPending === 'true') {
        event.preventDefault();
        return;
    }

    form.dataset.requestPending = 'true';
    form.setAttribute('aria-busy', 'true');
    const submitters = [...form.querySelectorAll('button:not([type]), button[type="submit"], input[type="submit"]')];
    if (event.submitter instanceof HTMLElement && !submitters.includes(event.submitter)) submitters.push(event.submitter);

    submitters.forEach((button) => {
        if (button.disabled) return;
        if (button.dataset.processingText) {
            button.dataset.requestOriginalText = button.textContent;
            button.textContent = button.dataset.processingText;
        }
        button.disabled = true;
        button.dataset.requestLocked = 'true';
    });
});

window.addEventListener('pageshow', () => {
    document.querySelectorAll('[data-request-locked="true"]').forEach((button) => {
        button.disabled = false;
        delete button.dataset.requestLocked;
        if (button.dataset.requestOriginalText !== undefined) {
            button.textContent = button.dataset.requestOriginalText;
            delete button.dataset.requestOriginalText;
        }
    });
    document.querySelectorAll('form[data-request-pending="true"]').forEach((form) => {
        delete form.dataset.requestPending;
        form.removeAttribute('aria-busy');
    });
});
document.addEventListener('input', (event) => {
    const input = event.target.closest('[data-admin-filter] input[type="search"]'); if (!input) return;
    clearTimeout(input.searchTimeout); input.searchTimeout = setTimeout(() => input.form.requestSubmit(), 450);
});
document.addEventListener('keydown', (event) => { if (event.key === 'Escape') document.querySelectorAll('.modal-backdrop.is-open').forEach(closeModal); });
window.addEventListener('popstate', () => { if (location.pathname.startsWith('/administrador/')) navigateTo(location.href, { addHistory: false }); });
