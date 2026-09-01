<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Determinar la vista activa (all | docentes | administrativo)
$active_view = get_query_var('flacso_equipo_view');
if (!$active_view) {
    $request_path = trim(wp_parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
    if (strpos($request_path, 'equipo/docentes') !== false || $request_path === 'docentes') {
        $active_view = 'docentes';
    } elseif (strpos($request_path, 'equipo/administrativo') !== false) {
        $active_view = 'administrativo';
    } else {
        $active_view = 'all';
    }
}

// Configurar textos de la cabecera
if ($active_view === 'docentes') {
    $kicker = __('Comunidad Académica', 'flacso-uruguay');
    $title  = __('Cuerpo Docente', 'flacso-uruguay');
    $desc   = __('Conocé a las y los docentes, investigadores y especialistas que integran la oferta académica de FLACSO Uruguay.', 'flacso-uruguay');
    $search_placeholder = __('Buscar docentes por nombre o especialidad...', 'flacso-uruguay');
} elseif ($active_view === 'administrativo') {
    $kicker = __('Gestión y Coordinación', 'flacso-uruguay');
    $title  = __('Equipo Administrativo y de Gestión', 'flacso-uruguay');
    $desc   = __('Conocé al equipo responsable de la coordinación institucional, secretaría académica y administración de FLACSO Uruguay.', 'flacso-uruguay');
    $search_placeholder = __('Buscar en el equipo administrativo...', 'flacso-uruguay');
} else {
    $kicker = __('Comunidad FLACSO', 'flacso-uruguay');
    $title  = __('Equipo FLACSO Uruguay', 'flacso-uruguay');
    $desc   = __('Conocé a todas las personas que integran el equipo académico, directivo y de gestión de FLACSO Uruguay.', 'flacso-uruguay');
    $search_placeholder = __('Buscar por nombre, cargo o especialidad...', 'flacso-uruguay');
}

// Configurar consulta
$query_args = [
    'post_type'      => 'docente',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => 'title',
    'order'          => 'ASC',
    'no_found_rows'  => true,
];

if ($active_view === 'docentes') {
    $query_args['meta_query'] = [
        'relation' => 'OR',
        [
            'key'     => 'roles',
            'value'   => '"docente"',
            'compare' => 'LIKE',
        ],
        [
            'key'     => 'roles',
            'compare' => 'NOT EXISTS',
        ],
    ];
} elseif ($active_view === 'administrativo') {
    $query_args['meta_query'] = [
        [
            'key'     => 'roles',
            'value'   => '"administrativo"',
            'compare' => 'LIKE',
        ],
    ];
}

$equipo_query = new WP_Query($query_args);
$total_count  = (int) $equipo_query->post_count;
?>

<div class="flacso-docentes-archive">
    <!-- Hero Section con selector de pestañas y buscador -->
    <header class="archive-hero">
        <div class="site-container">
            <div class="hero-content">
                <span class="hero-kicker"><?php echo esc_html($kicker); ?></span>
                <h1 class="hero-title"><?php echo esc_html($title); ?></h1>
                <p class="hero-desc"><?php echo esc_html($desc); ?></p>

                <!-- Pestañas de navegación por rol -->
                <nav class="flacso-equipo-tabs" aria-label="<?php esc_attr_e('Filtrar equipo por rol', 'flacso-uruguay'); ?>">
                    <a href="<?php echo esc_url(home_url('/equipo/')); ?>" class="flacso-equipo-tab<?php echo $active_view === 'all' ? ' is-active' : ''; ?>">
                        <i class="bi bi-people-fill" aria-hidden="true"></i> <?php esc_html_e('Todo el Equipo', 'flacso-uruguay'); ?>
                    </a>
                    <a href="<?php echo esc_url(home_url('/equipo/docentes/')); ?>" class="flacso-equipo-tab<?php echo $active_view === 'docentes' ? ' is-active' : ''; ?>">
                        <i class="bi bi-mortarboard-fill" aria-hidden="true"></i> <?php esc_html_e('Cuerpo Docente', 'flacso-uruguay'); ?>
                    </a>
                    <a href="<?php echo esc_url(home_url('/equipo/administrativo/')); ?>" class="flacso-equipo-tab<?php echo $active_view === 'administrativo' ? ' is-active' : ''; ?>">
                        <i class="bi bi-briefcase-fill" aria-hidden="true"></i> <?php esc_html_e('Equipo Administrativo', 'flacso-uruguay'); ?>
                    </a>
                </nav>

                <div class="search-container">
                    <div class="search-box">
                        <span class="search-icon">🔍</span>
                        <input type="text" id="archive-search" placeholder="<?php echo esc_attr($search_placeholder); ?>" aria-label="<?php esc_attr_e('Buscar en el equipo', 'flacso-uruguay'); ?>">
                    </div>
                    <div class="search-stats" id="search-stats">
                        <?php printf(esc_html__('Mostrando %d perfiles', 'flacso-uruguay'), $total_count); ?>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="archive-main">
        <div class="site-container">
            <div id="docentes-grid" class="docentes-grid">
                <?php if ($equipo_query->have_posts()) : ?>
                    <?php while ($equipo_query->have_posts()) : $equipo_query->the_post(); ?>
                        <?php
                        $id = get_the_ID();
                        $nombre          = (string) get_post_meta($id, 'nombre', true);
                        $apellido        = (string) get_post_meta($id, 'apellido', true);
                        $nombre_completo = get_the_title();
                        $titulo_academico= (string) get_post_meta($id, 'titulo_academico', true);
                        $prefijo_abrev   = (string) get_post_meta($id, 'prefijo_abrev', true);
                        $cargo           = (string) get_post_meta($id, 'cargo', true);
                        $roles           = class_exists('Docente_Meta') ? Docente_Meta::get_roles($id) : ['docente'];

                        $cv_raw  = (string) get_post_meta($id, 'cv', true);
                        $resumen = wp_trim_words(wp_strip_all_tags($cv_raw), 22);

                        $iniciales = function_exists('dp_iniciales') ? dp_iniciales($nombre, $apellido, 'D') : 'FL';
                        ?>

                        <article class="docente-card-mini docente-item" data-search="<?php echo esc_attr(strtolower($nombre_completo . ' ' . $titulo_academico . ' ' . $cargo)); ?>">
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
                                        <?php if (!empty($roles)) : ?>
                                            <div class="flacso-equipo-card__badges">
                                                <?php if (in_array('docente', $roles, true)) : ?>
                                                    <span class="flacso-badge flacso-badge--docente"><?php esc_html_e('Docente', 'flacso-uruguay'); ?></span>
                                                <?php endif; ?>
                                                <?php if (in_array('administrativo', $roles, true)) : ?>
                                                    <span class="flacso-badge flacso-badge--administrativo"><?php esc_html_e('Gestión / Equipo', 'flacso-uruguay'); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($prefijo_abrev) : ?>
                                            <span class="prefijo"><?php echo esc_html($prefijo_abrev); ?></span>
                                        <?php endif; ?>
                                        <h2 class="nombre"><?php echo esc_html($nombre_completo); ?></h2>

                                        <?php if ($cargo) : ?>
                                            <p class="flacso-equipo-card__cargo"><strong><?php echo esc_html($cargo); ?></strong></p>
                                        <?php endif; ?>

                                        <?php if ($titulo_academico) : ?>
                                            <p class="titulo"><?php echo esc_html($titulo_academico); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($resumen) : ?>
                                        <p class="resumen"><?php echo esc_html($resumen); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer">
                                    <a href="<?php the_permalink(); ?>" class="btn-profile"><?php esc_html_e('Ver Perfil', 'flacso-uruguay'); ?></a>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; wp_reset_postdata(); ?>
                <?php else : ?>
                    <div class="empty-results">
                        <p><?php esc_html_e('No se encontraron perfiles publicados en esta sección.', 'flacso-uruguay'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php
get_footer();
