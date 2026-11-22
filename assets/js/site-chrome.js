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

    function setSubmenuState(item, opening) {
        if (!item) return;

        var link = item.querySelector(':scope > a');
        var toggle = item.querySelector(':scope > .flacso-navbar__submenu-toggle');
        var label = link ? link.textContent.trim() : 'esta sección';

        item.classList.toggle('is-submenu-open', opening);

        if (toggle) {
            toggle.setAttribute('aria-expanded', opening ? 'true' : 'false');
            toggle.setAttribute('aria-label', (opening ? 'Ocultar' : 'Mostrar') + ' submenú de ' + label);
        }
    }

    function closeOtherSubmenus(activeItem) {
        if (!menu) return;

        Array.prototype.forEach.call(menu.querySelectorAll('.menu-item-has-children.is-submenu-open'), function (item) {
            if (item !== activeItem) setSubmenuState(item, false);
        });
    }

    function closeAllSubmenus() {
        if (!menu) return;

        Array.prototype.forEach.call(menu.querySelectorAll('.menu-item-has-children.is-submenu-open'), function (item) {
            setSubmenuState(item, false);
        });
    }

    function closeMenu(returnFocus) {
        if (!menu || !menuToggle) return;
        menu.classList.remove('is-open');
        if (menuOverlay) menuOverlay.classList.remove('is-visible');
        closeAllSubmenus();
        menuToggle.setAttribute('aria-expanded', 'false');
        menuToggle.querySelector('.screen-reader-text').textContent = 'Abrir menú';
        document.body.classList.remove('flacso-menu-open');
        if (returnFocus) menuToggle.focus();
    }

    // Submenú de escritorio: se abre por :hover y :focus-within; Escape lo descarta (WCAG 1.4.13) hasta que el
    // puntero o el foco salgan del ítem. El submenú pasa por una transición de visibility: focus() se reintenta.
    function dismissDesktopSubmenu() {
        if (!menu || window.innerWidth <= 1080) return false;
        var active = document.activeElement;
        var item = active && active.closest ? active.closest('.menu-item-has-children') : null;
        if (!item || !menu.contains(item)) item = menu.querySelector('.menu-item-has-children:hover');
        if (!item) return false;
        var link = item.querySelector(':scope > a');
        item.classList.add('is-submenu-dismissed');
        if (link && item.contains(active) && active !== link) link.focus();
        return true;
    }

    function openDesktopSubmenuFromKeyboard(item) {
        var first = item.querySelector(':scope > .sub-menu a');
        if (!first) return;
        item.classList.remove('is-submenu-dismissed');
        var attempts = 0;
        var focusFirst = function () {
            first.focus();
            if (document.activeElement !== first && attempts++ < 30) requestAnimationFrame(focusFirst);
        };
        requestAnimationFrame(focusFirst);
    }

    if (menu) {
        Array.prototype.forEach.call(menu.querySelectorAll('.menu-item-has-children'), function (item) {
            // Una nueva entrada o salida del puntero es una interacción nueva: el submenú vuelve a mostrarse.
            item.addEventListener('mouseenter', function () {
                item.classList.remove('is-submenu-dismissed');
            });
            item.addEventListener('mouseleave', function () {
                item.classList.remove('is-submenu-dismissed');
            });
            item.addEventListener('focusout', function (event) {
                if (!event.relatedTarget || !item.contains(event.relatedTarget)) item.classList.remove('is-submenu-dismissed');
            });
        });
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

                if (opening) closeOtherSubmenus(item);
                setSubmenuState(item, opening);
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
                closeAllSubmenus();
                // El cajón sigue en visibility:hidden al abrir y focus() no funciona sobre un elemento oculto. Cuántos
                // frames tarda en salir de "hidden" depende de la carga de la página: se reintenta hasta que el foco entra.
                var focusTarget = menuClose || menu.querySelector('a');
                var attempts = 0;
                var focusDrawer = function () {
                    if (!focusTarget || !menu.classList.contains('is-open')) return;
                    focusTarget.focus();
                    if (document.activeElement !== focusTarget && attempts++ < 60) requestAnimationFrame(focusDrawer);
                };
                requestAnimationFrame(focusDrawer);
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

    // El <nav> va antes del botón en el HTML: sin esto, Tab sale del cajón abierto hacia la página tapada.
    function menuFocusables() {
        return Array.prototype.filter.call(menu.querySelectorAll('a[href], button'), function (el) {
            return !el.disabled && getComputedStyle(el).visibility !== 'hidden' && el.offsetParent !== null;
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Tab' && menu && menu.classList.contains('is-open')) {
            var items = menuFocusables();
            if (!items.length) return;
            var first = items[0];
            var last = items[items.length - 1];
            if (event.shiftKey && (document.activeElement === first || !menu.contains(document.activeElement))) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && (document.activeElement === last || !menu.contains(document.activeElement))) {
                event.preventDefault();
                first.focus();
            }
            return;
        }
        if (event.key === 'ArrowDown' && menu && window.innerWidth > 1080) {
            var parentLink = document.activeElement;
            var parentItem = parentLink && parentLink.parentElement;
            if (parentItem && parentItem.classList.contains('menu-item-has-children') && menu.contains(parentItem)
                && parentLink.matches('.menu-item-has-children > a')) {
                event.preventDefault();
                openDesktopSubmenuFromKeyboard(parentItem);
                return;
            }
        }
        if (event.key !== 'Escape') return;
        if (search && !search.hidden) {
            closeSearch(true);
            return;
        }
        if (dismissDesktopSubmenu()) return;
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
