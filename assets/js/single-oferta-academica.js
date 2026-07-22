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
