<?php if (!defined('ABSPATH')) { exit; }
$campaign = $data['campaign'] ?? null;
if (!is_array($campaign)) { return; }
?>
<section class="campaign" id="campaignSection" aria-label="Campaña de inscripciones"><div class="container campaign-grid"><p class="campaign-label"><span class="status-dot"></span> <?php echo esc_html($campaign['label'] ?? ''); ?></p><div><h2><?php echo esc_html($campaign['title'] ?? ''); ?></h2><p><?php echo esc_html($campaign['text'] ?? ''); ?></p></div><?php if (!empty($campaign['cta']['url']) && !empty($campaign['cta']['label'])) : ?><a href="<?php echo esc_url($campaign['cta']['url']); ?>" class="campaign-cta"><?php echo esc_html($campaign['cta']['label']); ?> <span aria-hidden="true">→</span></a><?php endif; ?></div></section>
