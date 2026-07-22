(function() {
    if (typeof window.flacsoMetaTrack !== 'function') return;
    try {
        window.flacsoMetaTrack('ViewContent', {
            content_name: 'Listado de Seminarios',
            content_category: 'listado_seminarios',
            flacso_stage: 'listado_seminarios'
        });
    } catch (e) {}
})();
