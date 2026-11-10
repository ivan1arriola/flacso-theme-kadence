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
            flacso_stage: 'carta_informacion',
            flacso_offer_type: (location.pathname.split('/')[2] || '')
        });
    } catch (e) {}
})();
</script>

<script>
(function() {
    // Carta: vistas y clics de precios, calendario y malla curricular.
    if (typeof window.flacsoMetaTrackCustom !== 'function') return;
    var base = {
        content_name: <?php echo wp_json_encode((string) $titulo); ?>,
        content_category: 'oferta_academica',
        content_ids: ['oferta-' + <?php echo wp_json_encode((string) $post_id); ?>],
        flacso_offer_type: (location.pathname.split('/')[2] || ''),
        flacso_stage: 'carta_seccion_visible'
    };
    function fire(name, extra) {
        try { window.flacsoMetaTrackCustom(name, Object.assign({}, base, extra || {})); } catch (e) {}
    }
    function watch(selector, names) {
        var el = document.querySelector(selector);
        if (!el) return;
        function once() { names.forEach(function(n) { fire(n); }); }
        if (!('IntersectionObserver' in window)) { once(); return; }
        var io = new IntersectionObserver(function(entries) {
            if (entries.some(function(e) { return e.isIntersecting; })) { io.disconnect(); once(); }
        }, { threshold: 0.35 });
        io.observe(el);
    }
    watch('.fc-pricing-section', ['PreciosSectionView']);
    var docs = document.querySelector('.fc-documents-section');
    if (docs) {
        var t = (docs.querySelector('.fc-section-title') || {}).textContent || '';
        var names = [];
        if (/calendario/i.test(t) || /documentos/i.test(t)) names.push('CalendarioAcademicoSectionView');
        if (/malla|documentos/i.test(t)) names.push('MallaCurricularSectionView');
        watch('.fc-documents-section', names);
        docs.addEventListener('click', function(ev) {
            var a = ev.target.closest('a.fc-card-button');
            if (!a) return;
            var label = (a.textContent || '').toLowerCase();
            var extra = { flacso_stage: 'document_click', flacso_doc_url: a.href || '' };
            if (label.indexOf('calendario') !== -1) fire('CalendarioAcademicoClick', extra);
            if (label.indexOf('malla') !== -1) fire('MallaCurricularClick', extra);
        }, true);
    }
})();
</script>
