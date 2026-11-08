<?php if (!defined('ABSPATH')) { exit; }
$icons = [
    'maestrias' => '<path d="m3 11 13-7 13 7-13 7-13-7ZM8 14v9c5 4 11 4 16 0v-9M29 11v11"/>',
    'especializaciones' => '<circle cx="14" cy="13" r="9"/><path d="m21 20 7 8M10 13h8M14 9v8"/>',
    'diplomados' => '<path d="M5 6h22v21H5zM10 12h12M10 17h12M10 22h7"/>',
    'diplomas' => '<path d="M7 4h18v24H7zM11 10h10M11 15h10"/><circle cx="21" cy="23" r="5"/>',
    'seminarios' => '<path d="M4 6h24v17H15l-8 5v-5H4zM9 12h14M9 17h9"/>',
];
?>
<section class="offer-section" id="oferta" aria-labelledby="offerTitle"><div class="container"><div class="section-head"><div><p class="eyebrow">OFERTA ACADÉMICA</p><h2 id="offerTitle">Encontrá tu próxima formación</h2></div><a class="text-link" href="<?php echo esc_url(home_url('/formacion/')); ?>">Toda la oferta <span aria-hidden="true">↗</span></a></div><div class="offer-grid"><?php foreach ($icons as $slug => $icon) : $card = $data['cards'][$slug] ?? null; if (!$card) { continue; } ?><a href="<?php echo esc_url($card['url']); ?>"><svg viewBox="0 0 32 32" aria-hidden="true"><?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- constante del tema ?></svg><h3><?php echo esc_html($card['title']); ?></h3><span aria-hidden="true">↗</span></a><?php endforeach; ?></div></div></section>
