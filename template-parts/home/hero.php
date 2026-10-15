<?php if (!defined('ABSPATH')) { exit; } ?>
<section class="home-hero" aria-labelledby="home-hero-title">
  <div class="home-container home-hero-layout">
    <div class="home-hero-copy">
      <p class="home-eyebrow"><span aria-hidden="true"></span> <?php echo esc_html($data['hero_kicker']); ?></p>
      <?php if ($data['hero_title'] === 'Formación que conecta, conocimiento que transforma.') : ?><h1 id="home-hero-title">Formación que <em>conecta</em>, conocimiento que <em>transforma.</em></h1><?php else : ?><h1 id="home-hero-title"><?php echo esc_html($data['hero_title']); ?></h1><?php endif; ?>
      <a class="home-button" href="<?php echo esc_url($data['hero_cta']['url']); ?>"><?php echo esc_html($data['hero_cta']['label']); ?> <span aria-hidden="true">→</span></a>
    </div>
    <div class="home-hero-photo"><picture><?php if ($data['hero_mobile_image']) : ?><source media="(max-width: 760px)" srcset="<?php echo esc_url($data['hero_mobile_image']); ?>"><?php endif; ?><img src="<?php echo esc_url($data['hero_image']); ?>" alt="<?php echo esc_attr($data['hero_image_alt']); ?>" fetchpriority="high"></picture></div>
  </div>
</section>
