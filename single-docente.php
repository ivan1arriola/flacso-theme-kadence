<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) :
    the_post();

    $docente_id       = get_the_ID();
    $prefijo_abrev    = (string) get_post_meta($docente_id, 'prefijo_abrev', true);
    $titulo_academico = (string) get_post_meta($docente_id, 'titulo_academico', true);
    $cargo            = (string) get_post_meta($docente_id, 'cargo', true);
    $roles            = class_exists('Docente_Meta') ? Docente_Meta::get_roles($docente_id) : ['docente'];
    $nombre           = (string) get_post_meta($docente_id, 'nombre', true);
    $apellido         = (string) get_post_meta($docente_id, 'apellido', true);
    $cv_raw           = (string) get_post_meta($docente_id, 'cv', true);

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

    if (in_array('administrativo', $roles, true) && !in_array('docente', $roles, true)) {
        $archive_url   = home_url('/equipo/administrativo/');
        $archive_label = __('Equipo Administrativo', 'flacso-uruguay');
    } elseif (in_array('docente', $roles, true) && !in_array('administrativo', $roles, true)) {
        $archive_url   = home_url('/equipo/docentes/');
        $archive_label = __('Cuerpo Docente', 'flacso-uruguay');
    } else {
        $archive_url   = home_url('/equipo/');
        $archive_label = __('Equipo FLACSO', 'flacso-uruguay');
    }

    $docente_redes = function_exists('dp_get_docente_socials') ? dp_get_docente_socials($docente_id) : [];

    // Gradiente institucional
    $hero_gradient = 'linear-gradient(135deg, #0f172a 0%, #1e293b 100%)';
    ?>

    <div class="flacso-docente-page">
        <div class="docente-container">

            <!-- Breadcrumb Moderno -->
            <nav class="docente-breadcrumb" aria-label="<?php esc_attr_e('Navegación', 'flacso-uruguay'); ?>">
                <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Inicio', 'flacso-uruguay'); ?></a>
                <span class="sep">/</span>
                <a href="<?php echo esc_url(home_url('/equipo/')); ?>"><?php esc_html_e('Equipo', 'flacso-uruguay'); ?></a>
                <span class="sep">/</span>
                <a href="<?php echo esc_url($archive_url); ?>"><?php echo esc_html($archive_label); ?></a>
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
                            <div class="hero-badges" style="display: flex; gap: 8px; margin-bottom: 8px; flex-wrap: wrap;">
                                <?php if (in_array('docente', $roles, true)) : ?>
                                    <span class="hero-badge" style="background: rgba(56, 189, 248, 0.2); color: #7dd3fc; border: 1px solid rgba(56, 189, 248, 0.3);">
                                        <?php esc_html_e('Cuerpo Docente', 'flacso-uruguay'); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (in_array('administrativo', $roles, true)) : ?>
                                    <span class="hero-badge" style="background: rgba(251, 191, 36, 0.2); color: #fde68a; border: 1px solid rgba(251, 191, 36, 0.3);">
                                        <?php esc_html_e('Equipo de Gestión / Administrativo', 'flacso-uruguay'); ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <h1 class="hero-name"><?php echo esc_html($nombre_completo); ?></h1>

                            <?php if ($cargo) : ?>
                                <p class="hero-cargo" style="font-size: 1.15rem; color: #cbd5e1; font-weight: 500; margin: 4px 0 6px;">
                                    <?php echo esc_html($cargo); ?>
                                </p>
                            <?php endif; ?>

                            <?php if ($titulo_academico) : ?>
                                <p class="hero-title" style="color: #94a3b8; font-size: 0.95rem;"><?php echo esc_html($titulo_academico); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="hero-actions">
                        <a href="<?php echo esc_url($archive_url); ?>" class="btn-back">
                            <span class="icon">←</span> <?php printf(esc_html__('Volver a %s', 'flacso-uruguay'), esc_html($archive_label)); ?>
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
                            <h2><?php esc_html_e('Trayectoria Profesional', 'flacso-uruguay'); ?></h2>
                        </div>
                        <div class="cv-content">
                            <?php if ($cv_raw) : ?>
                                <?php echo wp_kses_post(wpautop($cv_raw)); ?>
                            <?php else : ?>
                                <p class="empty-state"><?php esc_html_e('No hay información de trayectoria disponible para este perfil.', 'flacso-uruguay'); ?></p>
                            <?php endif; ?>
                        </div>
                    </section>
                </main>

                <!-- Sidebar: Redes y Enlaces -->
                <aside class="docente-sidebar">
                    <?php if (!empty($docente_redes)) : ?>
                        <section class="docente-card contact-section">
                            <div class="card-header">
                                <h3><?php esc_html_e('Redes y Enlaces', 'flacso-uruguay'); ?></h3>
                            </div>
                            <div class="social-grid">
                                <?php foreach ($docente_redes as $red) :
                                    $url = $red['url'] ?? '';
                                    if (!$url) continue;
                                    $label = !empty($red['label']) ? $red['label'] : __('Perfil', 'flacso-uruguay');
                                ?>
                                    <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer" class="social-btn">
                                        <?php echo esc_html($label); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endif; ?>

                    <?php if (current_user_can('edit_post', $docente_id)) : ?>
                        <div class="admin-notice">
                            <p><?php esc_html_e('Estás viendo este perfil como administrador.', 'flacso-uruguay'); ?></p>
                            <a href="<?php echo esc_url(get_edit_post_link()); ?>" class="btn-admin"><?php esc_html_e('Editar en WordPress', 'flacso-uruguay'); ?></a>
                        </div>
                    <?php endif; ?>
                </aside>
            </div>
        </div>
    </div>

<?php
endwhile;
get_footer();
