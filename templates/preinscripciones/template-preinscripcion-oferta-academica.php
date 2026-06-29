<?php
/**
 * Template Name: FLACSO - Preinscripción a Oferta Académica
 * Description: Template para la ruta virtual de preinscripción de oferta académica de FLACSO Uruguay
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$plugin = FLACSO_Formulario_Preinscripcion_Final::get_instance();
$info_posgrado = method_exists($plugin, 'get_preinscripcion_template_context')
    ? $plugin->get_preinscripcion_template_context()
    : null;

if (!is_array($info_posgrado)) {
    ?>
    <div id="primary" class="content-area">
        <main id="main" class="site-main">
            <div class="error"><p>Error: No se pudo cargar el formulario de preinscripción.</p></div>
        </main>
    </div>
    <?php
    get_footer();
    return;
}
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <div class="flacso-preinscripciones-container">
            <?php $plugin->render_hero_header($info_posgrado); ?>

            <?php if (!empty($info_posgrado['preinscripcion_cerrada'])): ?>
                <?php $plugin->render_aviso_preinscripciones_cerradas($info_posgrado); ?>
            <?php else: ?>
                <div class="container" style="margin: 40px auto;">
                    <div class="row justify-content-center">
                        <div class="col-12 col-lg-8">
                            <div class="flacso-formulario-card">
                                <div class="flacso-formulario-body">
                                    <form id="flacso-formulario-preinscripcion" class="needs-validation" enctype="multipart/form-data" novalidate>
                                        <?php
                                        $plugin->render_campos_ocultos($info_posgrado);
                                        $plugin->render_seccion_correo();
                                        $plugin->render_seccion_info_personal();
                                        $plugin->render_seccion_contacto();
                                        $plugin->render_seccion_academica($info_posgrado);
                                        $plugin->render_seccion_documentacion($info_posgrado);

                                        if (!empty($info_posgrado['es_maestria'])) {
                                            $plugin->render_seccion_cartas_recomendacion();
                                        }

                                        $plugin->render_seccion_adicional();
                                        $plugin->render_boton_envio();
                                        ?>
                                    </form>

                                    <div id="flacso-resultado-envio" class="flacso-resultado-area mt-4" aria-live="polite" role="status"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <script>
            (function() {
                if (typeof window.flacsoMetaTrack !== 'function') return;
                try {
                    window.flacsoMetaTrack('ViewContent', {
                        content_name: <?php echo wp_json_encode((string) $info_posgrado['titulo_posgrado']); ?>,
                        content_category: 'oferta_academica',
                        content_ids: ['oferta-' + <?php echo wp_json_encode((string) $info_posgrado['id_posgrado']); ?>],
                        flacso_stage: 'formulario_preinscripcion'
                    });
                } catch (e) {}
            })();
            </script>
        </div>
    </main><!-- .site-main -->
</div><!-- .content-area -->

<?php get_footer();
