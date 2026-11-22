<?php if (!defined('ABSPATH')) { exit; }
$open = array_values((array) ($data['open_offers'] ?? []));
if (!$open) { return; }
?>
<section class="open-section" id="inscripciones" aria-labelledby="openTitle"><div class="container"><div class="section-head"><div><p class="eyebrow">INSCRIPCIONES ABIERTAS</p><h2 id="openTitle">Propuestas para empezar ahora</h2></div><a class="text-link" href="<?php echo esc_url(home_url('/formacion/')); ?>">Toda la oferta <span aria-hidden="true">↗</span></a></div>
<ul class="open-list">
<?php foreach ($open as $offer) :
    $style = $offer['color'] !== '' ? ' style="--open-color:' . esc_attr($offer['color']) . '"' : '';
    $image = function_exists('flacso_child_card_image_html')
        ? flacso_child_card_image_html((int) $offer['image_id'], (string) $offer['image_url'], '', '(max-width: 767px) 72vw, 300px')
        : '';
?>
<li><a class="open-card" href="<?php echo esc_url($offer['url']); ?>"<?php echo $style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- valor escapado arriba. ?>>
<?php if ($image !== '') : ?><span class="open-card__media"><?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML de WordPress. ?></span><?php endif; ?>
<span class="open-card__body">
<?php if ($offer['type'] !== '') : ?><span class="open-card__type"><?php echo esc_html($offer['type']); ?></span><?php endif; ?>
<h3><?php echo esc_html($offer['title']); ?></h3>
<?php if ($offer['start'] !== '') : ?><span class="open-card__date"><?php echo esc_html($offer['start']); ?></span><?php endif; ?>
<span class="open-card__cta">Ver propuesta <b aria-hidden="true">→</b></span>
</span></a></li>
<?php endforeach; ?>
</ul></div></section>
