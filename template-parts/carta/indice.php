<?php
/**
 * Carta · Índice lateral de la carta.
 *
 * Presenta datos ya resueltos por flacso_theme_get_carta_view().
 *
 * @package kadence-child-flacso
 */

if (!defined('ABSPATH')) {
    exit;
}

[
    'hay_bloques_detallados' => $hay_bloques_detallados,
] = $args['carta'];
?>
                <aside class="fc-reading-sidebar">
                    <nav class="fc-jump-nav" aria-label="<?php esc_attr_e('Navegación por la carta', 'flacso-uruguay'); ?>">
                        <span class="fc-jump-nav__label"><?php esc_html_e('Explorá la carta', 'flacso-uruguay'); ?></span>
                        <div class="fc-jump-nav__links">
                            <?php if ($hay_bloques_detallados) : ?>
                                <a href="#fc-overview-title"><?php esc_html_e('Propuesta', 'flacso-uruguay'); ?></a>
                                <a href="#fc-experience-title"><?php esc_html_e('Experiencia académica', 'flacso-uruguay'); ?></a>
                            <?php endif; ?>
                            <a href="#fc-planificacion"><?php esc_html_e('Calendario y costos', 'flacso-uruguay'); ?></a>
                            <a href="#fc-practica"><?php esc_html_e('Información práctica', 'flacso-uruguay'); ?></a>
                            <a href="#fc-trust"><?php esc_html_e('Por qué FLACSO', 'flacso-uruguay'); ?></a>
                            <a href="#fc-contact-title"><?php esc_html_e('Contacto', 'flacso-uruguay'); ?></a>
                        </div>
                        </nav>
                    <div class="fc-reading-help">
                        <i class="bi bi-chat-dots" aria-hidden="true"></i>
                        <strong><?php esc_html_e('¿Tenés consultas?', 'flacso-uruguay'); ?></strong>
                        <p><?php esc_html_e('Nuestro equipo académico puede orientarte.', 'flacso-uruguay'); ?></p>
                        <a href="#fc-contact-title"><?php esc_html_e('Ver contacto académico', 'flacso-uruguay'); ?> <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                    </div>
                </aside>
