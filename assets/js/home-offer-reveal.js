(function () {
    'use strict';

    var grid = document.querySelector('.flacso-home-v2 .home-offer-grid');
    if (!grid || !('IntersectionObserver' in window)) return;
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    grid.classList.add('js-offer-reveal');
    var observer = new IntersectionObserver(function (entries) {
        if (!entries[0].isIntersecting) return;
        grid.classList.add('is-revealed');
        observer.disconnect();
    }, { threshold: 0.25 });
    observer.observe(grid);
}());
