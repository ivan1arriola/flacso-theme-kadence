<?php

if (!defined('ABSPATH')) { exit; }

function flacso_home_v2_render(): string {
    $model = class_exists('FLACSO_Home_Campaign') ? FLACSO_Home_Campaign::view_model() : [];
    $campaign = is_array($model['campaign'] ?? null) ? $model['campaign'] : [];
    $image_id = absint($campaign['desktop_image_id'] ?? 0);
    $image = $image_id && function_exists('wp_get_attachment_image_url') ? wp_get_attachment_image_url($image_id, 'full') : '';
    $primary = is_array($campaign['cta_primary'] ?? null) ? $campaign['cta_primary'] : null;
    $secondary = is_array($campaign['cta_secondary'] ?? null) ? $campaign['cta_secondary'] : null;
    ob_start(); ?>
    <main id="main" class="flacso-home-v2" role="main">
        <section class="flacso-home-v2__hero"<?php echo $image ? ' style="--flacso-home-v2-image:url(' . esc_url($image) . ')"' : ''; ?>>
            <div class="flacso-home-v2__inner">
                <?php if (!empty($campaign['kicker'])) : ?><p class="flacso-home-v2__kicker"><?php echo esc_html($campaign['kicker']); ?></p><?php endif; ?>
                <h1><?php echo esc_html($campaign['title'] ?? 'FLACSO Uruguay'); ?></h1>
                <?php if (!empty($campaign['description'])) : ?><p class="flacso-home-v2__description"><?php echo esc_html($campaign['description']); ?></p><?php endif; ?>
                <?php if ($primary || $secondary) : ?><div class="flacso-home-v2__actions">
                    <?php foreach ([$primary, $secondary] as $index => $cta) : if (!$cta) { continue; } ?><a class="flacso-home-v2__cta <?php echo $index ? 'flacso-home-v2__cta--secondary' : ''; ?>" href="<?php echo esc_url($cta['url']); ?>"><?php echo esc_html($cta['label']); ?></a><?php endforeach; ?>
                </div><?php endif; ?>
            </div>
        </section>
        <section class="flacso-home-v2__links" aria-label="Formación FLACSO Uruguay">
            <a href="<?php echo esc_url(home_url('/formacion/')); ?>"><strong>Formación</strong><span>Conocé nuestra propuesta académica.</span></a>
            <a href="<?php echo esc_url(home_url('/formacion/seminarios/')); ?>"><strong>Seminarios</strong><span>Actualización y formación intensiva.</span></a>
            <a href="<?php echo esc_url(home_url('/contactos/')); ?>"><strong>Consultas</strong><span>Estamos para orientarte.</span></a>
        </section>
    </main>
    <?php return (string) ob_get_clean();
}
