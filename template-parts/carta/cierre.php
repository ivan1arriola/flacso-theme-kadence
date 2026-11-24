<?php
/**
 * Carta · Cierre: llamado final a la preinscripción o a la consulta.
 *
 * Presenta datos ya resueltos por flacso_theme_get_carta_view().
 *
 * @package kadence-child-flacso
 */

if (!defined('ABSPATH')) {
    exit;
}

[
    'titulo' => $titulo,
    'modalidad' => $modalidad,
    'url_inscripcion' => $url_inscripcion,
    'inscripciones_abiertas' => $inscripciones_abiertas,
    'textos' => $textos,
    'carta_cta_titulo' => $carta_cta_titulo,
] = $args['carta'];
?>
            <!-- FINAL CTA -->
            <section class="fc-final-cta">
                <div>
                    <span><?php echo esc_html(flacso_theme_brand_slogan()); ?></span>
                    <h2><?php echo esc_html($carta_cta_titulo); ?></h2>
                    <p><strong><?php echo esc_html($modalidad); ?></strong></p>
                </div>

                <?php if ($inscripciones_abiertas) : ?>
                    <a data-flacso-preinscription-cta="1" href="<?php echo esc_url($url_inscripcion); ?>" aria-label="<?php echo esc_attr(sprintf(/* translators: %s: offer title */ __('Preinscribirme en %s', 'flacso-uruguay'), $titulo)); ?>">
                        <i class="bi bi-pencil-square" aria-hidden="true"></i>
                        <?php esc_html_e('Preinscribirme', 'flacso-uruguay'); ?>
                    </a>
                <?php elseif ($textos['correo_inscripciones'] !== '') : ?>
                    <a href="mailto:<?php echo esc_attr($textos['correo_inscripciones']); ?>" aria-label="<?php echo esc_attr(sprintf(__('Solicitar información para %s', 'flacso-uruguay'), $titulo)); ?>">
                        <i class="bi bi-envelope" aria-hidden="true"></i>
                        <?php esc_html_e('Solicitar información', 'flacso-uruguay'); ?>
                    </a>
                <?php else : ?>
                    <span><?php esc_html_e('Correo de contacto pendiente de confirmar', 'flacso-uruguay'); ?></span>
                <?php endif; ?>
            </section>
