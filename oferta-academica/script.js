(function () {
    document.addEventListener('click', function (event) {
        const header = event.target.closest('.flacso-oferta-responsive .kt-blocks-accordion-header');

        if (!header) {
            return;
        }

        const pane = header.closest('.kt-accordion-pane');
        const wrapper = header.closest('.kt-accordion-inner-wrap');

        if (!pane || !wrapper) {
            return;
        }

        const panel = pane.querySelector('.kt-accordion-panel');
        const isActive = pane.classList.contains('kt-accordion-pane-active');

        wrapper.querySelectorAll('.kt-accordion-pane').forEach(function (item) {
            item.classList.remove('kt-accordion-pane-active');

            const itemPanel = item.querySelector('.kt-accordion-panel');

            if (itemPanel) {
                itemPanel.style.display = 'none';
            }
        });

        if (!isActive) {
            pane.classList.add('kt-accordion-pane-active');

            if (panel) {
                panel.style.display = 'block';
            }
        }
    });
})();

/* Panel lateral derecho de "Más información": reutiliza el único formulario existente. */
(function() {
    var root = document.querySelector('.flacso-oferta-responsive');
    if (!root || !window.matchMedia) return;

    var aside = root.querySelector('#flacso-oa-consulta.flacso-mobile-info-sheet');
    var openButton = root.querySelector('.flacso-mobile-actions__info');
    var closeButton = aside ? aside.querySelector('.flacso-mobile-info-sheet__close') : null;
    var overlay = root.querySelector('.flacso-mobile-info-overlay');
    if (!aside || !openButton || !closeButton || !overlay) return;

    var mobile = window.matchMedia('(max-width: 760px)');
    var isOpen = false;
    var returnFocus = null;
    var savedScroll = 0;
    var bodyBefore = {};
    var topLayerActive = false;

    function isMobile() { return mobile.matches; }

    // Los widgets de accesibilidad y traducción pueden estar por encima de
    // cualquier z-index normal. La top layer nativa evita solapamientos.
    function showInTopLayer() {
        if (typeof aside.showPopover !== 'function' || typeof overlay.showPopover !== 'function') {
            return;
        }

        overlay.setAttribute('popover', 'manual');
        aside.setAttribute('popover', 'manual');
        try {
            overlay.showPopover();
            aside.showPopover();
            topLayerActive = true;
        } catch (error) {
            if (aside.matches(':popover-open')) aside.hidePopover();
            if (overlay.matches(':popover-open')) overlay.hidePopover();
            aside.removeAttribute('popover');
            overlay.removeAttribute('popover');
            topLayerActive = false;
        }
    }

    function hideFromTopLayer() {
        if (!topLayerActive) return;
        if (aside.matches(':popover-open')) aside.hidePopover();
        if (overlay.matches(':popover-open')) overlay.hidePopover();
        aside.removeAttribute('popover');
        overlay.removeAttribute('popover');
        topLayerActive = false;
    }

    function setClosedAccessibility() {
        aside.inert = isMobile();
        if (isMobile()) {
            aside.setAttribute('aria-hidden', 'true');
        } else {
            aside.removeAttribute('aria-hidden');
        }
        aside.removeAttribute('role');
        aside.removeAttribute('aria-modal');
        aside.removeAttribute('aria-labelledby');
        openButton.setAttribute('aria-expanded', 'false');
    }

    function restoreBody() {
        document.body.style.position = bodyBefore.position || '';
        document.body.style.top = bodyBefore.top || '';
        document.body.style.left = bodyBefore.left || '';
        document.body.style.right = bodyBefore.right || '';
        document.body.style.width = bodyBefore.width || '';
        document.body.style.overflow = bodyBefore.overflow || '';
        window.scrollTo(0, savedScroll);
    }

    function closePanel(focusBack) {
        if (!isOpen) return;
        isOpen = false;
        root.classList.remove('flacso-mobile-sheet-open');
        aside.classList.remove('is-open');
        overlay.classList.remove('is-visible');
        hideFromTopLayer();
        setClosedAccessibility();
        restoreBody();
        if (focusBack !== false && returnFocus && returnFocus.isConnected) {
            returnFocus.focus({preventScroll: true});
        }
    }

    function openPanel(trigger) {
        if (!isMobile() || isOpen) return;
        returnFocus = trigger || document.activeElement;
        savedScroll = window.scrollY || document.documentElement.scrollTop || 0;
        bodyBefore = {
            position: document.body.style.position,
            top: document.body.style.top,
            left: document.body.style.left,
            right: document.body.style.right,
            width: document.body.style.width,
            overflow: document.body.style.overflow
        };
        document.body.style.position = 'fixed';
        document.body.style.top = '-' + savedScroll + 'px';
        document.body.style.left = '0';
        document.body.style.right = '0';
        document.body.style.width = '100%';
        document.body.style.overflow = 'hidden';

        isOpen = true;
        aside.inert = false;
        aside.removeAttribute('aria-hidden');
        aside.setAttribute('role', 'dialog');
        aside.setAttribute('aria-modal', 'true');
        aside.setAttribute('aria-labelledby', 'flacso-mobile-info-sheet-title');
        root.classList.add('flacso-mobile-sheet-open');
        aside.classList.add('is-open');
        overlay.classList.add('is-visible');
        showInTopLayer();
        openButton.setAttribute('aria-expanded', 'true');
        aside.scrollTop = 0;
        closeButton.focus({preventScroll: true});
    }

    function syncMode() {
        if (!isMobile() && isOpen) closePanel(false);
        root.classList.toggle('flacso-mobile-sheet-ready', isMobile());
        if (!isOpen) setClosedAccessibility();
    }

    openButton.addEventListener('click', function() { openPanel(openButton); });
    closeButton.addEventListener('click', function() { closePanel(true); });
    overlay.addEventListener('click', function() { closePanel(true); });

    // Cerrar deslizando hacia la derecha sin interferir con los campos del formulario.
    // Un desplazamiento vertical no debe activar el gesto de cierre.
    var touchStart = null;
    aside.addEventListener('touchstart', function(event) {
        if (!isOpen || event.touches.length !== 1) {
            touchStart = null;
            return;
        }
        var target = event.target;
        if (target instanceof Element && target.closest('input, textarea, select, button, a, label, [contenteditable="true"]')) {
            touchStart = null;
            return;
        }
        touchStart = {
            x: event.touches[0].clientX,
            y: event.touches[0].clientY
        };
    }, {passive: true});

    aside.addEventListener('touchend', function(event) {
        if (!touchStart || !isOpen || !event.changedTouches.length) return;
        var deltaX = event.changedTouches[0].clientX - touchStart.x;
        var deltaY = event.changedTouches[0].clientY - touchStart.y;
        touchStart = null;
        if (deltaX > 80 && Math.abs(deltaX) > Math.abs(deltaY) * 1.5) {
            closePanel(true);
        }
    }, {passive: true});
    aside.addEventListener('touchcancel', function() { touchStart = null; }, {passive: true});

    // Reutilizar también los CTA de solicitar información existentes.
    root.addEventListener('click', function(event) {
        if (!isMobile() || !(event.target instanceof Element)) return;
        var link = event.target.closest('a[href="#flacso-oa-consulta"]');
        if (!link) return;
        event.preventDefault();
        openPanel(link);
    });

    aside.addEventListener('click', function(event) {
        if (!isOpen || !(event.target instanceof Element)) return;
        var destination = event.target.closest('a[href^="#"]');
        if (destination && destination.getAttribute('href') !== '#flacso-oa-consulta') {
            closePanel(false);
        }
    });

    document.addEventListener('keydown', function(event) {
        if (!isOpen) return;
        if (event.key === 'Escape') {
            event.preventDefault();
            closePanel(true);
            return;
        }
        if (event.key !== 'Tab') return;
        var elements = Array.prototype.filter.call(
            aside.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'),
            function(el) { return el.getClientRects().length && !el.closest('[hidden]'); }
        );
        if (!elements.length) {
            event.preventDefault();
            closeButton.focus();
            return;
        }
        var first = elements[0], last = elements[elements.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });

    if (mobile.addEventListener) {
        mobile.addEventListener('change', syncMode);
    } else if (mobile.addListener) {
        mobile.addListener(syncMode);
    }
    syncMode();
})();


(function() {
    if (typeof window.flacsoMetaTrack !== 'function') return;
    try {
        window.flacsoMetaTrack('ViewContent', {
            content_name: document.querySelector('.flacso-oferta-responsive').dataset.offerTitle,
            content_category: 'oferta_academica',
            content_ids: ['oferta-' + document.querySelector('.flacso-oferta-responsive').dataset.offerId],
            flacso_stage: 'pagina_oferta',
            flacso_offer_type: (location.pathname.split('/')[2] || '')
        });
    } catch (e) {}
})();

document.addEventListener('click', function(event) {
    var link = event.target.closest('.flacso-oferta-responsive .final-cta [data-flacso-preinscription-cta]');
    var root = document.querySelector('.flacso-oferta-responsive');
    if (!link || !root || typeof window.flacsoMetaTrack !== 'function') return;
    window.flacsoMetaTrack('InitiateCheckout', {
        content_name: root.dataset.offerTitle,
        content_category: 'oferta_academica'
    });
});

(function() {
    if (typeof window.flacsoMetaTrackCustom !== 'function') return;

    var ofertaTitle = document.querySelector('.flacso-oferta-responsive').dataset.offerTitle;
    var ofertaId = document.querySelector('.flacso-oferta-responsive').dataset.offerId;
    var trackedSections = {};
    var baseParams = {
        content_name: ofertaTitle,
        content_category: 'oferta_academica',
        content_ids: ['oferta-' + ofertaId],
        flacso_offer_type: (location.pathname.split('/')[2] || '')
    };

    function trackDocumentEvent(eventName, extraParams) {
        if (!eventName) return;

        try {
            window.flacsoMetaTrackCustom(eventName, Object.assign({}, baseParams, extraParams || {}));
        } catch (e) {}
    }

    document.querySelectorAll('[data-flacso-meta-doc-section]').forEach(function(section) {
        var eventName = section.getAttribute('data-flacso-meta-doc-section') || '';
        var layout = section.getAttribute('data-flacso-meta-doc-layout') || '';

        if (!eventName) {
            return;
        }

        function fireSectionView() {
            if (trackedSections[eventName]) {
                return;
            }

            trackedSections[eventName] = true;
            trackDocumentEvent(eventName, {
                flacso_stage: 'document_section_visible',
                flacso_doc_layout: layout
            });

            var also = section.getAttribute('data-flacso-meta-doc-also') || '';
            if (also && !trackedSections[also]) {
                trackedSections[also] = true;
                trackDocumentEvent(also, {
                    flacso_stage: 'document_section_visible',
                    flacso_doc_layout: layout
                });
            }
        }

        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (!entry.isIntersecting || entry.intersectionRatio < 0.35) {
                        return;
                    }

                    fireSectionView();
                    observer.disconnect();
                });
            }, {
                threshold: [0.35]
            });

            observer.observe(section);
        } else {
            fireSectionView();
        }
    });

    document.addEventListener('click', function(event) {
        var link = event.target.closest('.flacso-meta-doc-link');

        if (!link) {
            return;
        }

        var section = link.closest('[data-flacso-meta-doc-section]');
        var layout = section ? (section.getAttribute('data-flacso-meta-doc-layout') || '') : '';
        var eventName = '';

        if (link.classList.contains('flacso-meta-doc-link--calendario')) {
            eventName = 'CalendarioAcademicoClick';
        }
        if (link.classList.contains('flacso-meta-doc-link--malla')) {
            trackDocumentEvent('MallaCurricularClick', {
                flacso_stage: 'document_click',
                flacso_doc_layout: layout,
                flacso_doc_url: link.href || ''
            });
        }

        trackDocumentEvent(eventName, {
            flacso_stage: 'document_click',
            flacso_doc_layout: layout,
            flacso_doc_url: link.href || ''
        });
    }, true);
})();
