import './bootstrap';

const sidebarToggle = document.getElementById('appSidebarToggle');
const sidebar = document.getElementById('appSidebar');

sidebarToggle?.addEventListener('click', () => {
    const isOpen = sidebar.classList.toggle('mobile-open');
    sidebarToggle.setAttribute('aria-expanded', String(isOpen));
});

document.addEventListener('click', (event) => {
    if (!sidebar || !sidebarToggle || window.innerWidth > 700 || !sidebar.classList.contains('mobile-open')) {
        return;
    }

    if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
        sidebar.classList.remove('mobile-open');
        sidebarToggle.setAttribute('aria-expanded', 'false');
    }
});
