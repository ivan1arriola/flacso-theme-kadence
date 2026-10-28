<?php
/**
 * Carta · Sección 05: por qué estudiar en FLACSO Uruguay.
 *
 * Presenta datos ya resueltos por flacso_theme_get_carta_view().
 *
 * @package kadence-child-flacso
 */

if (!defined('ABSPATH')) {
    exit;
}

[
    'textos' => $textos,
] = $args['carta'];
?>
            <!-- MÁS INFORMACIÓN (TRAYECTORIA Y GESTIÓN) -->
            <section id="fc-trust" class="fc-section fc-major-section fc-major-section--trust">
                <header class="fc-major-heading">
                    <span class="fc-major-heading__kicker" aria-hidden="true">05</span>
                    <div>
                        <h2><?php echo esc_html($textos['mas_info_titulo_seccion']); ?></h2>
                        <span class="fc-major-heading__rule" aria-hidden="true"></span>
                    </div>
                </header>

                <div class="fc-more-grid">
                    <article class="fc-more-card">
                        <div class="fc-more-icon"><i class="bi bi-award" aria-hidden="true"></i></div>
                        <h3><?php echo esc_html($textos['mas_info_trayectoria_titulo']); ?></h3>
                        <?php echo wp_kses_post($textos['mas_info_trayectoria_html']); ?>
                    </article>

                    <article class="fc-more-card">
                        <div class="fc-more-icon"><i class="bi bi-gear" aria-hidden="true"></i></div>
                        <h3><?php echo esc_html($textos['mas_info_gestion_titulo']); ?></h3>
                        <?php echo wp_kses_post($textos['mas_info_gestion_html']); ?>
                    </article>

                </div>
            </section>
