(function () {
    'use strict';

    function initCarousel(track) {
        if (track.dataset.homeNewsReady === '1') return;
        track.dataset.homeNewsReady = '1';
        var shell = track.closest('.flacso-home-v2__carousel-shell');
        var cards = Array.prototype.slice.call(track.children);
        var previous = shell ? shell.querySelector('[data-home-news-prev]') : null;
        var next = shell ? shell.querySelector('[data-home-news-next]') : null;
        var status = shell ? shell.querySelector('[data-home-news-status]') : null;
        if (!shell || !cards.length || !previous || !next) return;

        function cardStep() {
            var gap = parseFloat(window.getComputedStyle(track).columnGap || '0');
            return cards[0].getBoundingClientRect().width + gap;
        }

        function currentIndex() {
            return Math.max(0, Math.min(cards.length - 1, Math.round(track.scrollLeft / cardStep())));
        }

        function update() {
            var index = currentIndex();
            previous.disabled = index === 0;
            next.disabled = index >= cards.length - 1;
            if (status) status.textContent = (index + 1) + ' / ' + cards.length;
        }

        previous.addEventListener('click', function () {
            track.scrollBy({ left: -cardStep(), behavior: 'smooth' });
        });
        next.addEventListener('click', function () {
            track.scrollBy({ left: cardStep(), behavior: 'smooth' });
        });
        track.addEventListener('scroll', function () {
            window.requestAnimationFrame(update);
        }, { passive: true });
        window.addEventListener('resize', update);
        update();
    }

    function initAll() {
        document.querySelectorAll('[data-home-news-carousel]').forEach(initCarousel);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
}());
