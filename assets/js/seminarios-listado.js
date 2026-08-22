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
        var items = Array.prototype.slice.call(root.querySelectorAll('[data-seminar-item]'));
        var monthSections = Array.prototype.slice.call(root.querySelectorAll('[data-seminar-month-section]'));
        var featured = root.querySelector('[data-seminar-featured]');
        var empty = root.querySelector('[data-seminarios-filter-empty]');
        var activeMonth = 'all';

        function matches(item, term) {
            var monthMatches = activeMonth === 'all' || item.getAttribute('data-month') === activeMonth;
            var textMatches = !term || normalize(item.getAttribute('data-search')).indexOf(term) !== -1;
            return monthMatches && textMatches;
        }

        function update() {
            var term = normalize(search ? search.value : '');
            var visibleItems = 0;

            items.forEach(function (item) {
                var visible = matches(item, term);
                item.hidden = !visible;
                if (visible) {
                    visibleItems += 1;
                }
            });

            monthSections.forEach(function (section) {
                var hasVisibleItems = Array.prototype.some.call(
                    section.querySelectorAll('[data-seminar-item]'),
                    function (item) { return !item.hidden; }
                );
                section.hidden = !hasVisibleItems;
            });

            if (featured) {
                featured.hidden = !matches(featured, term);
            }

            if (empty) {
                empty.hidden = visibleItems !== 0;
            }

            if (count) {
                count.textContent = visibleItems === 1
                    ? '1 seminario'
                    : visibleItems + ' seminarios';
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
