<?php
/**
 * Carta · Mensaje de la cohorte, justo después de la sección 01.
 *
 * Presenta datos ya resueltos por flacso_theme_get_carta_view().
 *
 * @package kadence-child-flacso
 */

if (!defined('ABSPATH')) {
    exit;
}

['mensaje_cohorte' => $mensaje_cohorte] = $args['carta'];

if (trim(wp_strip_all_tags($mensaje_cohorte)) === '') {
    return;
}
?>
<aside class="fc-cohort-message" aria-label="<?php esc_attr_e('Mensaje de la cohorte', 'flacso-uruguay'); ?>">
    <div class="fc-cohort-message__body">
        <?php echo wp_kses_post(wpautop($mensaje_cohorte)); ?>
    </div>
</aside>
