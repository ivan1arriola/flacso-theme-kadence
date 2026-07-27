(function () {
    'use strict';

    var header = document.querySelector('[data-flacso-header]');
    if (!header) return;

    var menu = header.querySelector('[data-flacso-menu]');
    var menuToggle = header.querySelector('[data-flacso-menu-toggle]');
    var menuClose = header.querySelector('[data-flacso-menu-close]');
    var menuOverlay = header.querySelector('[data-flacso-menu-overlay]');
    var search = header.querySelector('[data-flacso-search]');
    var searchToggle = header.querySelector('[data-flacso-search-toggle]');
    var searchField = header.querySelector('#flacso-header-search-field');

    function closeMenu(returnFocus) {
        if (!menu || !menuToggle) return;
        menu.classList.remove('is-open');
        if (menuOverlay) menuOverlay.classList.remove('is-visible');
        Array.prototype.forEach.call(menu.querySelectorAll('.is-submenu-open'), function (item) {
            item.classList.remove('is-submenu-open');
            var toggle = item.querySelector(':scope > .flacso-navbar__submenu-toggle');
            if (toggle) toggle.setAttribute('aria-expanded', 'false');
        });
        menuToggle.setAttribute('aria-expanded', 'false');
        menuToggle.querySelector('.screen-reader-text').textContent = 'Abrir menú';
        document.body.classList.remove('flacso-menu-open');
        if (returnFocus) menuToggle.focus();
    }

    function closeSearch(returnFocus) {
        if (!search || !searchToggle) return;
        search.hidden = true;
        searchToggle.setAttribute('aria-expanded', 'false');
        if (returnFocus) searchToggle.focus();
    }

    if (menuToggle && menu) {
        Array.prototype.forEach.call(menu.querySelectorAll('.menu-item-has-children'), function (item, index) {
            var link = item.querySelector(':scope > a');
            var submenu = item.querySelector(':scope > .sub-menu');
            if (!link || !submenu) return;

            var submenuId = submenu.id || 'flacso-submenu-' + index;
            submenu.id = submenuId;

            var toggle = document.createElement('button');
            toggle.type = 'button';
            toggle.className = 'flacso-navbar__submenu-toggle';
            toggle.setAttribute('aria-expanded', 'false');
            toggle.setAttribute('aria-controls', submenuId);
            toggle.setAttribute('aria-label', 'Mostrar submenú de ' + link.textContent.trim());
            toggle.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m7 10 5 5 5-5"></path></svg>';
            link.insertAdjacentElement('afterend', toggle);

            toggle.addEventListener('click', function () {
                var opening = toggle.getAttribute('aria-expanded') !== 'true';
                item.classList.toggle('is-submenu-open', opening);
                toggle.setAttribute('aria-expanded', opening ? 'true' : 'false');
                toggle.setAttribute('aria-label', (opening ? 'Ocultar' : 'Mostrar') + ' submenú de ' + link.textContent.trim());
            });
        });

        menuToggle.addEventListener('click', function () {
            var opening = menuToggle.getAttribute('aria-expanded') !== 'true';
            if (opening) closeSearch(false);
            menu.classList.toggle('is-open', opening);
            if (menuOverlay) menuOverlay.classList.toggle('is-visible', opening);
            menuToggle.setAttribute('aria-expanded', opening ? 'true' : 'false');
            menuToggle.querySelector('.screen-reader-text').textContent = opening ? 'Cerrar menú' : 'Abrir menú';
            document.body.classList.toggle('flacso-menu-open', opening);
            if (opening) {
                var firstLink = menu.querySelector('a');
                if (firstLink) firstLink.focus();
            }
        });
    }

    if (menuClose) {
        menuClose.addEventListener('click', function () {
            closeMenu(true);
        });
    }

    if (menuOverlay) {
        menuOverlay.addEventListener('click', function () {
            closeMenu(true);
        });
    }

    if (searchToggle && search) {
        searchToggle.addEventListener('click', function () {
            var opening = searchToggle.getAttribute('aria-expanded') !== 'true';
            if (opening) closeMenu(false);
            search.hidden = !opening;
            searchToggle.setAttribute('aria-expanded', opening ? 'true' : 'false');
            if (opening && searchField) searchField.focus();
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;
        if (search && !search.hidden) {
            closeSearch(true);
            return;
        }
        if (menu && menu.classList.contains('is-open')) closeMenu(true);
    });

    document.addEventListener('click', function (event) {
        if (!header.contains(event.target)) {
            closeMenu(false);
            closeSearch(false);
        }
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth > 1080) closeMenu(false);
    });
}());
