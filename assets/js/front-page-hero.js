(function () {
    'use strict';

    var hero = document.querySelector('[data-flacso-front-hero]');
    if (!hero) return;
    var floating = document.querySelector('[data-flacso-front-hero-fab]');

    hero.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', function () {
            link.classList.add('is-activated');
        }, { once: true });
    });

    if (floating && 'IntersectionObserver' in window) {
        new IntersectionObserver(function (entries) {
            floating.classList.toggle('is-visible', !entries[0].isIntersecting);
        }, { threshold: 0.2 }).observe(hero);
    } else if (floating) {
        floating.classList.add('is-visible');
    }

}());
