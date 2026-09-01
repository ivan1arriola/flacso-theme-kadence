<?php
/**
 * Template para el archivo de Taxonomía: Tipo de Oferta Académica.
 *
 * Muestra las ofertas académicas pertenecientes al tipo consultado
 * (Maestrías, Especializaciones, Diplomados, Diplomas)
 * utilizando el View Model canónico.
 *
 * @package kadence-child-flacso
 */

if (!defined('ABSPATH')) {
    exit;
}

$term = get_queried_object();
if (!$term || !($term instanceof WP_Term)) {
    return;
}

$taxonomy_name = $term->name;
if (class_exists('FLACSO_Oferta_Academica') && method_exists('FLACSO_Oferta_Academica', 'tipos')) {
    $tipos = FLACSO_Oferta_Academica::tipos();
    if (isset($tipos[$term->slug])) {
        $taxonomy_name = $tipos[$term->slug];
    }
}

$archive_args = [
    'post_type'      => 'oferta-academica',
    'post_status'    => current_user_can('manage_options') ? ['publish', 'private'] : ['publish'],
    'posts_per_page' => -1,
    'no_found_rows'  => true,
    'tax_query'      => [
        [
            'taxonomy' => 'tipo-oferta-academica',
            'field'    => 'term_id',
            'terms'    => (int) $term->term_id,
        ],
    ],
];

$ofertas_query = new WP_Query($archive_args);
$offers_list = [];

if ($ofertas_query->have_posts()) {
    while ($ofertas_query->have_posts()) {
        $ofertas_query->the_post();
        $post_id = get_the_ID();
        $ctx = flacso_theme_get_offer_context($post_id);
        if (!empty($ctx)) {
            $is_open = !empty($ctx['registration']['is_open']);
            $fecha_inicio = (string) ($ctx['instance']['fecha_inicio'] ?? '');
            $carga_horaria = (string) ($ctx['academic']['carga_horaria'] ?? '');

            $offers_list[] = [
                'id'             => $post_id,
                'title'          => $ctx['title'],
                'permalink'      => $ctx['permalink'],
                'thumbnail_url'  => $ctx['thumbnail_url'],
                'is_open'        => $is_open,
                'duracion'       => $carga_horaria !== '' ? $carga_horaria . ' hs.' : '',
                'excerpt'        => wp_trim_words(wp_strip_all_tags($ctx['academic']['presentacion'] ?: get_the_excerpt($post_id)), 18, '...'),
                'start_label'    => !empty($ctx['instance']['fechas_formateadas']) ? $ctx['instance']['fechas_formateadas'] : ($fecha_inicio !== '' ? flacso_theme_format_date_es($fecha_inicio, false) : ''),
                'start_iso'      => $fecha_inicio,
                'sort_timestamp' => $fecha_inicio !== '' ? strtotime($fecha_inicio) : PHP_INT_MAX,
            ];
        }
    }
}
wp_reset_postdata();

usort($offers_list, static function (array $a, array $b): int {
    $a_open = !empty($a['is_open']) ? 0 : 1;
    $b_open = !empty($b['is_open']) ? 0 : 1;
    if ($a_open !== $b_open) {
        return $a_open <=> $b_open;
    }
    $a_sort = (int) ($a['sort_timestamp'] ?? PHP_INT_MAX);
    $b_sort = (int) ($b['sort_timestamp'] ?? PHP_INT_MAX);
    if ($a_sort === $b_sort) {
        return strcasecmp((string) ($a['title'] ?? ''), (string) ($b['title'] ?? ''));
    }
    return $a_sort <=> $b_sort;
});

get_header();
?>

