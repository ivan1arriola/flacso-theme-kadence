<?php
/**
 * Carta · Botón flotante de preinscripción.
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
    'url_inscripcion' => $url_inscripcion,
    'inscripciones_abiertas' => $inscripciones_abiertas,
] = $args['carta'];
?>
            <?php if ($inscripciones_abiertas) : ?>
                <a class="fc-floating-preinscripcion is-hidden" data-flacso-preinscription-cta="1" href="<?php echo esc_url($url_inscripcion); ?>" aria-label="<?php echo esc_attr(sprintf(__('Abrir formulario de preinscripción para %s', 'flacso-uruguay'), $titulo)); ?>" aria-hidden="true" tabindex="-1">
                    <i class="bi bi-pencil-square" aria-hidden="true"></i>
                    <span class="fc-floating-copy">
                        <strong><?php esc_html_e('Formulario de preinscripción', 'flacso-uruguay'); ?></strong>
                        <small><?php esc_html_e('Inscripciones abiertas', 'flacso-uruguay'); ?></small>
                    </span>
                </a>
            <?php endif; ?>
