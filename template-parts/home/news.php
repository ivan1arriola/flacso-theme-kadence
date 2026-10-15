<?php if (!defined('ABSPATH')) { exit; } ?>
<section class="home-section home-news" id="actualidad" aria-labelledby="home-news-title"><div class="home-container">
  <div class="home-section-heading"><div><p class="home-eyebrow">Noticias</p><h2 id="home-news-title">Noticias</h2></div><a class="home-heading-link" href="<?php echo esc_url(home_url('/categoria/novedades/')); ?>">Ver todas las noticias <span aria-hidden="true">↗</span></a></div>
  <?php if ($data['featured_news']) : ?><div class="home-featured-grid"><?php foreach ($data['featured_news'] as $post) { flacso_home_v2_news_card($post, 'featured', $data['has_pinned_news']); } ?></div><?php endif; ?>
  <?php if ($data['recent_news']) : ?><div class="home-recent-heading"><h3>Últimas noticias</h3><span>Publicaciones recientes</span></div><div class="home-recent-grid"><?php foreach ($data['recent_news'] as $post) { flacso_home_v2_news_card($post, 'recent', in_array((int) $post->ID, $data['extra_pinned_ids'], true)); } ?></div><?php endif; ?>
  <?php if (!$data['featured_news'] && !$data['recent_news']) : ?><p>Próximamente publicaremos nuevas noticias.</p><?php endif; ?>
</div></section>
