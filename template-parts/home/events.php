<?php if (!defined('ABSPATH')) { exit; }
// Sin actividades próximas no se muestra la sección (ni su título).
if (empty($data['events'])) { return; }
?>
<section class="home-section home-events" id="actividades" aria-labelledby="home-events-title"><div class="home-container">
  <div class="home-section-heading"><h2 id="home-events-title">Próximas actividades</h2></div>
  <div class="home-events-grid home-events-grid--<?php echo (int) min(3, max(1, count($data['events']))); ?>">
    <?php foreach ($data['events'] as $event) :
      $id = absint($event['id'] ?? 0);
      $url = trim((string) ($event['link'] ?? ''));
      $title = trim(wp_strip_all_tags((string) ($event['title'] ?? '')));
      if (!$id || !$title) { continue; }
      $image_id = get_post_thumbnail_id($id);
      ?>
      <article class="home-event-card">
        <?php if ($url) : ?><a href="<?php echo esc_url($url); ?>" class="home-event-media" aria-label="<?php echo esc_attr($title); ?>"><?php else : ?><div class="home-event-media"><?php endif; ?>
          <?php if ($image_id) : echo wp_get_attachment_image($image_id, 'medium_large', false, ['alt' => '', 'loading' => 'lazy']); else : ?><span class="home-event-date-art" aria-hidden="true"><?php echo esc_html($event['day'] ?? ''); ?><small><?php echo esc_html($event['month'] ?? ''); ?></small></span><?php endif; ?>
        <?php if ($url) : ?></a><?php else : ?></div><?php endif; ?>
        <div class="home-event-copy"><p class="home-event-date"><?php echo esc_html(trim(($event['day'] ?? '') . ' ' . ($event['month'] ?? ''))); ?></p><?php $status = trim((string) ($event['status'] ?? '')); $hora = trim((string) ($event['hora'] ?? '')); if ($status !== '' || $hora !== '') : ?><p class="home-event-meta"><?php if ($status !== '') : ?><span class="home-event-pill"><?php echo esc_html($status); ?></span><?php endif; ?><?php if ($hora !== '') : ?><span class="home-event-time"><?php echo esc_html($hora); ?></span><?php endif; ?></p><?php endif; ?><h3><?php if ($url) : ?><a href="<?php echo esc_url($url); ?>"><?php endif; ?><?php echo esc_html($title); ?><?php if ($url) : ?></a><?php endif; ?></h3><?php if ($url) : ?><a class="home-text-link" href="<?php echo esc_url($url); ?>">Ver actividad <span aria-hidden="true">→</span></a><?php endif; ?></div>
      </article>
    <?php endforeach; ?>
  </div>
</div></section>
