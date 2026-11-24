<?php
/**
 * Vista principal del catálogo de Oferta Académica (/formacion/).
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

<<?php echo flacso_child_main_tag(); ?> id="main" class="site-main flacso-formacion-page">

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
                            <?php $nav_open = count(array_filter($ofertas_por_tipo[$slug], static fn($o) => !empty($o['registration']['is_open']))); ?>
                            <span class="flacso-formacion-nav__count"><?php echo esc_html($nav_open > 0 ? sprintf(__('%d con inscripción abierta', 'flacso-uruguay'), $nav_open) : sprintf(_n('%d propuesta', '%d propuestas', count($ofertas_por_tipo[$slug]), 'flacso-uruguay'), count($ofertas_por_tipo[$slug]))); ?></span>
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
    $flacso_formacion_card_sizes = '(max-width: 600px) 40vw, (max-width: 900px) 46vw, (max-width: 1100px) 31vw, 400px';
    $flacso_formacion_card_meta = static function (string $fechas, string $carga_horaria): array {
        $meta = [];
        if ($fechas !== '') {
            $meta[] = ['icon' => 'bi-calendar3', 'text' => $fechas];
        }
        if ($carga_horaria !== '' && is_numeric($carga_horaria) && (float) $carga_horaria > 0) {
            $meta[] = ['icon' => 'bi-clock', 'text' => $carga_horaria . ' hs.'];
        }
        return $meta;
    };

    $render_formacion_card = static function (array $item) use ($flacso_formacion_offer_accent, $flacso_formacion_card_sizes, $flacso_formacion_card_meta): void {
        $accent = $flacso_formacion_offer_accent($item);
        $is_open = !empty($item['registration']['is_open']);
        $fecha_inicio = (string) ($item['instance']['fecha_inicio'] ?? '');
        $carga_horaria = (string) ($item['academic']['carga_horaria'] ?? '');
        $fechas_card = !empty($item['instance']['fechas_formateadas']) ? $item['instance']['fechas_formateadas'] : ($fecha_inicio !== '' ? flacso_theme_format_date_es($fecha_inicio, false) : '');
        get_template_part('template-parts/components/academic-card', null, [
            'title'       => (string) $item['title'],
            'url'         => (string) $item['permalink'],
            'badge'       => (string) $item['type_label'],
            'description' => !empty($item['academic']['presentacion']) ? wp_trim_words(wp_strip_all_tags($item['academic']['presentacion']), 22) : '',
            'image_id'    => (int) ($item['thumbnail_id'] ?? 0),
            'image_url'   => (string) ($item['thumbnail_url'] ?? ''),
            'image_sizes' => $flacso_formacion_card_sizes,
            'accent'      => $accent,
            'is_open'     => $is_open,
            'status'      => (string) ($item['instance']['estado'] ?? ''),
            'meta'        => $flacso_formacion_card_meta((string) $fechas_card, $carga_horaria),
        ]);
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
                                    <?php esc_html_e('Sin inscripciones abiertas', 'flacso-uruguay'); ?>
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
                        <?php
                        get_template_part('template-parts/components/academic-card', null, [
                            'title'       => (string) $sem['title'],
                            'url'         => (string) $sem['permalink'],
                            'badge'       => __('Seminario', 'flacso-uruguay'),
                            'description' => !empty($sem['academic']['presentacion']) ? wp_trim_words(wp_strip_all_tags($sem['academic']['presentacion']), 20) : '',
                            'image_id'    => (int) ($sem['thumbnail_id'] ?? 0),
                            'image_url'   => (string) ($sem['thumbnail_url'] ?? ''),
                            'image_sizes' => $flacso_formacion_card_sizes,
                            'is_open'     => $is_open,
            'status'      => (string) ($sem['instance']['estado'] ?? ''),
                            'meta'        => $flacso_formacion_card_meta($fecha_inicio !== '' ? flacso_theme_format_date_es($fecha_inicio, false) : '', $carga_horaria),
                        ]);
                        ?>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

    </div>

</<?php echo flacso_child_main_tag(); ?>>

<?php
get_footer();
