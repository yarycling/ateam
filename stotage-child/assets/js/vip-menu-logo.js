(function () {
    const config = window.ATeamVIPMenuLogo;
    if (!config || !config.enabled || !config.logoUrl) return;

    const selectors = [
        '#pxl-header-elementor .eael-simple-menu',
        '#pxl-header-elementor .eael-simple-menu-responsive',
        '#menu-demo',
        '.pxl-header-menu > ul',
        '.pxl-primary-menu > ul',
        '.main-navigation ul.menu',
        'header nav ul.menu',
        'header .menu'
    ];
    function createItem() {
        const item = document.createElement('li');
        item.className = 'menu-item ateam-vip-menu-logo-item ateam-vip-menu-logo-fallback';

        const link = document.createElement('a');
        link.className = 'ateam-vip-menu-logo-link';
        link.href = config.linkUrl || '/events/';

        const image = document.createElement('img');
        image.src = config.logoUrl;
        image.alt = 'Class VIP Cooler Fete';

        link.appendChild(image);
        item.appendChild(link);

        return item;
    }

    function syncMenus() {
        const menus = selectors.flatMap(function (selector) {
            return Array.from(document.querySelectorAll(selector));
        }).filter(function (menu, index, list) {
            return menu && list.indexOf(menu) === index;
        });

        menus.forEach(function (menu) {
            if (menu.querySelector('.ateam-vip-menu-logo-item')) return;
            menu.appendChild(createItem());
        });
    }

    syncMenus();
    document.addEventListener('DOMContentLoaded', syncMenus);
    window.setTimeout(syncMenus, 400);

    if ('MutationObserver' in window) {
        const observer = new MutationObserver(syncMenus);
        observer.observe(document.documentElement, { childList: true, subtree: true });
    }
})();
