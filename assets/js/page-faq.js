(function () {
    'use strict';

    function initializeFaq(root) {
        var items = Array.prototype.slice.call(root.querySelectorAll('[data-flacso-faq-item]'));
        var reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (!items.length || reducedMotion || !('IntersectionObserver' in window)) {
            items.forEach(function (item) {
                item.classList.add('is-visible');
            });
            return;
        }

        root.classList.add('flacso-faq--reveal');

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) {
                    return;
                }

                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        }, {
            rootMargin: '0px 0px -8% 0px',
            threshold: 0.1
        });

        items.forEach(function (item) {
            observer.observe(item);
        });
    }

    function initialize() {
        document.querySelectorAll('[data-flacso-faq]').forEach(initializeFaq);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        initialize();
    }
}());
