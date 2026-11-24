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

const adminNotificationMenu = document.getElementById(
    'admin-notification-menu'
);

if (adminNotificationMenu) {
    document.addEventListener('click', (event) => {
        if (
            adminNotificationMenu.open &&
            !adminNotificationMenu.contains(event.target)
        ) {
            adminNotificationMenu.open = false;
        }
    });

    document.addEventListener('keydown', (event) => {
        if (
            event.key === 'Escape' &&
            adminNotificationMenu.open
        ) {
            adminNotificationMenu.open = false;
            adminNotificationMenu.querySelector('summary')?.focus();
        }
    });

    adminNotificationMenu.addEventListener('toggle', () => {
        if (adminNotificationMenu.open && adminMobileMenu) {
            adminMobileMenu.open = false;
        }
    });
}

const adminProfileMenu = document.getElementById(
    'admin-profile-menu'
);

if (adminProfileMenu) {
    document.addEventListener('click', (event) => {
        if (
            adminProfileMenu.open &&
            !adminProfileMenu.contains(event.target)
        ) {
            adminProfileMenu.open = false;
        }
    });

    document.addEventListener('keydown', (event) => {
        if (
            event.key === 'Escape' &&
            adminProfileMenu.open
        ) {
            adminProfileMenu.open = false;
            adminProfileMenu.querySelector('summary')?.focus();
        }
    });

    adminProfileMenu.addEventListener('toggle', () => {
        if (adminProfileMenu.open) {
            if (adminNotificationMenu) {
                adminNotificationMenu.open = false;
            }

            if (adminMobileMenu) {
                adminMobileMenu.open = false;
            }
        }
    });

    adminNotificationMenu?.addEventListener('toggle', () => {
        if (adminNotificationMenu.open) {
            adminProfileMenu.open = false;
        }
    });

    adminMobileMenu?.addEventListener('toggle', () => {
        if (adminMobileMenu.open) {
            adminProfileMenu.open = false;

            if (adminNotificationMenu) {
                adminNotificationMenu.open = false;
            }
        }
    });
}
