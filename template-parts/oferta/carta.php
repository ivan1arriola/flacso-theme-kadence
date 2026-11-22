<?php
/**
 * Template para la Carta de Presentación de una Oferta Académica.
 *
 * Endpoint: /formacion/{tipo}/{slug}/carta/
 * Consume el View Model canónico de FLACSO Uruguay y el modelo de vista de la
 * carta (inc/carta-view-model.php); cada sección está en template-parts/carta/.
 * Integra de forma dinámica el color de presentación de la oferta académica.
 *
 * @package kadence-child-flacso
 */

if (!defined('ABSPATH')) {
    exit;
}

// La directiva robots de la Carta se resuelve temprano en inc/open-graph.php.

// Encolar estilos dedicados de la carta
wp_enqueue_style(
    'flacso-single-carta',
    get_stylesheet_directory_uri() . '/assets/css/single-carta.css',
    ['chld_thm_cfg_child'],
    flacso_child_asset_version('assets/css/single-carta.css')
);

wp_enqueue_style(
    'flacso-single-carta-editorial',
    get_stylesheet_directory_uri() . '/assets/css/single-carta-editorial.css',
    ['flacso-single-carta'],
    flacso_child_asset_version('assets/css/single-carta-editorial.css')
);

// Capa final de presentación del prototipo editorial; no toca datos académicos.
wp_enqueue_style(
    'flacso-single-carta-reference',
    get_stylesheet_directory_uri() . '/assets/css/single-carta-reference.css',
    ['flacso-single-carta-editorial'],
    flacso_child_asset_version('assets/css/single-carta-reference.css')
);

if (!wp_style_is('bootstrap-icons', 'enqueued')) {
    wp_enqueue_style(
        'bootstrap-icons',
        'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css',
        [],
        '1.13.1'
    );
}

$context = isset($args['context']) && is_array($args['context']) ? $args['context'] : null;
if (!$context) {
    $context = function_exists('flacso_theme_get_offer_context') ? flacso_theme_get_offer_context((int) get_the_ID()) : [];
}

if (empty($context)) {
    return;
}

$carta = flacso_theme_get_carta_view($context);
$carta_args = ['carta' => $carta];
$carta_parte = static function (string $parte) use ($carta_args): void {
    get_template_part('template-parts/carta/' . $parte, null, $carta_args);
};

?>

<div id="primary" class="content-area flacso-carta-virtual" style="<?php echo esc_attr($carta['inline_brand_styles']); ?>">
    <a class="fc-skip-link" href="<?php echo $carta['hay_bloques_detallados'] ? '#fc-overview-title' : '#fc-planificacion'; ?>"><?php esc_html_e('Saltar a la información principal', 'flacso-uruguay'); ?></a>
    <<?php echo flacso_child_main_tag(); ?> id="main" class="site-main" aria-labelledby="fc-carta-title">

        <?php $carta_parte('hero'); ?>
        <?php $carta_parte('propuesta-extras'); ?>

        <div class="fc-page-container">

            <!-- Índice de secciones. Mantiene anclas y estado activo sin duplicar el menú del sitio. -->
            <div class="fc-reading-layout">
                <?php $carta_parte('indice'); ?>
                <div class="fc-reading-main">
                    <?php $carta_parte('contenido'); ?>
                    <?php $carta_parte('planificacion'); ?>
                    <?php $carta_parte('practica'); ?>
                    <?php $carta_parte('confianza'); ?>
                    <?php $carta_parte('contacto'); ?>
                    <?php $carta_parte('cierre'); ?>
                </div><!-- /.fc-reading-main -->
            </div><!-- /.fc-reading-layout -->

            <?php $carta_parte('flotante'); ?>

        </div>
    </<?php echo flacso_child_main_tag(); ?>>
</div>

<?php $carta_parte('scripts'); ?>
