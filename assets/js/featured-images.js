(function () {
    'use strict';

    var selector = [
        '.post-thumbnail img',
        '.kadence-post-image img',
        '.flacso-hero-figure .flacso-hero-image',
        '.flacso-archive-card-thumb img',
        '.seminario-card__image',
        '.seminarios-featured__image',
        '.evento-img img',
        '.charla-img img',
        '.flc-eventos-react__media img',
        '.novedades-grid .card-img-container img'
    ].join(',');

    function imageUrl(image) {
        return image.currentSrc || image.src || image.getAttribute('data-src') || '';
    }

    function updateBackground(image, frame) {
        var src = imageUrl(image);
        if (src) {
            frame.style.setProperty('--flacso-featured-image', 'url(' + JSON.stringify(src) + ')');
        }
    }

    function enhance(image) {
        if (!(image instanceof HTMLImageElement) || image.dataset.flacsoFeatured45 === 'true') {
            return;
        }

        image.dataset.flacsoFeatured45 = 'true';

        var frame = document.createElement('span');
        frame.className = 'flacso-featured-image-45';
        image.parentNode.insertBefore(frame, image);
        frame.appendChild(image);
        updateBackground(image, frame);

        image.addEventListener('load', function () {
            updateBackground(image, frame);
        });
    }

    function scan(root) {
        if (root instanceof Element && root.matches(selector)) {
            enhance(root);
        }

        if (root.querySelectorAll) {
            root.querySelectorAll(selector).forEach(enhance);
        }
    }

    function init() {
        scan(document);

        new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (node) {
                    if (node.nodeType === 1) {
                        scan(node);
                    }
                });
            });
        }).observe(document.body, { childList: true, subtree: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
