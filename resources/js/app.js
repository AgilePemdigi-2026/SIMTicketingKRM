const adminMobileMenu = document.getElementById(
    'admin-mobile-menu'
);

if (adminMobileMenu) {
    const summary = adminMobileMenu.querySelector('summary');

    document.addEventListener('click', (event) => {
        if (
            adminMobileMenu.open &&
            !adminMobileMenu.contains(event.target)
        ) {
            adminMobileMenu.open = false;
        }
    });

    document.addEventListener('keydown', (event) => {
        if (
            event.key === 'Escape' &&
            adminMobileMenu.open
        ) {
            adminMobileMenu.open = false;
            summary?.focus();
        }
    });

    adminMobileMenu.querySelectorAll('nav a')
        .forEach((link) => {
            link.addEventListener('click', () => {
                adminMobileMenu.open = false;
            });
        });

    const desktop = window.matchMedia(
        '(min-width: 1024px)'
    );

    desktop.addEventListener('change', (event) => {
        if (event.matches) {
            adminMobileMenu.open = false;
        }
    });
}
