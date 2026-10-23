<?php if (!defined('ABSPATH')) { exit; } ?>
<section class="home-section home-offer" id="oferta" aria-labelledby="home-offer-title"><div class="home-container">
  <div class="home-section-heading"><div><p class="home-eyebrow">Oferta académica</p><h2 id="home-offer-title">Encontrá tu próxima formación</h2></div><a class="home-heading-link" href="<?php echo esc_url(home_url('/formacion/')); ?>">Toda la oferta <span aria-hidden="true">↗</span></a></div>
  <nav class="home-offer-grid" aria-label="Categorías de formación">
  <?php
  $icons = [
    'maestrias' => '<path d="m3 12 13-7 13 7-13 7-13-7ZM9 16v7c5 4 9 4 14 0v-7M29 12v11"/>',
    'especializaciones' => '<circle cx="13" cy="13" r="8"/><path d="m19 19 9 9M13 9v8M9 13h8"/>',
    'diplomados' => '<path d="M6 6h20v20H6zM10 12h12M10 17h12M10 22h7"/>',
    'diplomas' => '<path d="M7 4h18v24H7zM11 10h10M11 15h10M11 20h6"/><circle cx="23" cy="24" r="5"/>',
    'seminarios' => '<path d="M5 6h22v17H15l-8 5v-5H5zM10 12h12M10 17h8"/>',
  ];
  foreach ($data['cards'] as $slug => $card) : if (!$card) { continue; } ?>
    <a class="home-offer-card home-offer-card--<?php echo esc_attr(sanitize_html_class($slug)); ?>" href="<?php echo esc_url($card['url']); ?>"><span class="home-offer-icon" aria-hidden="true"><svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><?php echo $icons[$slug] ?? $icons['seminarios']; ?></svg></span><h3><?php echo esc_html($card['title']); ?></h3><span class="home-offer-bottom">Explorar <span aria-hidden="true">↗</span></span></a>
  <?php endforeach; ?>
  </nav>
</div></section>
