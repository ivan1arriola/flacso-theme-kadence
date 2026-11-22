<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) :
    the_post();

    $prog_id      = get_the_ID();
    $titulo       = get_the_title();
    $correo       = (string) get_post_meta($prog_id, 'correo', true);
    $presentacion = (string) get_post_meta($prog_id, 'presentacion', true);
    $coordinacion = flacso_theme_program_coordination($prog_id);

    // ?id=102,103 limita la página a esas ofertas/seminarios (solo los que pertenecen al programa).
    $ids_filtro = [];
    if (isset($_GET['id'])) {
        $raw = is_array($_GET['id']) ? implode(',', array_map('strval', $_GET['id'])) : (string) wp_unslash($_GET['id']);
        $ids_filtro = array_values(array_unique(array_filter(array_map('absint', preg_split('/[^0-9]+/', $raw)))));
    }
    $filtrado = !empty($ids_filtro);

    // Consultar Ofertas Académicas del Programa
    $ofertas = get_posts([
        'post_type'      => 'oferta-academica',
        'meta_key'       => 'programa_academico_id',
        'meta_value'     => $prog_id,
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'post__in'       => $filtrado ? $ids_filtro : [],
    ]);

    // Consultar Seminarios del Programa
    $seminarios = get_posts([
        'post_type'      => 'seminario',
        'meta_key'       => 'programa_academico_id',
        'meta_value'     => $prog_id,
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'post__in'       => $filtrado ? $ids_filtro : [],
    ]);
    ?>

    <div class="flacso-programa-page">
        <!-- Hero Section -->
        <header class="programa-hero">
            <div class="site-container">
                <nav class="programa-breadcrumb" aria-label="<?php esc_attr_e('Navegación', 'flacso-uruguay'); ?>">
                    <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Inicio', 'flacso-uruguay'); ?></a>
                    <span class="sep">/</span>
                    <a href="<?php echo esc_url(home_url('/programas/')); ?>"><?php esc_html_e('Programas Académicos', 'flacso-uruguay'); ?></a>
                    <span class="sep">/</span>
                    <span class="current"><?php echo esc_html($titulo); ?></span>
                    <?php if ($filtrado) : ?>
                        <span class="sep">/</span>
                        <a href="<?php echo esc_url(get_permalink($prog_id)); ?>"><?php esc_html_e('Ver todas las propuestas', 'flacso-uruguay'); ?></a>
                    <?php endif; ?>
                </nav>

                <div class="programa-hero__content">
                    <div class="programa-hero__text">
                        <span class="hero-kicker"><?php esc_html_e('Programa Académico', 'flacso-uruguay'); ?></span>
                        <h1 class="hero-title"><?php echo esc_html($titulo); ?></h1>

                        <?php if ($correo) : ?>
                            <p class="programa-hero__contact">
                                <span class="icon">✉️</span>
                                <a href="mailto:<?php echo esc_attr($correo); ?>"><?php echo esc_html($correo); ?></a>
                            </p>
                        <?php endif; ?>
                    </div>

                    <?php if (has_post_thumbnail()) : ?>
                        <div class="programa-hero__image">
                            <?php the_post_thumbnail('large', ['class' => 'programa-hero__img']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <div class="site-container programa-layout">
            <<?php echo flacso_child_main_tag(); ?> class="programa-main-content" id="main">
                <!-- Presentación del Programa -->
                <?php if ($presentacion && !$filtrado) : ?>
                    <section class="programa-section programa-presentacion">
                        <h2 class="section-title">
                            <span class="icon">📖</span> <?php esc_html_e('Presentación del Programa', 'flacso-uruguay'); ?>
                        </h2>
                        <div class="programa-prose">
                            <?php echo wp_kses_post(wpautop($presentacion)); ?>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- Ofertas Académicas -->
                <?php if (!empty($ofertas)) : ?>
                    <section class="programa-section programa-ofertas">
                        <h2 class="section-title">
                            <span class="icon">🎓</span> <?php esc_html_e('Oferta Académica y Posgrados', 'flacso-uruguay'); ?>
                        </h2>
                        <div class="academic-cards-grid">
                            <?php foreach ($ofertas as $of) :
                                $of_id = $of->ID;
                                $of_url = get_permalink($of_id);
                                $tipo_terms = wp_get_post_terms($of_id, 'tipo-oferta-academica');
                                $tipo_label = !empty($tipo_terms) && !is_wp_error($tipo_terms) ? $tipo_terms[0]->name : 'Posgrado';

                                // Cohorte vigente
                                $context_of = function_exists('flacso_theme_get_offer_context') ? flacso_theme_get_offer_context($of_id) : [];
                                $current_cohort = $context_of['instance'] ?? null;
                            ?>
                                <article class="academic-card">
                                    <div class="academic-card__image">
                                        <?php if (has_post_thumbnail($of_id)) : ?>
                                            <?php echo get_the_post_thumbnail($of_id, 'medium_large'); ?>
                                        <?php else : ?>
                                            <div class="academic-card__fallback">FLACSO</div>
                                        <?php endif; ?>
                                        <span class="academic-badge"><?php echo esc_html($tipo_label); ?></span>
                                    </div>
                                    <div class="academic-card__body">
                                        <h3 class="academic-card__title">
                                            <a href="<?php echo esc_url($of_url); ?>"><?php echo esc_html($of->post_title); ?></a>
                                        </h3>
                                        <?php if ($current_cohort && !empty($current_cohort['id'])) : ?>
                                            <p class="academic-card__cohort">
                                                <strong><?php echo esc_html($current_cohort['label'] ?? 'Cohorte'); ?></strong>
                                                <?php if (!empty($current_cohort['fechas_formateadas'])) : ?>
                                                    • <?php echo esc_html($current_cohort['fechas_formateadas']); ?>
                                                <?php endif; ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="academic-card__footer">
                                        <a href="<?php echo esc_url($of_url); ?>" class="btn-academic">
                                            <?php esc_html_e('Ver Información', 'flacso-uruguay'); ?> →
                                        </a>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- Seminarios -->
                <?php if (!empty($seminarios)) : ?>
                    <section class="programa-section programa-seminarios">
                        <h2 class="section-title">
                            <span class="icon">📚</span> <?php esc_html_e('Seminarios y Cursos de Actualización', 'flacso-uruguay'); ?>
                        </h2>
                        <div class="academic-cards-grid">
                            <?php foreach ($seminarios as $sem) :
                                $sem_id = $sem->ID;
                                $sem_url = get_permalink($sem_id);

                                $current_edition = null;
                                if (function_exists('flacso_theme_get_seminar_context')) {
                                    $sem_context = flacso_theme_get_seminar_context($sem_id);
                                    if (!empty($sem_context['instance']['id'])) {
                                        $current_edition = $sem_context['instance'];
                                        if (empty($current_edition['nombre']) && !empty($current_edition['label'])) {
                                            $current_edition['nombre'] = $current_edition['label'];
                                        }
                                    }
                                } elseif (class_exists('FLACSO_Academic_Catalog')) {
                                    $sem_data = FLACSO_Academic_Catalog::get_seminar($sem_id);
                                    $current_edition = $sem_data['edicion_vigente'] ?? null;
                                }
                            ?>
                                <article class="academic-card academic-card--seminar">
                                    <div class="academic-card__image">
                                        <?php if (has_post_thumbnail($sem_id)) : ?>
                                            <?php echo get_the_post_thumbnail($sem_id, 'medium_large'); ?>
                                        <?php else : ?>
                                            <div class="academic-card__fallback">FLACSO</div>
                                        <?php endif; ?>
                                        <span class="academic-badge academic-badge--seminar"><?php esc_html_e('Seminario', 'flacso-uruguay'); ?></span>
                                    </div>
                                    <div class="academic-card__body">
                                        <h3 class="academic-card__title">
                                            <a href="<?php echo esc_url($sem_url); ?>"><?php echo esc_html($sem->post_title); ?></a>
                                        </h3>
                                        <?php if ($current_edition) : ?>
                                            <p class="academic-card__cohort">
                                                <strong><?php echo esc_html($current_edition['nombre'] ?? 'Edición'); ?></strong>
                                                <?php if (!empty($current_edition['fecha_inicio'])) : ?>
                                                    • Inicio: <?php echo esc_html($current_edition['fecha_inicio']); ?>
                                                <?php endif; ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="academic-card__footer">
                                        <a href="<?php echo esc_url($sem_url); ?>" class="btn-academic">
                                            <?php esc_html_e('Ver Seminario', 'flacso-uruguay'); ?> →
                                        </a>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>
            </<?php echo flacso_child_main_tag(); ?>>

            <!-- Sidebar: Coordinación -->
            <aside class="programa-sidebar">
                <?php if (!empty($coordinacion)) : ?>
                    <section class="programa-sidebar-card">
                        <h3 class="sidebar-title"><?php esc_html_e('Equipo de Coordinación', 'flacso-uruguay'); ?></h3>
                        <div class="coordinacion-list">
                            <?php foreach ($coordinacion as $c) :
                                $doc_id = absint($c['docente_id'] ?? 0);
                                $rol = (string) ($c['rol'] ?? 'Coordinación');
                                $name = !empty($c['nombre']) ? $c['nombre'] : ($doc_id ? get_the_title($doc_id) : '');
                                if (!$name) continue;

                                $profile_url = $doc_id ? get_permalink($doc_id) : '';
                                $thumb = $doc_id && has_post_thumbnail($doc_id) ? get_the_post_thumbnail($doc_id, 'thumbnail', ['class' => 'coord-avatar-img']) : '';
                                $iniciales = strtoupper(substr($name, 0, 2));
                            ?>
                                <div class="coord-item">
                                    <div class="coord-avatar">
                                        <?php if ($thumb) : ?>
                                            <?php echo $thumb; ?>
                                        <?php else : ?>
                                            <div class="coord-fallback"><?php echo esc_html($iniciales); ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="coord-info">
                                        <?php if ($profile_url) : ?>
                                            <h4 class="coord-name"><a href="<?php echo esc_url($profile_url); ?>"><?php echo esc_html($name); ?></a></h4>
                                        <?php else : ?>
                                            <h4 class="coord-name"><?php echo esc_html($name); ?></h4>
                                        <?php endif; ?>
                                        <p class="coord-role"><?php echo esc_html($rol); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <?php if ($correo) : ?>
                    <section class="programa-sidebar-card contact-box">
                        <h3 class="sidebar-title"><?php esc_html_e('Contacto del Programa', 'flacso-uruguay'); ?></h3>
                        <p><?php esc_html_e('Para consultas académicas o administrativas sobre este programa:', 'flacso-uruguay'); ?></p>
                        <a href="mailto:<?php echo esc_attr($correo); ?>" class="btn-contact-programa">
                            ✉️ <?php echo esc_html($correo); ?>
                        </a>
                    </section>
                <?php endif; ?>
            </aside>
        </div>
    </div>

<?php
endwhile;
get_footer();
