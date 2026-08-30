<?php
/**
 * Template para el archivo de Taxonomía: Tipo de Oferta Académica.
 * Replicando fielmente el diseño de flacso.edu.uy
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('flacso_oferta_archive_uppercase_label')) {
    function flacso_oferta_archive_uppercase_label(string $label): string {
        $label = trim($label);
        if ($label === '') {
            return '';
        }

        return function_exists('mb_strtoupper')
            ? mb_strtoupper($label, 'UTF-8')
            : strtoupper($label);
    }
}

if (!function_exists('flacso_oferta_archive_get_start_data')) {
    function flacso_oferta_archive_get_start_data(int $post_id): array {
        $context = function_exists('flacso_theme_get_offer_context')
            ? flacso_theme_get_offer_context($post_id)
            : [];
        $instance = is_array($context) ? ($context['instance'] ?? []) : [];

        $raw = $instance['fecha_inicio'] ?? get_post_meta($post_id, 'proximo_inicio', true);
        if (is_array($raw)) {
            $raw = reset($raw);
        }

        $raw = trim((string) $raw);
        $precision = strtolower(trim((string) (
            $instance['precision_fecha_inicio']
            ?? get_post_meta($post_id, 'proximo_inicio_precision', true)
        )));
        $label = $raw;

        if ($raw !== '' && class_exists('Oferta_Renderer')) {
            $formatted = Oferta_Renderer::format_proximo_inicio_text($raw, $precision);
            if ($formatted !== '') {
                $label = $formatted;
            }
        }

        $result = [
            'raw' => $raw,
            'precision' => $precision,
            'label' => $label,
            'iso' => '',
            'sort_timestamp' => PHP_INT_MAX,
            'group_sort' => PHP_INT_MAX,
            'group_key' => 'sin-fecha',
            'group_label' => __('Sin fecha de inicio confirmada', 'flacso-oferta-academica'),
        ];

        if ($raw === '') {
            return $result;
        }

        $timezone = wp_timezone();

        if (
            preg_match('/^(\d{4})[-\/](\d{1,2})[-\/](\d{1,2})$/', $raw, $matches)
            || preg_match('/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/', $raw, $matches)
        ) {
            if (strlen($matches[1]) === 4) {
                $year = (int) $matches[1];
                $month = (int) $matches[2];
                $day = (int) $matches[3];
            } else {
                $day = (int) $matches[1];
                $month = (int) $matches[2];
                $year = (int) $matches[3];
            }

            if (checkdate($month, $day, $year)) {
                $date = DateTimeImmutable::createFromFormat('Y-m-d|', sprintf('%04d-%02d-%02d', $year, $month, $day), $timezone);
                if ($date instanceof DateTimeImmutable) {
                    $month_anchor = $date->modify('first day of this month');
                    $group_sort = $month_anchor->getTimestamp();

                    $result['iso'] = $date->format('Y-m-d');
                    $result['sort_timestamp'] = $date->getTimestamp();
                    $result['group_sort'] = $group_sort;
                    $result['group_key'] = $month_anchor->format('Y-m');
                    $result['group_label'] = flacso_oferta_archive_uppercase_label(
                        wp_date('F Y', $group_sort, $timezone)
                    );

                    return $result;
                }
            }
        }

        if (
            preg_match('/^(\d{4})[-\/](\d{1,2})$/', $raw, $matches)
            || preg_match('/^(\d{1,2})[-\/](\d{4})$/', $raw, $matches)
        ) {
            if (strlen($matches[1]) === 4) {
                $year = (int) $matches[1];
                $month = (int) $matches[2];
            } else {
                $month = (int) $matches[1];
                $year = (int) $matches[2];
            }

            if ($month >= 1 && $month <= 12) {
                $date = DateTimeImmutable::createFromFormat('Y-m-d|', sprintf('%04d-%02d-01', $year, $month), $timezone);
                if ($date instanceof DateTimeImmutable) {
                    $group_sort = $date->getTimestamp();

                    $result['sort_timestamp'] = $group_sort;
                    $result['group_sort'] = $group_sort;
                    $result['group_key'] = $date->format('Y-m');
                    $result['group_label'] = flacso_oferta_archive_uppercase_label(
                        wp_date('F Y', $group_sort, $timezone)
                    );

                    return $result;
                }
            }
        }

        if (preg_match('/^\d{4}$/', $raw)) {
            $year = (int) $raw;
            $date = DateTimeImmutable::createFromFormat('Y-m-d|', sprintf('%04d-01-01', $year), $timezone);
            if ($date instanceof DateTimeImmutable) {
                $year_sort = $date->getTimestamp();

                $result['sort_timestamp'] = $year_sort;
                $result['group_sort'] = $year_sort;
                $result['group_key'] = 'year-' . $year;
                $result['group_label'] = sprintf(
                    __('%s (mes a confirmar)', 'flacso-oferta-academica'),
                    $year
                );
            }
        }

        return $result;
    }
}

$term = get_queried_object();
$taxonomy_name = $term->name;
if (class_exists('FLACSO_Oferta_Academica') && method_exists('FLACSO_Oferta_Academica', 'tipos')) {
    $tipos = FLACSO_Oferta_Academica::tipos();
    if (isset($tipos[$term->slug])) {
        $taxonomy_name = $tipos[$term->slug];
    }
} elseif ($term->slug === 'doctorado') {
    $taxonomy_name = 'Doctorados';
} elseif ($term->slug === 'maestria') {
    $taxonomy_name = 'Maestrías';
} elseif ($term->slug === 'especializacion') {
    $taxonomy_name = 'Especializaciones';
} elseif ($term->slug === 'diplomado') {
    $taxonomy_name = 'Diplomados';
} elseif ($term->slug === 'diploma') {
    $taxonomy_name = 'Diplomas';
} elseif ($term->slug === 'seminario') {
    $taxonomy_name = 'Seminarios';
}

$term_image_data = null;
if (class_exists('Oferta_Taxonomies') && method_exists('Oferta_Taxonomies', 'get_term_featured_image_data')) {
    $term_image_data = Oferta_Taxonomies::get_term_featured_image_data($term);
}

if (class_exists('Oferta_Renderer')) {
    Oferta_Renderer::enqueue_styles();
}

$archive_args = [
    'post_type' => 'oferta-academica',
    'post_status' => current_user_can('manage_options') ? ['publish', 'private'] : ['publish'],
    'posts_per_page' => -1,
    'no_found_rows' => true,
    'has_password' => false,
    'tax_query' => [
        [
            'taxonomy' => 'tipo-oferta-academica',
            'field' => 'term_id',
            'terms' => (int) $term->term_id,
        ],
    ],
];

$ofertas_query = new WP_Query($archive_args);
$offers_grouped = [];

if ($ofertas_query->have_posts()) {
    while ($ofertas_query->have_posts()) {
        $ofertas_query->the_post();

        $post_id = get_the_ID();
        $thumbnail_url = get_the_post_thumbnail_url($post_id, 'medium_large');
        $inscripciones_abiertas = get_post_meta($post_id, 'inscripciones_abiertas', true);
        $is_open = function_exists('flacso_offer_accepts_preinscriptions')
            ? flacso_offer_accepts_preinscriptions($post_id)
            : ($inscripciones_abiertas === '1' || $inscripciones_abiertas === 'true' || $inscripciones_abiertas === true || $inscripciones_abiertas === 1);

        $duracion_meses = get_post_meta($post_id, 'duracion_meses', true);
        if (is_array($duracion_meses)) {
            $duracion_meses = reset($duracion_meses);
        }
        $duracion = !empty($duracion_meses) && class_exists('Oferta_Renderer')
            ? Oferta_Renderer::format_duration_months((string) $duracion_meses, 'flacso-uruguay')
            : '';
        $start_data = flacso_oferta_archive_get_start_data($post_id);
        $group_key = (string) $start_data['group_key'];

        if (!isset($offers_grouped[$group_key])) {
            $offers_grouped[$group_key] = [
                'label' => (string) $start_data['group_label'],
                'sort' => (int) $start_data['group_sort'],
                'items' => [],
            ];
        }

        $offers_grouped[$group_key]['items'][] = [
            'title' => get_the_title($post_id),
            'permalink' => get_permalink($post_id),
            'thumbnail_url' => $thumbnail_url,
            'is_open' => $is_open,
            'duracion' => $duracion,
            'excerpt' => wp_trim_words(get_the_excerpt($post_id), 18, '...'),
            'start_label' => (string) $start_data['label'],
            'start_iso' => (string) $start_data['iso'],
            'sort_timestamp' => (int) $start_data['sort_timestamp'],
        ];
    }
}

wp_reset_postdata();

$offers_flat = [];

if (!empty($offers_grouped)) {
    foreach ($offers_grouped as $group) {
        foreach ($group['items'] as $item) {
            $offers_flat[] = $item;
        }
    }

    usort($offers_flat, static function (array $a, array $b): int {
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
}

$total_ofertas = 0;
$ofertas_abiertas = 0;
$tiene_abiertas = false;

foreach ($offers_flat as $item) {
    $total_ofertas++;

    if (!empty($item['is_open'])) {
        $ofertas_abiertas++;
        $tiene_abiertas = true;
    }
}



get_header();
?>

<main class="flacso-taxonomy-oferta">
    <div class="flacso-taxonomy-container">

        <header class="flacso-taxonomy-hero">
            <div class="flacso-taxonomy-hero__body">
                <nav class="flacso-taxonomy-breadcrumb" aria-label="Navegación">
                    <a href="<?php echo esc_url(home_url('/')); ?>">Inicio</a>
                    <span>/</span>
                    <a href="<?php echo esc_url(home_url('/')); ?>">Oferta académica</a>
                    <span>/</span>
                    <span><?php echo esc_html($taxonomy_name); ?></span>
                </nav>

                <h1 class="flacso-taxonomy-hero__title"><?php echo esc_html($taxonomy_name); ?></h1>

                <p class="flacso-taxonomy-hero__description">
                    Conocé las propuestas disponibles. Revisá la duración, modalidad, fecha de inicio y requisitos de inscripción de cada programa.
                </p>
            </div>

            <?php if (!empty($term_image_data['large']) || !empty($term_image_data['url'])) : ?>
                <figure class="flacso-taxonomy-hero__figure">
                    <img
                        src="<?php echo esc_url((string) ($term_image_data['large'] ?? $term_image_data['url'] ?? '')); ?>"
                        alt="<?php echo esc_attr((string) ($term_image_data['alt'] ?? $taxonomy_name)); ?>"
                        class="flacso-taxonomy-hero__image"
                    />
                </figure>
            <?php endif; ?>
        </header>

            <div id="flacso-listado-ofertas"></div>
            <?php if (!empty($offers_flat)) : ?>
                <section class="flacso-ofertas-listado" aria-labelledby="flacso-ofertas-titulo">
                    <div class="flacso-ofertas-toolbar">
                        <h2 id="flacso-ofertas-titulo" class="flacso-ofertas-toolbar__title">
                            Propuestas disponibles
                        </h2>


                    </div>

                    <div class="flacso-ofertas-grid">
                        <?php foreach ($offers_flat as $item) : ?>
                                <article class="grid-item-wrap">
                                    <a
                                        href="<?php echo esc_url((string) $item['permalink']); ?>"
                                        class="flacso-premium-card flacso-premium-card--link h-100 w-100"
                                        aria-label="<?php echo esc_attr('Ver información de ' . (string) $item['title']); ?>"
                                        data-flacso-offer-click="1"
                                        data-offer-title="<?php echo esc_attr((string) $item['title']); ?>"
                                        data-offer-status="<?php echo !empty($item['is_open']) ? 'inscripciones_abiertas' : 'proxima_apertura'; ?>"
                                    >
                                        <div class="flacso-premium-card__image-wrap">
                                            <?php if (!empty($item['thumbnail_url'])) : ?>
                                                <img src="<?php echo esc_url((string) $item['thumbnail_url']); ?>" class="flacso-premium-card__img" alt="<?php echo esc_attr((string) $item['title']); ?>">
                                            <?php else : ?>
                                                <div class="flacso-premium-card__img-placeholder">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-mortarboard" viewBox="0 0 16 16">
                                                        <path d="M8.211 2.047a.5.5 0 0 0-.422 0l-7.5 3.5a.5.5 0 0 0 .025.917l7.5 3a.5.5 0 0 0 .372 0L14 7.14V13a1 1 0 0 0-1 1v2h3v-2a2 2 0 0 0-1-1V6.739l.686-.275a.5.5 0 0 0 .025-.917l-7.5-3.5ZM8 8.46 1.758 5.965 8 3.052l6.242 2.913L8 8.46Z"/>
                                                        <path d="M4.176 9.032a.5.5 0 0 0-.656.327l-.5 1.7a.5.5 0 0 0 .294.605l4.5 1.8a.5.5 0 0 0 .372 0l4.5-1.8a.5.5 0 0 0 .294-.605l-.5-1.7a.5.5 0 0 0-.656-.327L8 10.466 4.176 9.032Zm-.068 1.873.22-.748 3.496 1.311a.5.5 0 0 0 .352 0l3.496-1.311.22.748L8 12.46l-3.892-1.555Z"/>
                                                    </svg>
                                                </div>
                                            <?php endif; ?>

                                            <div class="flacso-premium-card__badges">
                                                <?php if (!empty($item['is_open'])) : ?>
                                                    <span class="flacso-badge flacso-badge--open">Inscripciones abiertas</span>
                                                <?php else : ?>
                                                    <span class="flacso-badge flacso-badge--soon">Próxima apertura</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="flacso-premium-card__body">
                                            <div class="flacso-premium-card__meta">
                                                <?php if (!empty($item['duracion'])) : ?>
                                                    <span class="flacso-meta-item">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/></svg>
                                                        <?php echo esc_html((string) $item['duracion']); ?>
                                                    </span>
                                                <?php endif; ?>

                                                <?php if (!empty($item['start_label'])) : ?>
                                                    <span class="flacso-meta-item flacso-meta-item--start">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v1H0V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5ZM16 14a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V5h16v9ZM4.5 7a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1h-1Z"/></svg>
                                                        <?php if (!empty($item['start_iso'])) : ?>
                                                            <time datetime="<?php echo esc_attr((string) $item['start_iso']); ?>"><?php echo esc_html((string) $item['start_label']); ?></time>
                                                        <?php else : ?>
                                                            <span><?php echo esc_html((string) $item['start_label']); ?></span>
                                                        <?php endif; ?>
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
    ? 'Ver información e inscribirme'
    : 'Ver información del programa';
?>
<span class="flacso-premium-card__cta <?php echo !empty($item['is_open']) ? 'flacso-premium-card__cta--open' : ''; ?>">
                                                    <?php echo esc_html($card_cta); ?>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                        <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                                                    </svg>
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
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="text-muted mb-3 d-block mx-auto" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/></svg>
                    <h3 class="fw-bold" style="color: var(--flacso-blue-dark);">Próximamente</h3>
                    <p class="text-muted">En este momento no hay propuestas académicas listadas en esta categoría. Te invitamos a estar atento/a a nuestras próximas aperturas.</p>
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
        var destination = link.href || '';

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
