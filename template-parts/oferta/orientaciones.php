<?php
/**
 * Orientaciones de la oferta como bloques, debajo del hero.
 *
 * Recibe `orientaciones` (lista de textos del meta `orientaciones`). Sin datos no dibuja nada.
 *
 * @package kadence-child-flacso
 */

if (!defined('ABSPATH')) {
    exit;
}

$items = [];
foreach ((array) ($args['orientaciones'] ?? []) as $item) {
    $texto = is_scalar($item) ? trim(wp_strip_all_tags((string) $item)) : '';
    if ($texto !== '') {
        $items[] = $texto;
    }
}

if ($items === []) {
    return;
}
?>
<section class="flacso-academic-orientations" aria-labelledby="flacso-oa-orientations-title">
    <div class="flacso-academic-orientations__inner">
        <h2 id="flacso-oa-orientations-title" class="flacso-academic-orientations__title"><?php esc_html_e('Orientaciones', 'flacso-uruguay'); ?></h2>
        <ul class="flacso-academic-orientations__list">
            <?php foreach ($items as $texto) : ?>
                <li class="flacso-academic-orientations__item"><?php echo esc_html($texto); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
