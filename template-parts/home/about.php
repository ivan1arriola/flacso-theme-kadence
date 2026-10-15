<?php if (!defined('ABSPATH')) { exit; } ?>
<section class="home-section home-about" id="institucional" aria-labelledby="home-about-title"><div class="home-container home-about-grid">
  <div class="home-about-logo"><img src="https://flacso.edu.uy/wp-content/uploads/2026/02/cropped-cropped-Logos-FLACSO-Claro.png" alt="Logo de FLACSO Uruguay" loading="lazy"></div>
  <div class="home-about-copy"><h2 id="home-about-title">¿Qué es FLACSO Uruguay?</h2>
  <?php
  $content = trim((string) ($data['institution']['content'] ?? ''));
  if ($content) { echo wp_kses_post($content); }
  ?>
  <div class="home-about-links"><a class="home-heading-link" href="<?php echo esc_url(home_url('/sobre-nosotros/')); ?>">Sobre la institución ↗</a><a class="home-heading-link" href="<?php echo esc_url(home_url('/institucional/investigaciones/')); ?>">Investigaciones ↗</a></div>
  </div>
</div></section>
