<?php
/**
 * Template para la página de Oferta Académica (/formacion/).
 *
 * Muestra el catálogo público de propuestas académicas de FLACSO Uruguay
 * (Doctorados, Maestrías, Especializaciones, Diplomados, Diplomas y Seminarios)
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
    'doctorado'       => [
        'label'       => __('Doctorados', 'flacso-uruguay'),
        'url'         => home_url('/formacion/doctorados/'),
        'description' => __('Máximo nivel de formación de posgrado orientado a la investigación original.', 'flacso-uruguay'),
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

// Extraer exclusivamente el color principal validado de cada Oferta Académica.
// Las tonalidades vienen del modelo canónico, no de colores fijos por categoría.
$flacso_formacion_offer_accent = static function (array $item): string {
    $raw = $item['color_principal']
        ?? ($item['academic']['color_principal']
        ?? ($item['academic']['colores_presentacion']['principal'] ?? ''));
    if (!is_string($raw)) {
        return '';
    }
    $color = trim($raw);
    return preg_match('/^#[0-9a-fA-F]{6}$/', $color) ? strtolower($color) : '';
};

// Una muestra representativa de los colores de las ofertas publicadas.
$flacso_formacion_palette = [];
foreach ($ofertas_por_tipo as $offers_by_type) {
    foreach ($offers_by_type as $offer) {
        $color = $flacso_formacion_offer_accent($offer);
        if ($color !== '' && !in_array($color, $flacso_formacion_palette, true)) {
            $flacso_formacion_palette[] = $color;
        }
    }
}

// Seminarios destacados
$seminarios = function_exists('flacso_theme_get_seminars_catalog')
    ? flacso_theme_get_seminars_catalog()
    : [];
?>

<main id="main" class="site-main flacso-formacion-page">

    <section class="flacso-formacion-hero">
        <div class="flacso-formacion-hero__inner">
            <div class="flacso-formacion-hero__intro">
                <div class="flacso-formacion-hero__copy">
                    <span class="flacso-formacion-hero__kicker">
                <i class="bi bi-mortarboard-fill" aria-hidden="true"></i>
                <?php esc_html_e('FLACSO Uruguay', 'flacso-uruguay'); ?>
            </span>
            <h1 class="flacso-formacion-hero__title"><?php esc_html_e('Oferta Académica', 'flacso-uruguay'); ?></h1>
            <p class="flacso-formacion-hero__lead">
                <?php esc_html_e('Propuestas de posgrado y formación continua en ciencias sociales, educación, género y políticas públicas.', 'flacso-uruguay'); ?>
            </p>
                </div>

                <?php if (!empty($flacso_formacion_palette)) : ?>
                    <div class="flacso-formacion-hero__palette" aria-hidden="true">
                        <?php foreach (array_slice($flacso_formacion_palette, 0, 8) as $color) : ?>
                            <span style="--offer-accent: <?php echo esc_attr($color); ?>;"></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <nav class="flacso-formacion-nav" aria-label="<?php esc_attr_e('Tipos de formación', 'flacso-uruguay'); ?>">
                <?php foreach ($tipos_oferta as $slug => $info) : ?>
                    <?php if (!empty($ofertas_por_tipo[$slug])) : ?>
                        <a href="#<?php echo esc_attr($slug); ?>" class="flacso-formacion-nav__link">
                            <span class="flacso-formacion-nav__name"><?php echo esc_html($info['label']); ?></span>
                            <span class="flacso-formacion-nav__count"><?php echo esc_html(sprintf(_n('%d propuesta', '%d propuestas', count($ofertas_por_tipo[$slug]), 'flacso-uruguay'), count($ofertas_por_tipo[$slug]))); ?></span>
                            <span class="flacso-formacion-nav__colors" aria-hidden="true">
                                <?php
                                $nav_colors = [];
                                foreach ($ofertas_por_tipo[$slug] as $offer) {
                                    $color = $flacso_formacion_offer_accent($offer);
                                    if ($color !== '' && !in_array($color, $nav_colors, true)) {
                                        $nav_colors[] = $color;
                                    }
                                }
                                ?>
                                <?php foreach (array_slice($nav_colors, 0, 4) as $color) : ?>
                                    <i style="--offer-accent: <?php echo esc_attr($color); ?>;"></i>
                                <?php endforeach; ?>
                            </span>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
                <a href="<?php echo esc_url(home_url('/formacion/seminarios/')); ?>" class="flacso-formacion-nav__link flacso-formacion-nav__link--seminars">
                    <span class="flacso-formacion-nav__name"><?php esc_html_e('Seminarios', 'flacso-uruguay'); ?></span>
                    <span class="flacso-formacion-nav__count"><?php esc_html_e('Formación continua', 'flacso-uruguay'); ?></span>
                </a>
            </nav>
        </div>
    </section>

    <?php
    $render_formacion_card = static function (array $item) use ($flacso_formacion_offer_accent): void {
        $accent = $flacso_formacion_offer_accent($item);
        $is_open = !empty($item['registration']['is_open']);
        $fecha_inicio = (string) ($item['instance']['fecha_inicio'] ?? '');
        $carga_horaria = (string) ($item['academic']['carga_horaria'] ?? '');
        $fechas_card = !empty($item['instance']['fechas_formateadas']) ? $item['instance']['fechas_formateadas'] : ($fecha_inicio !== '' ? flacso_theme_format_date_es($fecha_inicio, false) : '');
        ?>
        <a href="<?php echo esc_url($item['permalink']); ?>"
           class="flacso-formacion-card<?php echo $accent !== '' ? ' flacso-formacion-card--accented' : ''; ?>"
           <?php if ($accent !== '') : ?>style="--offer-accent: <?php echo esc_attr($accent); ?>;"<?php endif; ?>>
            <div class="flacso-formacion-card__media">
                <?php if (!empty($item['thumbnail_url'])) : ?>
                    <img src="<?php echo esc_url($item['thumbnail_url']); ?>" alt="<?php echo esc_attr($item['title']); ?>" loading="lazy" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                    <div class="flacso-formacion-card__placeholder" style="display:none;">
                        <i class="bi bi-mortarboard" aria-hidden="true"></i>
                    </div>
                <?php else : ?>
                    <div class="flacso-formacion-card__placeholder">
                        <i class="bi bi-mortarboard" aria-hidden="true"></i>
                    </div>
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
                    <?php if ($fechas_card !== '') : ?>
                        <span class="flacso-formacion-card__meta-item">
                            <i class="bi bi-calendar3" aria-hidden="true"></i>
                            <?php echo esc_html($fechas_card); ?>
                        </span>
                    <?php endif; ?>
                    <?php if ($carga_horaria !== '' && is_numeric($carga_horaria) && (float) $carga_horaria > 0) : ?>
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
        <?php
    };
    ?>

    <div class="flacso-formacion-catalog">

        <?php foreach ($tipos_oferta as $slug => $info) : ?>
            <?php if (!empty($ofertas_por_tipo[$slug])) :
                $group_open = [];
                $group_closed = [];
                foreach ($ofertas_por_tipo[$slug] as $item) {
                    if (!empty($item['registration']['is_open'])) {
                        $group_open[] = $item;
                    } else {
                        $group_closed[] = $item;
                    }
                }
                $sort_formacion = static function (array $a, array $b): int {
                    $a_date = (string) ($a['instance']['fecha_inicio'] ?? '');
                    $b_date = (string) ($b['instance']['fecha_inicio'] ?? '');
                    $a_ts = $a_date !== '' ? strtotime($a_date) : PHP_INT_MAX;
                    $b_ts = $b_date !== '' ? strtotime($b_date) : PHP_INT_MAX;
                    if ($a_ts === $b_ts) {
                        return strcasecmp((string) ($a['title'] ?? ''), (string) ($b['title'] ?? ''));
                    }
                    return $a_ts <=> $b_ts;
                };
                usort($group_open, $sort_formacion);
                usort($group_closed, $sort_formacion);

                $group_palette = [];
                foreach ($ofertas_por_tipo[$slug] as $offer) {
                    $color = $flacso_formacion_offer_accent($offer);
                    if ($color !== '' && !in_array($color, $group_palette, true)) {
                        $group_palette[] = $color;
                    }
                }
            ?>
                <section id="<?php echo esc_attr($slug); ?>" class="flacso-formacion-group" aria-labelledby="heading-<?php echo esc_attr($slug); ?>">
                    <header class="flacso-formacion-group__header">
                        <div class="flacso-formacion-group__heading">
                            <h2 id="heading-<?php echo esc_attr($slug); ?>" class="flacso-formacion-group__title"><?php echo esc_html($info['label']); ?></h2>
                            <p class="flacso-formacion-group__description"><?php echo esc_html($info['description']); ?></p>
                            <?php if ($group_palette) : ?>
                                <div class="flacso-formacion-group__palette" aria-hidden="true">
                                    <?php foreach (array_slice($group_palette, 0, 6) as $color) : ?>
                                        <span style="--offer-accent: <?php echo esc_attr($color); ?>;"></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <a href="<?php echo esc_url($info['url']); ?>" class="flacso-formacion-group__more">
                            <?php
                            $label_lower = mb_strtolower((string) $info['label']);
                            $link_label = in_array($slug, ['diplomado', 'doctorado'], true)
                                ? sprintf(__('Ver todos los %s', 'flacso-uruguay'), $label_lower)
                                : sprintf(__('Ver todas las %s', 'flacso-uruguay'), $label_lower);
                            echo esc_html($link_label);
                            ?> <span aria-hidden="true">↗</span>
                        </a>
                    </header>

                    <?php if (!empty($group_open) && !empty($group_closed)) : ?>
                        <div class="flacso-formacion-subgroup">
                            <div class="flacso-formacion-subgroup__header">
                                <h3 class="flacso-formacion-subgroup__title flacso-formacion-subgroup__title--open">
                                    <i class="bi bi-check-circle-fill text-success" aria-hidden="true"></i>
                                    <?php esc_html_e('Inscripciones abiertas', 'flacso-uruguay'); ?>
                                </h3>
                                <span class="flacso-formacion-subgroup__count flacso-formacion-subgroup__count--open">
                                    <?php echo count($group_open); ?>
                                </span>
                            </div>
                            <div class="flacso-formacion-grid">
                                <?php foreach ($group_open as $item) : ?>
                                    <?php $render_formacion_card($item); ?>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="flacso-formacion-subgroup flacso-formacion-subgroup--closed">
                            <div class="flacso-formacion-subgroup__header">
                                <h3 class="flacso-formacion-subgroup__title flacso-formacion-subgroup__title--soon">
                                    <i class="bi bi-clock-history text-secondary" aria-hidden="true"></i>
                                    <?php esc_html_e('Próxima apertura', 'flacso-uruguay'); ?>
                                </h3>
                                <span class="flacso-formacion-subgroup__count flacso-formacion-subgroup__count--soon">
                                    <?php echo count($group_closed); ?>
                                </span>
                            </div>
                            <div class="flacso-formacion-grid">
                                <?php foreach ($group_closed as $item) : ?>
                                    <?php $render_formacion_card($item); ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else : ?>
                        <div class="flacso-formacion-grid">
                            <?php foreach (array_merge($group_open, $group_closed) as $item) : ?>
                                <?php $render_formacion_card($item); ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
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
                                    <img src="<?php echo esc_url($sem['thumbnail_url']); ?>" alt="<?php echo esc_attr($sem['title']); ?>" loading="lazy" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                                    <div class="flacso-formacion-card__placeholder" style="display:none;">
                                        <i class="bi bi-mortarboard" aria-hidden="true"></i>
                                    </div>
                                <?php else : ?>
                                    <div class="flacso-formacion-card__placeholder">
                                        <i class="bi bi-mortarboard" aria-hidden="true"></i>
                                    </div>
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
                                    <?php if ($carga_horaria !== '' && is_numeric($carga_horaria) && (float) $carga_horaria > 0) : ?>
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
