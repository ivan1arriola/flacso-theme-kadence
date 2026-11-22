<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

$programas_query = new WP_Query([
    'post_type'      => 'programa-academico',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => ['menu_order' => 'ASC', 'title' => 'ASC'],
    'no_found_rows'  => true,
]);

$total_programas = (int) $programas_query->post_count;
?>

<div class="flacso-programas-archive">
    <header class="programas-hero">
        <div class="site-container">
            <div class="hero-content">
                <span class="hero-kicker"><?php esc_html_e('Estructura Institucional', 'flacso-uruguay'); ?></span>
                <h1 class="hero-title"><?php esc_html_e('Programas Académicos', 'flacso-uruguay'); ?></h1>
                <p class="hero-desc">
                    <?php esc_html_e('Conocé las áreas de conocimiento y programas interdisciplinarios que nuclean las ofertas de posgrado, formación y seminarios de FLACSO Uruguay.', 'flacso-uruguay'); ?>
                </p>
                <div class="programas-stats">
                    <?php printf(esc_html__('%d Programas Académicos activos', 'flacso-uruguay'), $total_programas); ?>
                </div>
            </div>
        </div>
    </header>

    <<?php echo flacso_child_main_tag(); ?> class="programas-main" id="main">
        <div class="site-container">
            <div class="programas-grid">
                <?php if ($programas_query->have_posts()) : ?>
                    <?php while ($programas_query->have_posts()) : $programas_query->the_post(); ?>
                        <?php
                        $prog_id = get_the_ID();
                        $coordinacion = (array) get_post_meta($prog_id, 'coordinacion', true);
                        $correo = (string) get_post_meta($prog_id, 'correo', true);
                        $presentacion = (string) get_post_meta($prog_id, 'presentacion', true);
                        $resumen = wp_trim_words(wp_strip_all_tags($presentacion), 25);

                        $ofertas_count = count(get_posts([
                            'post_type'      => 'oferta-academica',
                            'meta_key'       => 'programa_academico_id',
                            'meta_value'     => $prog_id,
                            'post_status'    => 'publish',
                            'posts_per_page' => -1,
                            'fields'         => 'ids',
                        ]));

                        $seminarios_count = count(get_posts([
                            'post_type'      => 'seminario',
                            'meta_key'       => 'programa_academico_id',
                            'meta_value'     => $prog_id,
                            'post_status'    => 'publish',
                            'posts_per_page' => -1,
                            'fields'         => 'ids',
                        ]));

                        $coord_names = [];
                        foreach ($coordinacion as $c) {
                            $name = !empty($c['nombre']) ? $c['nombre'] : ($c['docente_id'] ? get_the_title($c['docente_id']) : '');
                            if ($name) $coord_names[] = $name;
                        }
                        ?>

                        <article class="programa-card">
                            <div class="programa-card__inner">
                                <?php if (has_post_thumbnail()) : ?>
                                    <div class="programa-card__image">
                                        <?php the_post_thumbnail('medium_large', ['class' => 'programa-card__img']); ?>
                                    </div>
                                <?php endif; ?>

                                <div class="programa-card__body">
                                    <div class="programa-card__meta-counts">
                                        <?php if ($ofertas_count > 0) : ?>
                                            <span class="count-pill count-pill--ofertas">
                                                🎓 <?php printf(esc_html__('%d Ofertas', 'flacso-uruguay'), $ofertas_count); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($seminarios_count > 0) : ?>
                                            <span class="count-pill count-pill--seminarios">
                                                📚 <?php printf(esc_html__('%d Seminarios', 'flacso-uruguay'), $seminarios_count); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <h2 class="programa-card__title">
                                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    </h2>

                                    <?php if (!empty($coord_names)) : ?>
                                        <p class="programa-card__coord">
                                            <strong><?php esc_html_e('Coordinación:', 'flacso-uruguay'); ?></strong>
                                            <?php echo esc_html(implode(', ', $coord_names)); ?>
                                        </p>
                                    <?php endif; ?>

                                    <?php if ($resumen) : ?>
                                        <p class="programa-card__desc"><?php echo esc_html($resumen); ?></p>
                                    <?php endif; ?>
                                </div>

                                <div class="programa-card__footer">
                                    <a href="<?php the_permalink(); ?>" class="btn-programa">
                                        <?php esc_html_e('Explorar Programa', 'flacso-uruguay'); ?> →
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; wp_reset_postdata(); ?>
                <?php else : ?>
                    <div class="empty-results">
                        <p><?php esc_html_e('No se encontraron programas académicos publicados.', 'flacso-uruguay'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </<?php echo flacso_child_main_tag(); ?>>
</div>

<?php
get_footer();
