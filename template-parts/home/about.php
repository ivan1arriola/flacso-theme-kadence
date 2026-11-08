<?php if (!defined('ABSPATH')) { exit; }
$content = trim((string) ($data['institution']['content'] ?? ''));
?>
<section class="about-section" id="institucional" aria-labelledby="aboutTitle"><div class="container about-grid"><div class="about-identity"><img src="https://flacso.edu.uy/wp-content/uploads/2026/02/cropped-cropped-Logos-FLACSO-Claro.png" alt="FLACSO Uruguay" loading="lazy"><p>Formación.<br>Investigación.<br>Cooperación.</p><span class="about-rule"></span></div><div><h2 id="aboutTitle">¿Qué es FLACSO Uruguay?</h2><?php if ($content !== '') { echo wp_kses_post(wpautop($content)); } ?><a class="text-link" href="<?php echo esc_url(home_url('/sobre-nosotros/')); ?>">Sobre la institución <span aria-hidden="true">↗</span></a></div></div></section>
