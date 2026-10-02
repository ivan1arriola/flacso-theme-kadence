(function () {
    'use strict';

    function normalize(value) {
        return String(value || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();
    }

    function setupSeminarsCatalog(root) {
        var search = root.querySelector('[data-seminar-search]');
        var count = root.querySelector('[data-seminar-search-count]');
        var chips = Array.prototype.slice.call(root.querySelectorAll('[data-seminar-month]'));
        var items = Array.prototype.slice.call(
            root.querySelectorAll('[data-seminar-item], [data-seminar-card]')
        );
        var monthSections = Array.prototype.slice.call(
            root.querySelectorAll('[data-seminar-month-section], [data-month-group]')
        );
        var featured = root.querySelector('[data-seminar-featured], .seminario-featured');
        var empty = root.querySelector('[data-seminarios-filter-empty]');
        var catalogTitle = root.querySelector('#seminarios-catalogo-titulo');
        var catalogLead = root.querySelector('.seminarios-catalog__lead');
        var activeMonth = 'all';
        var featuredItem = null;

        function getMonth(item) {
            return item.getAttribute('data-month') || item.getAttribute('data-month-group') || '';
        }

        function getSearchText(item) {
            return item.getAttribute('data-search') || item.textContent || '';
        }

        function matches(item, term) {
            var itemMonth = getMonth(item);
            var monthMatches = activeMonth === 'all' || itemMonth === '' || itemMonth === activeMonth;
            var textMatches = !term || normalize(getSearchText(item)).indexOf(term) !== -1;
            return monthMatches && textMatches;
        }

        if (featured) {
            var featuredLink = featured.querySelector('a[href]');
            var featuredHref = featuredLink ? featuredLink.href : '';

            if (featuredHref) {
                featuredItem = items.find(function (item) {
                    var itemLink = item.querySelector('a[href]');
                    return itemLink && itemLink.href === featuredHref;
                }) || null;
            }

            if (featuredItem) {
                featuredItem.classList.add('is-featured-duplicate');

                if (catalogTitle) {
                    catalogTitle.textContent = 'Más propuestas';
                }

                if (catalogLead) {
                    catalogLead.textContent = 'Explorá el resto del cronograma de seminarios con inscripción abierta.';
                }
            }
        }

        function update() {
            var term = normalize(search ? search.value : '');
            var featuredVisible = false;
            var visibleItems = 0;

            if (featured) {
                featuredVisible = featuredItem
                    ? matches(featuredItem, term)
                    : matches(featured, term);
                featured.hidden = !featuredVisible;
            }

            items.forEach(function (item) {
                var visible = matches(item, term);

                // El seminario destacado ya está visible en una tarjeta principal.
                // Se evita repetirlo inmediatamente en el catálogo inferior.
                if (featuredVisible && item === featuredItem) {
                    visible = false;
                }

                item.hidden = !visible;
                if (visible) {
                    visibleItems += 1;
                }
            });

            monthSections.forEach(function (section) {
                var sectionItems = Array.prototype.slice.call(
                    section.querySelectorAll('[data-seminar-item], [data-seminar-card]')
                );
                var sectionVisibleItems = sectionItems.filter(function (item) {
                    return !item.hidden;
                });
                var sectionCount = section.querySelector('.seminarios-group__count');

                section.hidden = sectionVisibleItems.length === 0;

                if (!section.hidden && (term || activeMonth !== 'all') && section.tagName === 'DETAILS') {
                    section.open = true;
                }

                if (sectionCount) {
                    sectionCount.textContent = sectionVisibleItems.length === 1
                        ? '1 seminario'
                        : sectionVisibleItems.length + ' seminarios';
                }
            });

            var visibleTotal = visibleItems + (featuredVisible ? 1 : 0);

            if (empty) {
                empty.hidden = visibleTotal !== 0;
            }

            if (count) {
                count.textContent = visibleTotal === 1
                    ? '1 seminario'
                    : visibleTotal + ' seminarios';
            }
        }

        chips.forEach(function (chip) {
            chip.addEventListener('click', function () {
                activeMonth = chip.getAttribute('data-seminar-month') || 'all';
                chips.forEach(function (candidate) {
                    var selected = candidate === chip;
                    candidate.classList.toggle('is-active', selected);
                    candidate.setAttribute('aria-pressed', selected ? 'true' : 'false');
                });
                update();
            });
        });

        if (search) {
            search.addEventListener('input', update);
        }

        update();
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.flacso-seminarios-page').forEach(setupSeminarsCatalog);

        if (typeof window.flacsoMetaTrack === 'function') {
            try {
                window.flacsoMetaTrack('ViewContent', {
                    content_name: 'Listado de Seminarios',
                    content_category: 'listado_seminarios',
                    flacso_stage: 'listado_seminarios'
                });
            } catch (e) {}
        }
    });
})();
