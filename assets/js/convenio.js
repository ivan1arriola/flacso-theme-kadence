(function () {
    'use strict';

    var input = document.querySelector('[data-convenios-search]');
    var grid = document.querySelector('[data-convenios-grid]');
    if (!input || !grid) return;

    var cards = Array.prototype.slice.call(grid.querySelectorAll('[data-convenio-card]'));
    var counter = document.querySelector('[data-convenios-count]');
    var empty = document.querySelector('[data-convenios-empty]');

    function normalize(value) {
        var text = String(value || '').toLowerCase();
        try {
            text = text.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        } catch (error) {
            // Navegadores antiguos conservan la búsqueda básica.
        }
        return text.trim();
    }

    function update() {
        var query = normalize(input.value);
        var visible = 0;

        cards.forEach(function (card) {
            var matches = !query || normalize(card.getAttribute('data-search')).indexOf(query) !== -1;
            card.hidden = !matches;
            if (matches) visible += 1;
        });

        if (counter) counter.textContent = visible + (visible === 1 ? ' convenio' : ' convenios');
        if (empty) empty.hidden = visible !== 0;
    }

    input.addEventListener('input', update);
}());
