<?php
/**
 * Carta · Información extra de la cohorte (promociones, becas), justo después de la sección 01.
 *
 * Presenta datos ya resueltos por flacso_theme_get_carta_view().
 *
 * @package kadence-child-flacso
 */

if (!defined('ABSPATH')) {
    exit;
}

['info_extra' => $info_extra] = $args['carta'];

if (trim(wp_strip_all_tags($info_extra)) === '') {
    return;
}
?>
<aside class="fc-extra-info" aria-label="<?php esc_attr_e('Información extra', 'flacso-uruguay'); ?>">
    <div class="fc-extra-info__body">
        <?php echo wp_kses_post(wpautop($info_extra)); ?>
    </div>
</aside>
