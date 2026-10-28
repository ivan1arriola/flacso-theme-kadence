<?php
/**
 * Carta · Sección 06: contacto académico.
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
    'contacto' => $contacto,
] = $args['carta'];
?>
            <!-- CONTACTO ACADÉMICO -->
            <section class="fc-major-section fc-major-section--contact" aria-labelledby="fc-contact-title">
                <header class="fc-major-heading">
                    <span class="fc-major-heading__kicker" aria-hidden="true">06</span>
                    <div>
                        <h2 id="fc-contact-title"><?php esc_html_e('Contacto académico', 'flacso-uruguay'); ?></h2>
                        <span class="fc-major-heading__rule" aria-hidden="true"></span>
                    </div>
                </header>
                <div class="fc-assistant-card">
                <div class="fc-assistant-photo">
                    <?php if ($contacto['foto_url']) : ?>
                        <img src="<?php echo esc_url($contacto['foto_url']); ?>" alt="<?php echo esc_attr(wp_strip_all_tags($contacto['nombre'])); ?>">
                    <?php else : ?>
                        <div class="fc-assistant-placeholder">
                            <?php echo esc_html(strtoupper(mb_substr(wp_strip_all_tags($contacto['nombre']), 0, 1))); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="fc-assistant-copy">
                    <span class="fc-assistant-label"><?php esc_html_e('Contacto académico', 'flacso-uruguay'); ?></span>
                    <h2><?php echo esc_html($contacto['nombre']); ?></h2>
                    <p><?php echo esc_html($contacto['rol']); ?></p>
                </div>

                <div class="fc-assistant-meta">
                    <?php if ($contacto['correo'] !== '') : ?>
                        <a href="mailto:<?php echo esc_attr($contacto['correo']); ?>">
                            <i class="bi bi-envelope" aria-hidden="true"></i>
                            <span><?php echo esc_html($contacto['correo']); ?></span>
                        </a>
                    <?php endif; ?>
                    <?php if ($textos['telefono'] !== '') : ?>
                        <a href="<?php echo esc_attr($textos['telefono_href']); ?>">
                            <i class="bi bi-telephone" aria-hidden="true"></i>
                            <span><?php echo esc_html($textos['telefono']); ?></span>
                        </a>
                    <?php endif; ?>
                </div>

                <?php if ($contacto['correo'] !== '') : ?>
                    <a class="fc-assistant-action" href="mailto:<?php echo esc_attr($contacto['correo']); ?>">
                        <?php esc_html_e('Realizar una consulta', 'flacso-uruguay'); ?>
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </a>
                <?php endif; ?>
                </div>
            </section>
