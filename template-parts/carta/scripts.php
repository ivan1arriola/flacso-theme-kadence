<?php
/**
 * Carta · Scripts de la carta: acordeón, índice, botón flotante y seguimiento.
 *
 * Presenta datos ya resueltos por flacso_theme_get_carta_view().
 *
 * @package kadence-child-flacso
 */

if (!defined('ABSPATH')) {
    exit;
}

[
    'post_id' => $post_id,
    'titulo' => $titulo,
] = $args['carta'];
?>
<script>
(function () {
    const groups = Array.from(document.querySelectorAll('.fc-academic-detail'));

    groups.forEach((detail) => {
        detail.addEventListener('toggle', () => {
            if (!detail.open) return;

            groups.forEach((other) => {
                if (other !== detail && other.open) {
                    other.open = false;
                }
            });
        });
    });
}());

</script>

<script>
/* El índice resalta la sección que se está leyendo, también con teclado. */
(function () {
    const nav = document.querySelector('.fc-jump-nav');
    if (!nav) return;

    const items = Array.from(nav.querySelectorAll('a[href^="#"]'))
        .map((link) => ({ link, target: document.getElementById(link.hash.slice(1)) }))
        .filter((item) => item.target);

    if (!items.length) return;
    let scheduled = false;

    const update = () => {
        scheduled = false;
        const threshold = Math.min(170, window.innerHeight * .28);
        let current = items[0];

        for (const item of items) {
            if (item.target.getBoundingClientRect().top <= threshold) {
                current = item;
            }
        }

        if (window.scrollY + window.innerHeight >= document.documentElement.scrollHeight - 24) {
            current = items[items.length - 1];
        }

        for (const item of items) {
            if (item === current) {
                item.link.setAttribute('aria-current', 'location');
            } else {
                item.link.removeAttribute('aria-current');
            }
        }
    };

    const scheduleUpdate = () => {
        if (scheduled) return;
        scheduled = true;
        requestAnimationFrame(update);
    };

    window.addEventListener('scroll', scheduleUpdate, { passive: true });
    window.addEventListener('resize', scheduleUpdate);
    update();
}());
</script>

<script>
(function () {
    const floatingButton = document.querySelector('.fc-floating-preinscripcion');
    if (!floatingButton || !('IntersectionObserver' in window)) {
        return;
    }

    const preinscriptionButtons = Array.from(document.querySelectorAll('.fc-primary-button, .fc-final-cta a, a[href*="/preinscripcion"]'))
        .filter((button) => button !== floatingButton);

    if (!preinscriptionButtons.length) {
        floatingButton.classList.remove('is-hidden');
        floatingButton.removeAttribute('aria-hidden');
        floatingButton.removeAttribute('tabindex');
        return;
    }

    const visibleButtons = new Set();
    const setFloatingVisibility = () => {
        const shouldHide = visibleButtons.size > 0;
        floatingButton.classList.toggle('is-hidden', shouldHide);

        if (shouldHide) {
            floatingButton.setAttribute('aria-hidden', 'true');
            floatingButton.setAttribute('tabindex', '-1');
        } else {
            floatingButton.removeAttribute('aria-hidden');
            floatingButton.removeAttribute('tabindex');
        }
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting && entry.intersectionRatio >= 0.35) {
                visibleButtons.add(entry.target);
            } else {
                visibleButtons.delete(entry.target);
            }
        });
        setFloatingVisibility();
    }, {
        threshold: [0, 0.35, 0.65],
        rootMargin: '0px 0px -8% 0px'
    });

    preinscriptionButtons.forEach((button) => observer.observe(button));
    setFloatingVisibility();
}());
</script>

<script>
(function() {
    if (typeof window.flacsoMetaTrack !== 'function') return;
    try {
        window.flacsoMetaTrack('ViewContent', {
            content_name: <?php echo wp_json_encode((string) $titulo); ?>,
            content_category: 'oferta_academica',
            content_ids: ['oferta-' + <?php echo wp_json_encode((string) $post_id); ?>],
            flacso_stage: 'carta_informacion'
        });
    } catch (e) {}
})();
</script>
