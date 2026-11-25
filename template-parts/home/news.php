<?php if (!defined('ABSPATH')) { exit; }
$featured = (array) ($data['featured_news'] ?? []);
$recent = (array) ($data['recent_news'] ?? []);
?>
<section class="news-section" id="noticias" aria-labelledby="newsTitle"><div class="container"><div class="section-head"><div><h2 id="newsTitle">Noticias</h2></div><a class="text-link" href="<?php echo esc_url(home_url('/categoria/novedades/')); ?>">Ver todas las noticias <span aria-hidden="true">↗</span></a></div>
<?php if ($featured || $recent) : ?>
<div class="news-bento<?php echo count($featured) + count($recent) < 3 ? ' news-bento--compact' : ''; ?>">
<?php foreach (array_values($featured) as $i => $post) { flacso_home_news_card($post, $i === 0 ? 'featured' : 'secondary', function_exists('is_sticky') && is_sticky((int) $post->ID)); } ?>
<?php foreach ($recent as $post) { flacso_home_news_card($post, 'recent', function_exists('is_sticky') && is_sticky((int) $post->ID)); } ?>
</div>
<?php endif; ?>
<?php if (!$featured && !$recent) : ?><p>Próximamente publicaremos nuevas noticias.</p><?php endif; ?>
</div></section>
