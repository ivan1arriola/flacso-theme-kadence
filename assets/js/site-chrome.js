(function () {
    'use strict';

    var header = document.querySelector('[data-flacso-header]');
    if (!header) return;

    var menu = header.querySelector('[data-flacso-menu]');
    var menuToggle = header.querySelector('[data-flacso-menu-toggle]');
    var search = header.querySelector('[data-flacso-search]');
    var searchToggle = header.querySelector('[data-flacso-search-toggle]');
    var searchField = header.querySelector('#flacso-header-search-field');

    function closeMenu(returnFocus) {
        if (!menu || !menuToggle) return;
        menu.classList.remove('is-open');
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
        menuToggle.addEventListener('click', function () {
            var opening = menuToggle.getAttribute('aria-expanded') !== 'true';
            if (opening) closeSearch(false);
            menu.classList.toggle('is-open', opening);
            menuToggle.setAttribute('aria-expanded', opening ? 'true' : 'false');
            menuToggle.querySelector('.screen-reader-text').textContent = opening ? 'Cerrar menú' : 'Abrir menú';
            document.body.classList.toggle('flacso-menu-open', opening);
            if (opening) {
                var firstLink = menu.querySelector('a');
                if (firstLink) firstLink.focus();
            }
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
