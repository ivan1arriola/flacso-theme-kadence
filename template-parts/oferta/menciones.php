<?php
/**
 * Menciones de la oferta como bloques, debajo del hero.
 *
 * Recibe `menciones` (lista de textos del meta `menciones`). Sin datos no dibuja nada.
 *
 * @package kadence-child-flacso
 */

if (!defined('ABSPATH')) {
    exit;
}

$items = [];
foreach ((array) ($args['menciones'] ?? []) as $item) {
    $texto = is_scalar($item) ? trim(wp_strip_all_tags((string) $item)) : '';
    if ($texto !== '') {
        $items[] = $texto;
    }
}

if ($items === []) {
    return;
}
?>
<section class="flacso-academic-menciones" aria-labelledby="flacso-oa-menciones-title">
    <div class="flacso-academic-menciones__inner">
        <h2 id="flacso-oa-menciones-title" class="flacso-academic-menciones__title"><?php esc_html_e('Menciones', 'flacso-uruguay'); ?></h2>
        <ul class="flacso-academic-menciones__list">
            <?php foreach ($items as $texto) : ?>
                <li class="flacso-academic-menciones__item"><?php echo esc_html($texto); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
