<?php if (!defined('ABSPATH')) { exit; }
$event = $data['event'] ?? null;
if (!is_array($event)) { return; }
$event_id = absint($event['id'] ?? 0);
$title = trim(wp_strip_all_tags((string) ($event['title'] ?? '')));
if (!$event_id || $title === '') { return; }
$url = trim((string) ($event['link'] ?? ''));
$hora = trim((string) ($event['hora'] ?? ''));
$status = trim((string) ($event['status'] ?? ''));
$when = implode(' · ', array_filter([$hora, $status], static fn($part) => $part !== ''));
$image_id = get_post_thumbnail_id($event_id);
?>
<section class="agenda-section" id="agendaSection" aria-labelledby="agendaTitle"><div class="container agenda-grid"><div><p class="eyebrow">AGENDA ACADÉMICA</p><h2 id="agendaTitle">Próximas actividades</h2><a class="text-link" href="https://agenda.flacso.edu.uy/">Ver agenda completa <span aria-hidden="true">↗</span></a></div><article class="event"><div class="event-date"><strong><?php echo esc_html($event['day'] ?? ''); ?></strong><span><?php echo esc_html($event['month'] ?? ''); ?></span></div><div class="event-copy"><?php if ($when !== '') : ?><p><?php echo esc_html($when); ?></p><?php endif; ?><h3><?php if ($url !== '') : ?><a href="<?php echo esc_url($url); ?>"><?php endif; ?><?php echo esc_html($title); ?><?php if ($url !== '') : ?></a><?php endif; ?></h3><?php if ($url !== '') : ?><a class="text-link" href="<?php echo esc_url($url); ?>">Ver actividad <span aria-hidden="true">↗</span></a><?php endif; ?></div><?php if ($image_id) : ?><a class="event-poster" href="<?php echo esc_url($url); ?>" aria-label="<?php echo esc_attr('Ver afiche y actividad: ' . $title); ?>"><?php echo wp_get_attachment_image($image_id, 'medium_large', false, ['alt' => '', 'loading' => 'lazy']); ?></a><?php endif; ?></article></div></section>
