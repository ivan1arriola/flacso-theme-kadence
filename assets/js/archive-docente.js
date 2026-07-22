document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('archive-search');
    const items = document.querySelectorAll('.docente-item');
    const stats = document.getElementById('search-stats');

    if (!searchInput) return;

    searchInput.addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase().trim();
        let visibleCount = 0;

        items.forEach(item => {
            const content = item.getAttribute('data-search');
            if (content.includes(term)) {
                item.style.display = 'block';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        stats.textContent = `Mostrando ${visibleCount} ${visibleCount === 1 ? 'perfil' : 'perfiles'}`;
    });
});
