import './bootstrap';

const sidebarToggle = document.getElementById('appSidebarToggle');
const sidebar = document.getElementById('appSidebar');
const sidebarBackdrop = document.getElementById('appSidebarBackdrop');
const mobileSidebar = window.matchMedia('(max-width: 700px)');

function setSidebarOpen(isOpen) {
    if (!sidebar || !sidebarToggle) return;

    const wasOpen = sidebar.classList.contains('mobile-open');
    const shouldOpen = isOpen && mobileSidebar.matches;
    sidebar.classList.toggle('mobile-open', shouldOpen);
    sidebar.setAttribute('aria-hidden', String(mobileSidebar.matches && !shouldOpen));
    sidebar.inert = mobileSidebar.matches && !shouldOpen;
    sidebarToggle.setAttribute('aria-expanded', String(shouldOpen));
    sidebarToggle.setAttribute('aria-label', shouldOpen ? 'Fechar menu' : 'Abrir menu');
    sidebarToggle.querySelector('span').textContent = shouldOpen ? '×' : '☰';
    sidebarBackdrop?.classList.toggle('is-visible', shouldOpen);
    if (sidebarBackdrop) sidebarBackdrop.hidden = !shouldOpen;
    document.body.classList.toggle('sidebar-open', shouldOpen);

    if (shouldOpen) {
        sidebar.querySelector('.sidebar-link')?.focus({ preventScroll: true });
    } else if (wasOpen && mobileSidebar.matches) {
        sidebarToggle.focus({ preventScroll: true });
    }
}

sidebarToggle?.addEventListener('click', () => {
    setSidebarOpen(!sidebar.classList.contains('mobile-open'));
});

sidebarBackdrop?.addEventListener('click', () => setSidebarOpen(false));
sidebar?.querySelectorAll('.sidebar-link, .app-brand').forEach((link) => {
    link.addEventListener('click', () => setSidebarOpen(false));
});

sidebar?.addEventListener('keydown', (event) => {
    if (event.key !== 'Tab' || !sidebar.classList.contains('mobile-open')) return;

    const focusable = [...sidebar.querySelectorAll('a[href], button:not([disabled])')];
    const first = focusable[0];
    const last = focusable[focusable.length - 1];

    if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last?.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first?.focus();
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && sidebar?.classList.contains('mobile-open')) {
        setSidebarOpen(false);
    }
});

mobileSidebar.addEventListener('change', () => setSidebarOpen(false));
setSidebarOpen(false);
