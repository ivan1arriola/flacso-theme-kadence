<?php
/**
 * Participantes del evento: personas del equipo asociadas, con foto, cargo y perfil.
 *
 * Recibe `ids` (ids de docente en el orden elegido). Sin personas no dibuja nada.
 *
 * @package kadence-child-flacso
 */

if (!defined('ABSPATH')) {
    exit;
}

$ids = array_filter(array_map('absint', (array) ($args['ids'] ?? [])));
if ($ids === []) {
    return;
}
?>
<section class="flacso-evento-speakers" aria-labelledby="flacso-evento-speakers-title">
    <h2 id="flacso-evento-speakers-title">
        <?php echo esc_html(_n('Participante', 'Participantes', count($ids), 'kadence-child-flacso')); ?>
    </h2>
    <ul class="flacso-evento-speakers__list">
        <?php foreach ($ids as $person_id) :
            $name = function_exists('dp_nombre_completo') ? dp_nombre_completo($person_id) : get_the_title($person_id);
            $cargo = trim((string) get_post_meta($person_id, 'cargo', true));
            $bio = trim((string) get_post_meta($person_id, 'cv', true)); // biografía completa
            $initials = function_exists('dp_iniciales')
                ? dp_iniciales((string) get_post_meta($person_id, 'nombre', true), (string) get_post_meta($person_id, 'apellido', true), '')
                : '';
            ?>
            <li class="flacso-evento-speaker">
                <a class="flacso-evento-speaker__photo" href="<?php echo esc_url(get_permalink($person_id)); ?>" tabindex="-1" aria-hidden="true">
                    <?php if (has_post_thumbnail($person_id)) : ?>
                        <?php echo get_the_post_thumbnail($person_id, 'medium', ['alt' => '', 'loading' => 'lazy']); ?>
                    <?php else : ?>
                        <span class="flacso-evento-speaker__initials"><?php echo esc_html($initials); ?></span>
                    <?php endif; ?>
                </a>
                <div class="flacso-evento-speaker__body">
                    <h3><a href="<?php echo esc_url(get_permalink($person_id)); ?>"><?php echo esc_html($name); ?></a></h3>
                    <?php if ($cargo !== '') : ?><p class="flacso-evento-speaker__role"><?php echo esc_html($cargo); ?></p><?php endif; ?>
                    <?php if ($bio !== '') : ?><div class="flacso-evento-speaker__bio"><?php echo wp_kses_post(wpautop($bio)); ?></div><?php endif; ?>
                    <a class="flacso-evento-speaker__link" href="<?php echo esc_url(get_permalink($person_id)); ?>">
                        <?php esc_html_e('Ver perfil', 'kadence-child-flacso'); ?>
                    </a>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
