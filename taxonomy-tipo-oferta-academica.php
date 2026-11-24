<?php
/**
 * Template para el archivo de Taxonomía: Tipo de Oferta Académica.
 *
 * Muestra las ofertas académicas pertenecientes al tipo consultado
 * (Doctorados, Maestrías, Especializaciones, Diplomados, Diplomas)
 * utilizando el View Model canónico.
 *
 * @package kadence-child-flacso
 */

if (!defined('ABSPATH')) {
    exit;
}

$catalog_css = 'assets/css/formacion-page.css';
wp_enqueue_style(
    'flacso-formacion-page',
    get_stylesheet_directory_uri() . '/' . $catalog_css,
    ['flacso-taxonomy-oferta'],
    flacso_child_asset_version($catalog_css)
);
$listing_css = 'assets/css/type-listing-cards.css';
wp_enqueue_style(
    'flacso-type-listing-cards',
    get_stylesheet_directory_uri() . '/' . $listing_css,
    ['flacso-formacion-page'],
    flacso_child_asset_version($listing_css)
);

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

            $image_id = (int) get_post_thumbnail_id($post_id);
            $context_thumbnail_url = (string) ($ctx['thumbnail_url'] ?? '');

            if ($image_id <= 0 && $context_thumbnail_url !== '') {
                $image_id = (int) attachment_url_to_postid($context_thumbnail_url);
            }

            $thumbnail_url = '';
            if ($image_id > 0) {
                $cropped = wp_get_attachment_image_url($image_id, 'flacso-card');
                if ($cropped) {
                    $thumbnail_url = (string) $cropped;
                }
            }

            if ($thumbnail_url === '') {
                $thumbnail_url = $context_thumbnail_url;
            }

            $raw_color = '';
            if (!empty($ctx['academic']['color_principal']) && is_string($ctx['academic']['color_principal'])) {
                $raw_color = trim($ctx['academic']['color_principal']);
            } elseif (!empty($ctx['academic']['colores_presentacion']['principal']) && is_string($ctx['academic']['colores_presentacion']['principal'])) {
                $raw_color = trim($ctx['academic']['colores_presentacion']['principal']);
            }
            $brand_color = preg_match('/^#[0-9a-fA-F]{6}$/', $raw_color) ? strtolower($raw_color) : '#0a3470';

            $offers_list[] = [
                'id'             => $post_id,
                'title'          => $ctx['title'],
                'permalink'      => $ctx['permalink'],
                'image_id'       => $image_id,
                'thumbnail_url'  => $thumbnail_url,
                'brand_color'    => $brand_color,
                'is_open'        => $is_open,
                'status'         => (string) ($ctx['instance']['estado'] ?? ''),
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

$offers_open = [];
$offers_closed = [];
foreach ($offers_list as $item) {
    if (!empty($item['is_open'])) {
        $offers_open[] = $item;
    } else {
        $offers_closed[] = $item;
    }
}

$sort_offers = static function (array $a, array $b): int {
    $a_sort = (int) ($a['sort_timestamp'] ?? PHP_INT_MAX);
    $b_sort = (int) ($b['sort_timestamp'] ?? PHP_INT_MAX);
    if ($a_sort === $b_sort) {
        return strcasecmp((string) ($a['title'] ?? ''), (string) ($b['title'] ?? ''));
    }
    return $a_sort <=> $b_sort;
};

usort($offers_open, $sort_offers);
usort($offers_closed, $sort_offers);

$render_offer_card = static function (array $item) use ($taxonomy_name): void {
    ?>
    <article class="grid-item-wrap">
        <?php
        get_template_part('template-parts/components/academic-card', null, [
            'title'       => (string) $item['title'],
            'url'         => (string) $item['permalink'],
            'badge'       => (string) $taxonomy_name,
            'description' => (string) $item['excerpt'],
            'image_id'    => (int) ($item['image_id'] ?? 0),
            'image_url'   => (string) ($item['thumbnail_url'] ?? ''),
            'image_sizes' => '(max-width: 767px) 42vw, (max-width: 1200px) 33vw, 340px',
            'accent'      => (string) ($item['brand_color'] ?? '#0a3470'),
            'is_open'     => !empty($item['is_open']),
            'status'      => (string) ($item['status'] ?? ''),
            'icon_style'  => 'font-size: 3rem;',
            'meta'        => array_values(array_filter([
                !empty($item['duracion']) ? ['icon' => 'bi-clock', 'text' => (string) $item['duracion']] : null,
                !empty($item['start_label']) ? ['icon' => 'bi-calendar3', 'text' => (string) $item['start_label'], 'datetime' => (string) $item['start_iso']] : null,
            ])),
            'link_attrs'  => [
                'aria-label'              => sprintf(__('Ver información de %s', 'flacso-uruguay'), (string) $item['title']),
                'data-flacso-offer-click' => '1',
                'data-offer-title'        => (string) $item['title'],
                'data-offer-status'       => !empty($item['is_open']) ? 'inscripciones_abiertas' : 'proxima_apertura',
            ],
        ]);
        ?>
    </article>
    <?php
};

get_header();
?>

<<?php echo flacso_child_main_tag(); ?> class="flacso-taxonomy-oferta" id="main">
    <?php
    $term_lede = trim(wp_strip_all_tags((string) term_description()));
    $term_image_id = (int) get_term_meta((int) get_queried_object_id(), 'thumbnail_id', true);
    $lede_is_short = '' !== $term_lede && mb_strlen($term_lede) <= 260;
    get_template_part(
        'template-parts/components/page-header',
        null,
        [
            'breadcrumb' => [
                ['label' => __('Inicio', 'flacso-uruguay'), 'url' => home_url('/')],
                ['label' => __('Oferta académica', 'flacso-uruguay'), 'url' => home_url('/formacion/')],
                ['label' => $taxonomy_name],
            ],
            'kicker'     => __('Oferta académica', 'flacso-uruguay'),
            'title'      => $taxonomy_name,
            'lede'       => $lede_is_short ? $term_lede : __('Conocé las propuestas disponibles. Revisá la duración, modalidad, fecha de inicio y requisitos de inscripción de cada programa.', 'flacso-uruguay'),
            'image_id'   => $term_image_id,
            'image_size' => 'flacso-seminario-4x5',
        ]
    );
    ?>
    <div class="flacso-taxonomy-container">


        <div id="flacso-listado-ofertas"></div>
        <?php if (!empty($offers_open) || !empty($offers_closed)) : ?>

            <?php if (!empty($offers_open)) : ?>
                <section class="flacso-ofertas-listado flacso-ofertas-listado--open" aria-labelledby="flacso-ofertas-abiertas-titulo">
                    <div class="flacso-ofertas-toolbar flacso-ofertas-toolbar--open">
                        <div class="flacso-ofertas-toolbar__header">
                            <h2 id="flacso-ofertas-abiertas-titulo" class="flacso-ofertas-toolbar__title">
                                <i class="bi bi-check-circle-fill text-success" aria-hidden="true"></i>
                                <?php esc_html_e('Inscripciones abiertas', 'flacso-uruguay'); ?>
                            </h2>
                            <span class="flacso-ofertas-toolbar__count flacso-ofertas-toolbar__count--open">
                                <?php echo count($offers_open); ?> <?php echo count($offers_open) === 1 ? esc_html__('propuesta disponible', 'flacso-uruguay') : esc_html__('propuestas disponibles', 'flacso-uruguay'); ?>
                            </span>
                        </div>
                    </div>

                    <div class="flacso-ofertas-grid flacso-formacion-grid">
                        <?php foreach ($offers_open as $item) : ?>
                            <?php $render_offer_card($item); ?>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php if (!empty($offers_closed)) : ?>
                <section class="flacso-ofertas-listado flacso-ofertas-listado--soon" aria-labelledby="flacso-ofertas-proximas-titulo">
                    <div class="flacso-ofertas-toolbar flacso-ofertas-toolbar--soon">
                        <div class="flacso-ofertas-toolbar__header">
                            <h2 id="flacso-ofertas-proximas-titulo" class="flacso-ofertas-toolbar__title">
                                <i class="bi bi-clock-history text-secondary" aria-hidden="true"></i>
                                <?php esc_html_e('Sin inscripciones abiertas', 'flacso-uruguay'); ?>
                            </h2>
                            <span class="flacso-ofertas-toolbar__count flacso-ofertas-toolbar__count--soon">
                                <?php echo count($offers_closed); ?> <?php echo count($offers_closed) === 1 ? esc_html__('propuesta', 'flacso-uruguay') : esc_html__('propuestas', 'flacso-uruguay'); ?>
                            </span>
                        </div>
                        <p class="flacso-ofertas-toolbar__note">
                            <?php esc_html_e('Programas sin inscripciones activas en este momento. Podés consultar el plan de estudios y registrar tu consulta.', 'flacso-uruguay'); ?>
                        </p>
                    </div>

                    <div class="flacso-ofertas-grid flacso-formacion-grid">
                        <?php foreach ($offers_closed as $item) : ?>
                            <?php $render_offer_card($item); ?>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

        <?php else : ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-info-circle text-muted mb-3 d-block mx-auto" style="font-size: 3rem;"></i>
                <h3 class="fw-bold" style="color: var(--flacso-blue-dark);"><?php esc_html_e('Próximamente', 'flacso-uruguay'); ?></h3>
                <p class="text-muted"><?php esc_html_e('En este momento no hay propuestas académicas listadas en esta categoría. Te invitamos a estar atento/a a nuestras próximas aperturas.', 'flacso-uruguay'); ?></p>
            </div>
        <?php endif; ?>

        <?php
        $term_desc = term_description();
        if (!empty($term_desc) && !$lede_is_short) :
        ?>
            <div class="flacso-taxonomy-description-footer mt-5">
                <div class="flacso-content-box" style="padding: 2rem; background: #ffffff; border-radius: 12px; box-shadow: 0 4px 15px rgba(5, 25, 56, 0.05); color: #475569; line-height: 1.6;">
                    <?php echo wp_kses_post($term_desc); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</<?php echo flacso_child_main_tag(); ?>>

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
