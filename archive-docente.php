<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

$docentes_query = new WP_Query([
    'post_type' => 'docente',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
    'no_found_rows' => true,
]);

$total_docentes = (int) $docentes_query->post_count;
?>

<div class="flacso-docentes-archive">
    <!-- Hero Section con buscador -->
    <header class="archive-hero">
        <div class="site-container">
            <div class="hero-content">
                <span class="hero-kicker">Comunidad Académica</span>
                <h1 class="hero-title">Nuestros Docentes</h1>
                <p class="hero-desc">Conoce a los profesionales y académicos que integran el cuerpo docente de FLACSO Uruguay.</p>

                <div class="search-container">
                    <div class="search-box">
                        <span class="search-icon">🔍</span>
                        <input type="text" id="archive-search" placeholder="Buscar por nombre o especialidad..." aria-label="Buscar docentes">
                    </div>
                    <div class="search-stats" id="search-stats">
                        Mostrando <?php echo $total_docentes; ?> perfiles
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="archive-main">
        <div class="site-container">
            <div id="docentes-grid" class="docentes-grid">
                <?php if ($docentes_query->have_posts()) : ?>
                    <?php while ($docentes_query->have_posts()) : $docentes_query->the_post(); ?>
                        <?php
                        $id = get_the_ID();
                        $nombre = (string) get_post_meta($id, 'nombre', true);
                        $apellido = (string) get_post_meta($id, 'apellido', true);
                        $nombre_completo = get_the_title();
                        $titulo_academico = (string) get_post_meta($id, 'titulo_academico', true);
                        $prefijo_abrev = (string) get_post_meta($id, 'prefijo_abrev', true);

                        $cv_raw = (string) get_post_meta($id, 'cv', true);
                        $resumen = wp_trim_words(wp_strip_all_tags($cv_raw), 22);

                        $iniciales = function_exists('dp_iniciales') ? dp_iniciales($nombre, $apellido, 'D') : 'FL';
                        ?>

                        <article class="docente-card-mini docente-item" data-search="<?php echo esc_attr(strtolower($nombre_completo . ' ' . $titulo_academico)); ?>">
                            <div class="card-inner">
                                <div class="card-avatar">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <?php the_post_thumbnail('medium', ['class' => 'avatar-img']); ?>
                                    <?php else : ?>
                                        <div class="avatar-fallback"><?php echo esc_html($iniciales); ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <div class="card-meta">
                                        <?php if ($prefijo_abrev) : ?>
                                            <span class="prefijo"><?php echo esc_html($prefijo_abrev); ?></span>
                                        <?php endif; ?>
                                        <h2 class="nombre"><?php echo esc_html($nombre_completo); ?></h2>
                                        <?php if ($titulo_academico) : ?>
                                            <p class="titulo"><?php echo esc_html($titulo_academico); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($resumen) : ?>
                                        <p class="resumen"><?php echo esc_html($resumen); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer">
                                    <a href="<?php the_permalink(); ?>" class="btn-profile">Ver Perfil Académico</a>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; wp_reset_postdata(); ?>
                <?php else : ?>
                    <div class="empty-results">
                        <p>No se encontraron docentes publicados en este momento.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>


<?php
get_footer();
