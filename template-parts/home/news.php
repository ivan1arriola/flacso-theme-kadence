<?php if (!defined('ABSPATH')) { exit; }
$featured = (array) ($data['featured_news'] ?? []);
$recent = (array) ($data['recent_news'] ?? []);
?>
<section class="news-section" id="noticias" aria-labelledby="newsTitle"><div class="container"><div class="section-head"><div><p class="eyebrow">VIDA ACADÉMICA E INSTITUCIONAL</p><h2 id="newsTitle">Noticias</h2></div><a class="text-link" href="<?php echo esc_url(home_url('/categoria/novedades/')); ?>">Ver todas las noticias <span aria-hidden="true">↗</span></a></div>
<?php if ($featured) : ?><div class="featured-news"><?php foreach (array_values($featured) as $i => $post) { flacso_home_news_card($post, $i === 0 ? 'featured' : 'secondary', function_exists('is_sticky') && is_sticky((int) $post->ID)); } ?></div><?php endif; ?>
<?php if ($recent) : ?><div class="recent-heading"><h3>Últimas noticias</h3></div><div class="recent-grid"><?php foreach ($recent as $post) { flacso_home_news_card($post, 'recent', false); } ?></div><?php endif; ?>
<?php if (!$featured && !$recent) : ?><p>Próximamente publicaremos nuevas noticias.</p><?php endif; ?>
</div></section>
