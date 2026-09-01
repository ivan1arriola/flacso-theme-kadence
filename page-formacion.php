<?php
/**
 * Template para la página de Oferta Académica (/formacion/).
 *
 * Muestra el catálogo público de propuestas académicas de FLACSO Uruguay
 * (Maestrías, Especializaciones, Diplomados, Diplomas y Seminarios)
 * consumiendo directamente los CPTs canónicos.
 *
 * @package kadence-child-flacso
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$tipos_oferta = [
    'maestria'        => [
        'label'       => __('Maestrías', 'flacso-uruguay'),
        'url'         => home_url('/formacion/maestrias/'),
        'description' => __('Formación académica avanzada que profundiza en marcos teórico-metodológicos y promueve la investigación.', 'flacso-uruguay'),
    ],
    'especializacion' => [
        'label'       => __('Especializaciones', 'flacso-uruguay'),
        'url'         => home_url('/formacion/especializaciones/'),
        'description' => __('Profundización en el dominio de un tema o área profesional determinada.', 'flacso-uruguay'),
    ],
    'diplomado'       => [
        'label'       => __('Diplomados', 'flacso-uruguay'),
        'url'         => home_url('/formacion/diplomados/'),
        'description' => __('Trayectos formativos orientados a la actualización teórico-práctica.', 'flacso-uruguay'),
    ],
    'diploma'         => [
        'label'       => __('Diplomas', 'flacso-uruguay'),
        'url'         => home_url('/formacion/diplomas/'),
        'description' => __('Formación específica sobre temáticas contemporáneas de relevancia social.', 'flacso-uruguay'),
    ],
];

// Obtener todas las ofertas publicadas
$ofertas_posts = get_posts([
    'post_type'      => 'oferta-academica',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => 'title',
    'order'          => 'ASC',
]);

$ofertas_por_tipo = [];
foreach ($tipos_oferta as $slug => $info) {
    $ofertas_por_tipo[$slug] = [];
}

foreach ($ofertas_posts as $post_obj) {
    $context = flacso_theme_get_offer_context((int) $post_obj->ID);
    if (empty($context)) {
        continue;
    }
    $tipo = $context['type'] ?: 'maestria';
    if (!isset($ofertas_por_tipo[$tipo])) {
        $ofertas_por_tipo[$tipo] = [];
    }
    $ofertas_por_tipo[$tipo][] = $context;
}

// Seminarios destacados
$seminarios = function_exists('flacso_theme_get_seminars_catalog')
    ? flacso_theme_get_seminars_catalog()
    : [];
?>

<main id="main" class="site-main flacso-formacion-page">

    <section class="flacso-formacion-hero">
        <div class="flacso-formacion-hero__inner">
            <span class="flacso-formacion-hero__kicker">
                <i class="bi bi-mortarboard-fill" aria-hidden="true"></i>
                <?php esc_html_e('FLACSO Uruguay', 'flacso-uruguay'); ?>
            </span>
            <h1 class="flacso-formacion-hero__title"><?php esc_html_e('Oferta Académica', 'flacso-uruguay'); ?></h1>
            <p class="flacso-formacion-hero__lead">
                <?php esc_html_e('Propuestas de posgrado y formación continua en ciencias sociales, educación, género y políticas públicas.', 'flacso-uruguay'); ?>
            </p>

            <nav class="flacso-formacion-nav" aria-label="<?php esc_attr_e('Tipos de formación', 'flacso-uruguay'); ?>">
                <?php foreach ($tipos_oferta as $slug => $info) : ?>
                    <?php if (!empty($ofertas_por_tipo[$slug])) : ?>
                        <a href="#<?php echo esc_attr($slug); ?>" class="flacso-formacion-nav__link"><?php echo esc_html($info['label']); ?></a>
                    <?php endif; ?>
                <?php endforeach; ?>
                <a href="<?php echo esc_url(home_url('/formacion/seminarios/')); ?>" class="flacso-formacion-nav__link"><?php esc_html_e('Seminarios', 'flacso-uruguay'); ?></a>
            </nav>
        </div>
    </section>

    <div class="flacso-formacion-catalog">

        <?php foreach ($tipos_oferta as $slug => $info) : ?>
            <?php if (!empty($ofertas_por_tipo[$slug])) : ?>
                <section id="<?php echo esc_attr($slug); ?>" class="flacso-formacion-group" aria-labelledby="heading-<?php echo esc_attr($slug); ?>">
                    <header class="flacso-formacion-group__header">
                        <h2 id="heading-<?php echo esc_attr($slug); ?>" class="flacso-formacion-group__title"><?php echo esc_html($info['label']); ?></h2>
                        <a href="<?php echo esc_url($info['url']); ?>" class="flacso-formacion-group__more">
                            <?php printf(esc_html__('Ver todas las %s →', 'flacso-uruguay'), esc_html(mb_strtolower($info['label']))); ?>
                        </a>
                    </header>

                    <div class="flacso-formacion-grid">
                        <?php foreach ($ofertas_por_tipo[$slug] as $item) :
                            $is_open = !empty($item['registration']['is_open']);
                            $fecha_inicio = (string) ($item['instance']['fecha_inicio'] ?? '');
                            $carga_horaria = (string) ($item['academic']['carga_horaria'] ?? '');
                        ?>
                            <a href="<?php echo esc_url($item['permalink']); ?>" class="flacso-formacion-card">
                                <div class="flacso-formacion-card__media">
                                    <?php if (!empty($item['thumbnail_url'])) : ?>
                                        <img src="<?php echo esc_url($item['thumbnail_url']); ?>" alt="<?php echo esc_attr($item['title']); ?>" loading="lazy">
                                    <?php endif; ?>
                                    <span class="flacso-formacion-card__badge"><?php echo esc_html($item['type_label']); ?></span>
                                </div>
                                <div class="flacso-formacion-card__body">
                                    <h3 class="flacso-formacion-card__title"><?php echo esc_html($item['title']); ?></h3>
                                    <?php if (!empty($item['academic']['presentacion'])) : ?>
                                        <p class="flacso-formacion-card__excerpt">
                                            <?php echo esc_html(wp_trim_words(wp_strip_all_tags($item['academic']['presentacion']), 22)); ?>
                                        </p>
                                    <?php endif; ?>

                                    <div class="flacso-formacion-card__meta">
                                        <?php
                                        $fechas_card = !empty($item['instance']['fechas_formateadas']) ? $item['instance']['fechas_formateadas'] : ($fecha_inicio !== '' ? flacso_theme_format_date_es($fecha_inicio, false) : '');
                                        if ($fechas_card !== '') : ?>
                                            <span class="flacso-formacion-card__meta-item">
                                                <i class="bi bi-calendar3" aria-hidden="true"></i>
                                                <?php echo esc_html($fechas_card); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($carga_horaria !== '') : ?>
                                            <span class="flacso-formacion-card__meta-item">
                                                <i class="bi bi-clock" aria-hidden="true"></i>
                                                <?php echo esc_html($carga_horaria); ?> hs.
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="flacso-formacion-card__footer">
                                        <span class="flacso-formacion-card__status<?php echo $is_open ? ' is-open' : ''; ?>">
                                            <?php echo esc_html($is_open ? __('Inscripciones abiertas', 'flacso-uruguay') : __('Próxima apertura', 'flacso-uruguay')); ?>
                                        </span>
                                        <span class="flacso-formacion-card__action">
                                            <?php esc_html_e('Conocer más', 'flacso-uruguay'); ?> →
                                        </span>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if (!empty($seminarios)) : ?>
            <section id="seminarios" class="flacso-formacion-group" aria-labelledby="heading-seminarios">
                <header class="flacso-formacion-group__header">
                    <h2 id="heading-seminarios" class="flacso-formacion-group__title"><?php esc_html_e('Seminarios', 'flacso-uruguay'); ?></h2>
                    <a href="<?php echo esc_url(home_url('/formacion/seminarios/')); ?>" class="flacso-formacion-group__more">
                        <?php esc_html_e('Ver todos los seminarios →', 'flacso-uruguay'); ?>
                    </a>
                </header>

                <div class="flacso-formacion-grid">
                    <?php foreach (array_slice($seminarios, 0, 6) as $sem) :
                        $is_open = !empty($sem['registration']['is_open']);
                        $fecha_inicio = (string) ($sem['instance']['fecha_inicio'] ?? '');
                        $carga_horaria = (string) ($sem['academic']['carga_horaria'] ?? '');
                    ?>
                        <a href="<?php echo esc_url($sem['permalink']); ?>" class="flacso-formacion-card">
                            <div class="flacso-formacion-card__media">
                                <?php if (!empty($sem['thumbnail_url'])) : ?>
                                    <img src="<?php echo esc_url($sem['thumbnail_url']); ?>" alt="<?php echo esc_attr($sem['title']); ?>" loading="lazy">
                                <?php endif; ?>
                                <span class="flacso-formacion-card__badge"><?php esc_html_e('Seminario', 'flacso-uruguay'); ?></span>
                            </div>
                            <div class="flacso-formacion-card__body">
                                <h3 class="flacso-formacion-card__title"><?php echo esc_html($sem['title']); ?></h3>
                                <?php if (!empty($sem['academic']['presentacion'])) : ?>
                                    <p class="flacso-formacion-card__excerpt">
                                        <?php echo esc_html(wp_trim_words(wp_strip_all_tags($sem['academic']['presentacion']), 20)); ?>
                                    </p>
                                <?php endif; ?>

                                <div class="flacso-formacion-card__meta">
                                    <?php if ($fecha_inicio !== '') : ?>
                                        <span class="flacso-formacion-card__meta-item">
                                            <i class="bi bi-calendar3" aria-hidden="true"></i>
                                            <?php echo esc_html(flacso_theme_format_date_es($fecha_inicio, false)); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($carga_horaria !== '') : ?>
                                        <span class="flacso-formacion-card__meta-item">
                                            <i class="bi bi-clock" aria-hidden="true"></i>
                                            <?php echo esc_html($carga_horaria); ?> hs.
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="flacso-formacion-card__footer">
                                    <span class="flacso-formacion-card__status<?php echo $is_open ? ' is-open' : ''; ?>">
                                        <?php echo esc_html($is_open ? __('Inscripciones abiertas', 'flacso-uruguay') : __('Próxima apertura', 'flacso-uruguay')); ?>
                                    </span>
                                    <span class="flacso-formacion-card__action">
                                        <?php esc_html_e('Conocer más', 'flacso-uruguay'); ?> →
                                    </span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

    </div>

</main>

<?php
get_footer();
