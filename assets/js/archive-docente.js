// Directorio del equipo: búsqueda sin tildes y perfiles por tandas (sin JavaScript se ven todos).
document.addEventListener('DOMContentLoaded', function () {
    var searchInput = document.getElementById('archive-search');
    var grid = document.getElementById('docentes-grid');
    var stats = document.getElementById('search-stats');
    if (!searchInput || !grid) return;

    var BATCH = 24;
    var items = Array.prototype.slice.call(grid.querySelectorAll('.docente-item'));
    var limit = BATCH;
    var normalize = function (text) {
        return String(text || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().trim();
    };
    items.forEach(function (item) { item.dataset.norm = normalize(item.getAttribute('data-search')); });

    if (stats) { stats.setAttribute('aria-live', 'polite'); }

    var more = document.createElement('div');
    more.className = 'flacso-load-more';
    more.innerHTML = '<button type="button" class="btn-profile" id="archive-more"></button>';
    grid.parentNode.insertBefore(more, grid.nextSibling);
    var moreButton = more.querySelector('button');

    var empty = document.createElement('p');
    empty.className = 'empty-results';
    empty.hidden = true;
    empty.textContent = 'No encontramos perfiles con esa búsqueda. Probá con otro nombre o cargo.';
    grid.parentNode.insertBefore(empty, more);

    function render() {
        var term = normalize(searchInput.value);
        var matches = items.filter(function (item) { return term === '' || item.dataset.norm.indexOf(term) !== -1; });
        items.forEach(function (item) { item.hidden = true; item.style.display = 'none'; });
        matches.slice(0, limit).forEach(function (item) { item.hidden = false; item.style.display = ''; });

        var shown = Math.min(limit, matches.length);
        if (stats) {
            stats.textContent = 'Mostrando ' + shown + ' de ' + matches.length + (matches.length === 1 ? ' perfil' : ' perfiles');
        }
        empty.hidden = matches.length !== 0;
        var remaining = matches.length - shown;
        more.hidden = remaining <= 0;
        moreButton.textContent = 'Mostrar más (' + remaining + ' restantes)';
    }

    searchInput.addEventListener('input', function () { limit = BATCH; render(); });
    moreButton.addEventListener('click', function () { limit += BATCH; render(); });
    render();
});