<main class="flacso-taxonomy-oferta">
    <div class="flacso-taxonomy-container">

        <header class="flacso-taxonomy-hero">
            <div class="flacso-taxonomy-hero__body">
                <nav class="flacso-taxonomy-breadcrumb" aria-label="<?php esc_attr_e('Navegación', 'flacso-uruguay'); ?>">
                    <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Inicio', 'flacso-uruguay'); ?></a>
                    <span>/</span>
                    <a href="<?php echo esc_url(home_url('/formacion/')); ?>"><?php esc_html_e('Oferta académica', 'flacso-uruguay'); ?></a>
                    <span>/</span>
                    <span><?php echo esc_html($taxonomy_name); ?></span>
                </nav>

                <h1 class="flacso-taxonomy-hero__title"><?php echo esc_html($taxonomy_name); ?></h1>

                <p class="flacso-taxonomy-hero__description">
                    <?php esc_html_e('Conocé las propuestas disponibles. Revisá la duración, modalidad, fecha de inicio y requisitos de inscripción de cada programa.', 'flacso-uruguay'); ?>
                </p>
            </div>
        </header>

        <div id="flacso-listado-ofertas"></div>
        <?php if (!empty($offers_list)) : ?>
            <section class="flacso-ofertas-listado" aria-labelledby="flacso-ofertas-titulo">
                <div class="flacso-ofertas-toolbar">
                    <h2 id="flacso-ofertas-titulo" class="flacso-ofertas-toolbar__title">
                        <?php esc_html_e('Propuestas disponibles', 'flacso-uruguay'); ?>
                    </h2>
                </div>

                <div class="flacso-ofertas-grid">
                    <?php foreach ($offers_list as $item) : ?>
                        <article class="grid-item-wrap">
                            <a
                                href="<?php echo esc_url((string) $item['permalink']); ?>"
                                class="flacso-premium-card flacso-premium-card--link h-100 w-100"
                                aria-label="<?php echo esc_attr(sprintf(__('Ver información de %s', 'flacso-uruguay'), (string) $item['title'])); ?>"
                                data-flacso-offer-click="1"
                                data-offer-title="<?php echo esc_attr((string) $item['title']); ?>"
                                data-offer-status="<?php echo !empty($item['is_open']) ? 'inscripciones_abiertas' : 'proxima_apertura'; ?>"
                            >
                                <div class="flacso-premium-card__image-wrap">
                                    <?php if (!empty($item['thumbnail_url'])) : ?>
                                        <img src="<?php echo esc_url((string) $item['thumbnail_url']); ?>" class="flacso-premium-card__img" alt="<?php echo esc_attr((string) $item['title']); ?>">
                                    <?php else : ?>
                                        <div class="flacso-premium-card__img-placeholder">
                                            <i class="bi bi-mortarboard" style="font-size: 3rem;"></i>
                                        </div>
                                    <?php endif; ?>

                                    <div class="flacso-premium-card__badges">
                                        <?php if (!empty($item['is_open'])) : ?>
                                            <span class="flacso-badge flacso-badge--open"><?php esc_html_e('Inscripciones abiertas', 'flacso-uruguay'); ?></span>
                                        <?php else : ?>
                                            <span class="flacso-badge flacso-badge--soon"><?php esc_html_e('Próxima apertura', 'flacso-uruguay'); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="flacso-premium-card__body">
                                    <div class="flacso-premium-card__meta">
                                        <?php if (!empty($item['duracion'])) : ?>
                                            <span class="flacso-meta-item">
                                                <i class="bi bi-clock" aria-hidden="true"></i>
                                                <?php echo esc_html((string) $item['duracion']); ?>
                                            </span>
                                        <?php endif; ?>

                                        <?php if (!empty($item['start_label'])) : ?>
                                            <span class="flacso-meta-item flacso-meta-item--start">
                                                <i class="bi bi-calendar3" aria-hidden="true"></i>
                                                <time datetime="<?php echo esc_attr((string) $item['start_iso']); ?>"><?php echo esc_html((string) $item['start_label']); ?></time>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <h3 class="flacso-premium-card__title">
                                        <?php echo esc_html((string) $item['title']); ?>
                                    </h3>

                                    <div class="flacso-premium-card__excerpt">
                                        <?php echo esc_html((string) $item['excerpt']); ?>
                                    </div>

                                    <div class="flacso-premium-card__footer">
                                        <?php
                                        $card_cta = !empty($item['is_open'])
                                            ? __('Ver información e inscribirme', 'flacso-uruguay')
                                            : __('Ver información del programa', 'flacso-uruguay');
                                        ?>
                                        <span class="flacso-premium-card__cta <?php echo !empty($item['is_open']) ? 'flacso-premium-card__cta--open' : ''; ?>">
                                            <?php echo esc_html($card_cta); ?>
                                            <i class="bi bi-arrow-right" aria-hidden="true"></i>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php else : ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-info-circle text-muted mb-3 d-block mx-auto" style="font-size: 3rem;"></i>
                <h3 class="fw-bold" style="color: var(--flacso-blue-dark);"><?php esc_html_e('Próximamente', 'flacso-uruguay'); ?></h3>
                <p class="text-muted"><?php esc_html_e('En este momento no hay propuestas académicas listadas en esta categoría. Te invitamos a estar atento/a a nuestras próximas aperturas.', 'flacso-uruguay'); ?></p>
            </div>
        <?php endif; ?>

        <?php
        $term_desc = term_description();
        if (!empty($term_desc)) :
        ?>
            <div class="flacso-taxonomy-description-footer mt-5">
                <div class="flacso-content-box" style="padding: 2rem; background: #ffffff; border-radius: 12px; box-shadow: 0 4px 15px rgba(5, 25, 56, 0.05); color: #475569; line-height: 1.6;">
                    <?php echo wp_kses_post($term_desc); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
(function() {
    var termTitle = <?php echo wp_json_encode((string) single_term_title('', false)); ?>;

    if (typeof window.flacsoMetaTrack === 'function') {
        try {
            window.flacsoMetaTrack('ViewContent', {
                content_name: 'Listado: ' + termTitle,
                content_category: 'listado_ofertas',
                flacso_stage: 'listado_tipo_oferta'
            });
        } catch (e) {}
    }

    document.addEventListener('click', function(event) {
        var link = event.target.closest ? event.target.closest('a[data-flacso-offer-click]') : null;
        if (!link) return;

        var offerTitle = link.getAttribute('data-offer-title') || '';
        var offerStatus = link.getAttribute('data-offer-status') || '';

        if (typeof window.flacsoMetaTrackCustom === 'function') {
            try {
                window.flacsoMetaTrackCustom('OfertaAcademicaClick', {
                    content_name: offerTitle,
                    content_category: termTitle,
                    flacso_offer_status: offerStatus,
                    flacso_stage: 'click_desde_listado'
                });
            } catch (e) {}
        }
    }, true);
})();
</script>
<?php
get_footer();
