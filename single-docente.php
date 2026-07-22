<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) :
    the_post();

    $docente_id = get_the_ID();

    $prefijo_abrev = (string) get_post_meta($docente_id, 'prefijo_abrev', true);
    $titulo_academico = (string) get_post_meta($docente_id, 'titulo_academico', true);
    $nombre = (string) get_post_meta($docente_id, 'nombre', true);
    $apellido = (string) get_post_meta($docente_id, 'apellido', true);
    $cv_raw = (string) get_post_meta($docente_id, 'cv', true);

    $nombre_completo = function_exists('dp_nombre_completo')
        ? dp_nombre_completo($docente_id, true)
        : get_the_title($docente_id);

    // Si no hay prefijo en el nombre completo pero existe el abreviado, lo agregamos
    if ($prefijo_abrev && strpos($nombre_completo, $prefijo_abrev) === false) {
        $nombre_completo = $prefijo_abrev . ' ' . $nombre_completo;
    }

    $iniciales = function_exists('dp_iniciales')
        ? dp_iniciales($nombre, $apellido, 'DP')
        : strtoupper(substr($nombre_completo, 0, 2));

    $docentes_url = get_post_type_archive_link('docente');
    if (!$docentes_url) {
        $docentes_url = home_url('/docentes/');
    }

    $docente_correos = function_exists('dp_get_docente_emails') ? dp_get_docente_emails($docente_id) : [];
    $docente_redes = function_exists('dp_get_docente_socials') ? dp_get_docente_socials($docente_id) : [];

    // Color institucional FLACSO con gradiente moderno
    $hero_gradient = 'linear-gradient(135deg, #0f172a 0%, #1e293b 100%)';
    ?>

    <div class="flacso-docente-page">
        <div class="docente-container">

            <!-- Breadcrumb Moderno -->
            <nav class="docente-breadcrumb" aria-label="Navegación">
                <a href="<?php echo esc_url(home_url('/')); ?>">Inicio</a>
                <span class="sep">/</span>
                <a href="<?php echo esc_url($docentes_url); ?>">Docentes</a>
                <span class="sep">/</span>
                <span class="current"><?php echo esc_html($nombre_completo); ?></span>
            </nav>

            <!-- Hero Section Premium -->
            <header class="docente-hero">
                <div class="hero-bg" style="background: <?php echo esc_attr($hero_gradient); ?>;"></div>
                <div class="hero-content">
                    <div class="hero-main">
                        <div class="hero-avatar-wrapper">
                            <?php if (has_post_thumbnail($docente_id)) : ?>
                                <?php echo get_the_post_thumbnail($docente_id, 'large', ['class' => 'hero-avatar-img']); ?>
                            <?php else : ?>
                                <div class="hero-avatar-fallback"><?php echo esc_html($iniciales); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="hero-text">
                            <span class="hero-badge">Perfil Académico</span>
                            <h1 class="hero-name"><?php echo esc_html($nombre_completo); ?></h1>
                            <?php if ($titulo_academico) : ?>
                                <p class="hero-title"><?php echo esc_html($titulo_academico); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="hero-actions">
                        <a href="<?php echo esc_url($docentes_url); ?>" class="btn-back">
                            <span class="icon">←</span> Volver al listado
                        </a>
                    </div>
                </div>
            </header>

            <div class="docente-grid">
                <!-- Columna Principal: CV -->
                <main class="docente-main">
                    <section class="docente-card profile-section">
                        <div class="card-header">
                            <span class="icon">📜</span>
                            <h2>Trayectoria Profesional</h2>
                        </div>
                        <div class="cv-content">
                            <?php if ($cv_raw) : ?>
                                <?php echo wp_kses_post(wpautop($cv_raw)); ?>
                            <?php else : ?>
                                <p class="empty-state">No hay información de trayectoria disponible para este perfil.</p>
                            <?php endif; ?>
                        </div>
                    </section>
                </main>

                <!-- Sidebar: Contacto y Redes -->
                <aside class="docente-sidebar">
                    <section class="docente-card contact-section">
                        <div class="card-header">
                            <h3>Información de Contacto</h3>
                        </div>

                        <?php if (!empty($docente_correos)) : ?>
                            <div class="contact-methods">
                                <?php foreach ($docente_correos as $correo) :
                                    $email = $correo['email'] ?? '';
                                    if (!$email) continue;
                                    $label = !empty($correo['label']) ? $correo['label'] : 'Email';
                                ?>
                                    <div class="contact-item">
                                        <span class="label"><?php echo esc_html($label); ?></span>
                                        <a href="mailto:<?php echo esc_attr(antispambot($email)); ?>" class="value">
                                            <?php echo esc_html(antispambot($email)); ?>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else : ?>
                            <p class="empty-compact">Sin correos públicos</p>
                        <?php endif; ?>

                        <?php if (!empty($docente_redes)) : ?>
                            <div class="social-grid">
                                <?php foreach ($docente_redes as $red) :
                                    $url = $red['url'] ?? '';
                                    if (!$url) continue;
                                    $label = !empty($red['label']) ? $red['label'] : 'Perfil';
                                ?>
                                    <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener" class="social-btn">
                                        <?php echo esc_html($label); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>

                    <?php if (current_user_can('edit_post', $docente_id)) : ?>
                        <div class="admin-notice">
                            <p>Estás viendo este perfil como administrador.</p>
                            <a href="<?php echo esc_url(get_edit_post_link()); ?>" class="btn-admin">Editar en WordPress</a>
                        </div>
                    <?php endif; ?>
                </aside>
            </div>
        </div>
    </div>



<?php
endwhile;
get_footer();

