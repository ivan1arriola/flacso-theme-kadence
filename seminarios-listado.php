<?php
/**
 * Catálogo público de seminarios.
 *
 * Compatible con la base legacy y con el modelo académico unificado.
 * Utiliza el View Model centralizado de inc/academic-public-model.php.
 *
 * @package kadence-child-flacso
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

if (function_exists('flacso_global_styles')) {
    flacso_global_styles();
}

$hero_image_url = 'https://flacso.edu.uy/wp-content/uploads/2026/02/seminarios-artwork.png';
$hero_image_id = attachment_url_to_postid($hero_image_url);
if ($hero_image_id) {
    $hero_cropped_url = wp_get_attachment_image_url($hero_image_id, 'flacso-seminario-4x5');
    if ($hero_cropped_url) {
        $hero_image_url = $hero_cropped_url;
    }
}
$hoy = new DateTimeImmutable('today', wp_timezone());

$posgrado_filtro = isset($_GET['posgrado']) ? absint(wp_unslash($_GET['posgrado'])) : 0;
if ($posgrado_filtro <= 0 && isset($_GET['programa'])) {
    $posgrado_filtro = absint(wp_unslash($_GET['programa']));
}

$meses_es = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
$dias_semana_es = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
$fallback_variants = ['a', 'b', 'c', 'd'];

$parse_date = static function ($value) {
    if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        return null;
    }

    $date = DateTimeImmutable::createFromFormat('Y-m-d|', $value, wp_timezone());
    if (!$date) {
        return null;
    }

    return $date->setTime(0, 0, 0);
};

$format_month = static function (DateTimeImmutable $date) use ($meses_es) {
    return $meses_es[(int) $date->format('n')] . ' ' . $date->format('Y');
};

$format_date_long = static function (DateTimeImmutable $date) use ($meses_es, $dias_semana_es) {
    $dia_semana = $dias_semana_es[(int) $date->format('w')];
    $dia = (int) $date->format('j');
    $mes = $meses_es[(int) $date->format('n')];
    $ano = $date->format('Y');

    return sprintf('%s %d de %s de %s', $dia_semana, $dia, $mes, $ano);
};

$days_diff = static function (DateTimeImmutable $from, DateTimeImmutable $to) {
    return (int) floor(($to->getTimestamp() - $from->getTimestamp()) / DAY_IN_SECONDS);
};

$get_docentes_label = static function ($docentes_meta) {
    if (!is_array($docentes_meta) || empty($docentes_meta)) {
        return __('Docentes a confirmar', 'flacso-uruguay');
    }

    $docentes_count = count(array_values(array_filter(array_map('intval', $docentes_meta))));
    if ($docentes_count <= 0) {
        return __('Docentes a confirmar', 'flacso-uruguay');
    }

    return $docentes_count === 1 ? __('1 docente', 'flacso-uruguay') : sprintf(__('%d docentes', 'flacso-uruguay'), $docentes_count);
};

$get_creditos_value = static function ($value) {
    if ($value === '' || $value === null) {
        return '';
    }

    if (!is_numeric($value)) {
        return '';
    }

    $number = (float) $value;
    if ((float) (int) $number === $number) {
        return (string) (int) $number;
    }

    return rtrim(rtrim(number_format($number, 1, '.', ''), '0'), '.');
};

$catalog_raw = function_exists('flacso_theme_get_seminars_catalog')
    ? flacso_theme_get_seminars_catalog()
    : [];

$seminarios_catalogo = [];
$seminarios_por_posgrado = [];

foreach ($catalog_raw as $item_context) {
    $seminario_id = (int) $item_context['offer_id'];

    // Si es componente de un seminario integrado, se omite como tarjeta independiente del listado
    if (!empty($item_context['relations']['integrated_parent'])) {
        continue;
    }

    $fecha_inicio = (string) ($item_context['instance']['fecha_inicio'] ?? '');
    $inicio_obj = $parse_date($fecha_inicio);
    if (!$inicio_obj) {
        continue;
    }

    $dias_hasta_inicio = $days_diff($hoy, $inicio_obj);

    $is_upcoming = $dias_hasta_inicio >= 0;
    $is_started_recent = $dias_hasta_inicio < 0 && !empty($item_context['registration']['is_open']);
    $is_visible = $is_upcoming || $is_started_recent;

    if (!$is_visible) {
        continue;
    }

    // Programas vinculados
    $related_progs = $item_context['relations']['related_programs'] ?? [];
    $related_ids = array_map(static function ($p) {
        return (int) $p['id'];
    }, $related_progs);

    $seminarios_por_posgrado[$seminario_id] = $related_ids;

    if ($posgrado_filtro > 0 && !in_array($posgrado_filtro, $related_ids, true)) {
        continue;
    }

    $title = (string) $item_context['title'];
    $modalidad = trim(wp_strip_all_tags((string) ($item_context['instance']['modalidad'] ?? '')));
    if ($modalidad === '') {
        $modalidad = __('No especificado', 'flacso-uruguay');
    }

    $creditos = $get_creditos_value($item_context['academic']['creditos'] ?? '');
    $carga_horaria = '';
    if (!empty($item_context['academic']['carga_horaria']) && is_numeric($item_context['academic']['carga_horaria'])) {
        $carga_horaria = (string) (int) $item_context['academic']['carga_horaria'];
    }

    $month_key = $inicio_obj->format('Y-m');
    $month_label = $format_month($inicio_obj);
    $fecha_larga = $format_date_long($inicio_obj);

    $image_id = (int) get_post_thumbnail_id($seminario_id);
    $image_url = '';
    if ($image_id > 0) {
        $cropped_image_url = wp_get_attachment_image_url($image_id, 'flacso-seminario-4x5');
        if ($cropped_image_url) {
            $image_url = (string) $cropped_image_url;
        }
    }
    if ($image_url === '') {
        $image_url = (string) ($item_context['thumbnail_url'] ?? '');
    }
    $permalink = (string) ($item_context['permalink'] ?? get_permalink($seminario_id));

    $desc_raw = (string) ($item_context['academic']['presentacion'] ?? '');
    if ($desc_raw === '') {
        $desc_raw = (string) ($item_context['academic']['objetivo_general'] ?? '');
    }
    if ($desc_raw === '') {
        $desc_raw = (string) get_the_excerpt($seminario_id);
    }
    $description = wp_trim_words(wp_strip_all_tags($desc_raw), 34, '...');

    $docentes_meta = $item_context['instance']['docentes_ids'] ?? [];

    $seminarios_catalogo[] = [
        'id'               => $seminario_id,
        'title'            => $title,
        'description'      => $description,
        'month_key'        => $month_key,
        'month_label'      => $month_label,
        'date_long'        => $fecha_larga,
        'days_left'        => $dias_hasta_inicio,
        'days_since_start' => max(0, -$dias_hasta_inicio),
        'is_upcoming'      => $is_upcoming,
        'is_started_recent'=> $is_started_recent,
        'is_open'          => !empty($item_context['registration']['is_open']),
        'modality'         => $modalidad,
        'credits'          => $creditos,
        'carga_horaria'    => $carga_horaria,
        'docentes_label'   => $get_docentes_label($docentes_meta),
        'image_id'         => $image_id,
        'image_url'        => $image_url,
        'permalink'        => $permalink,
        'timestamp'        => $inicio_obj->getTimestamp(),
        'fallback'         => $fallback_variants[$seminario_id % count($fallback_variants)],
    ];
}

usort($seminarios_catalogo, static function ($a, $b) {
    if ($a['timestamp'] === $b['timestamp']) {
        return $a['id'] <=> $b['id'];
    }
    return $a['timestamp'] <=> $b['timestamp'];
});

$seminario_destacado = null;
foreach ($seminarios_catalogo as $item) {
    if ($item['is_upcoming']) {
        $seminario_destacado = $item;
        break;
    }
}
if (!$seminario_destacado && !empty($seminarios_catalogo)) {
    $seminario_destacado = $seminarios_catalogo[0];
}

$seminarios_agrupados = [];
foreach ($seminarios_catalogo as $item) {
    $key = $item['month_key'];
    if (!isset($seminarios_agrupados[$key])) {
        $seminarios_agrupados[$key] = [
            'month_label' => $item['month_label'],
            'items'       => [],
        ];
    }
    $seminarios_agrupados[$key]['items'][] = $item;
}

$seminarios_total = count($seminarios_catalogo);
?>

<div class="content-area flacso-seminarios-page">
    <main id="main" class="site-main">
        <header class="seminarios-hero" role="banner">
            <div class="site-container seminarios-hero__inner">
                <div class="seminarios-hero__poster">
                    <div class="seminarios-hero__content">
                        <div class="seminarios-hero__dots" aria-hidden="true">
                            <span></span><span></span><span></span><span></span><span></span>
                        </div>

                        <p class="seminarios-hero__kicker"><?php esc_html_e('Formación de posgrado', 'flacso-uruguay'); ?></p>
                        <h1 id="seminarios-page-title" class="seminarios-hero__title"><?php esc_html_e('Seminarios', 'flacso-uruguay'); ?></h1>
                        <div class="seminarios-hero__editorial">
                            <p><strong class="seminarios-hero__editorial-highlight">Los seminarios de posgrado de FLACSO Uruguay son propuestas de formación de corta duración, diseñadas para la actualización teórica y profesional en distintos campos de las ciencias sociales.</strong> Tienen una duración aproximada de entre tres y cinco semanas y se vinculan con los diferentes programas académicos de la institución.</p>
                            <p>Se ofrecen en modalidad virtual o híbrida, lo que permite la participación de personas residentes en Montevideo, en el interior del país o en el exterior. La cursada combina actividades que pueden realizarse con flexibilidad horaria y encuentros sincrónicos o presenciales previamente establecidos (según el seminario).</p>
                            <p>Los seminarios pueden ser acreditables a estudios de maestría o doctorado, de acuerdo con las características de cada propuesta y los criterios del programa correspondiente. En la información de cada seminario podrás consultar sus objetivos, contenidos, modalidad de cursada, duración, equipo docente, cantidad de créditos, requisitos e inscripción.</p>
                        </div>

                        <div class="seminarios-hero__facts" aria-label="<?php esc_attr_e('Características principales', 'flacso-uruguay'); ?>">
                            <span><strong>3–5</strong> <?php esc_html_e('semanas', 'flacso-uruguay'); ?></span>
                            <span><?php esc_html_e('Virtual o híbrida', 'flacso-uruguay'); ?></span>
                            <span><?php esc_html_e('Posibilidad de acreditación', 'flacso-uruguay'); ?></span>
                        </div>

                        <p class="seminarios-hero__contact">
                            <?php esc_html_e('Por dudas o consultas:', 'flacso-uruguay'); ?>
                            <a href="mailto:inscripciones@flacso.edu.uy">inscripciones@flacso.edu.uy</a>
                        </p>
                    </div>

                    <?php if (!empty($hero_image_url)) : ?>
                        <div class="seminarios-hero__visual" aria-hidden="true">
                            <img
                                class="seminarios-hero__image"
                                src="<?php echo esc_url($hero_image_url); ?>"
                                alt=""
                                loading="eager"
                                decoding="async"
                            >
                        </div>
                    <?php endif; ?>
                </div>

                <div class="seminarios-hero__tools">
                    <div class="seminarios-hero__search">
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <label class="screen-reader-text" for="seminarios-catalog-search"><?php esc_html_e('Buscar seminarios', 'flacso-uruguay'); ?></label>
                        <input id="seminarios-catalog-search" type="search" placeholder="<?php esc_attr_e('Buscar por seminario, modalidad o palabra clave', 'flacso-uruguay'); ?>" autocomplete="off" data-seminar-search>
                    </div>
                    <div class="seminarios-hero__search-meta">
                        <span class="seminarios-hero__search-count" data-seminar-search-count aria-live="polite"></span>
                    </div>

                    <?php if (!empty($seminarios_agrupados)) : ?>
                        <nav class="seminarios-hero__months" aria-label="<?php esc_attr_e('Filtrar seminarios por mes', 'flacso-uruguay'); ?>">
                            <button class="is-active" type="button" data-seminar-month="all" aria-pressed="true"><?php esc_html_e('Todos', 'flacso-uruguay'); ?></button>
                            <?php foreach ($seminarios_agrupados as $month_key => $grupo) : ?>
                                <button type="button" data-seminar-month="<?php echo esc_attr($month_key); ?>" aria-pressed="false"><?php echo esc_html($grupo['month_label']); ?></button>
                            <?php endforeach; ?>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <section id="seminarios-destacados" class="seminarios-main" aria-labelledby="seminarios-destacados-titulo">
            <div class="site-container">
                <?php if ($seminario_destacado) : ?>
                    <?php
                    $featured_badge = __('Fecha a confirmar', 'flacso-uruguay');
                    if ($seminario_destacado['is_upcoming']) {
                        if ($seminario_destacado['days_left'] === 0) {
                            $featured_badge = __('Comienza hoy', 'flacso-uruguay');
                        } elseif ($seminario_destacado['days_left'] === 1) {
                            $featured_badge = __('Falta 1 día', 'flacso-uruguay');
                        } else {
                            $featured_badge = sprintf(__('Faltan %d días', 'flacso-uruguay'), $seminario_destacado['days_left']);
                        }
                    } elseif ($seminario_destacado['is_started_recent']) {
                        if ($seminario_destacado['days_since_start'] === 1) {
                            $featured_badge = __('Inició hace 1 día', 'flacso-uruguay');
                        } else {
                            $featured_badge = sprintf(__('Inició hace %d días', 'flacso-uruguay'), $seminario_destacado['days_since_start']);
                        }
                    }
                    ?>
                    <article class="seminario-featured" aria-labelledby="seminario-destacado-titulo">
                        <a class="seminario-featured__media-link" href="<?php echo esc_url($seminario_destacado['permalink']); ?>">
                            <?php if (!empty($seminario_destacado['image_url'])) : ?>
                                <?php
                                if (!empty($seminario_destacado['image_id'])) {
                                    echo wp_get_attachment_image(
                                        (int) $seminario_destacado['image_id'],
                                        'flacso-seminario-4x5',
                                        false,
                                        [
                                            'alt'      => $seminario_destacado['title'],
                                            'class'    => 'seminario-featured__image',
                                            'loading'  => 'eager',
                                            'decoding' => 'async',
                                            'sizes'    => '(max-width: 767px) 112px, (max-width: 980px) 448px, 360px',
                                        ]
                                    );
                                } else {
                                    ?>
                                    <img
                                        src="<?php echo esc_url($seminario_destacado['image_url']); ?>"
                                        alt="<?php echo esc_attr($seminario_destacado['title']); ?>"
                                        class="seminario-featured__image"
                                        loading="eager"
                                        decoding="async"
                                    >
                                    <?php
                                }
                                ?>
                            <?php else : ?>
                                <div class="seminario-featured__fallback seminario-featured__fallback--<?php echo esc_attr($seminario_destacado['fallback']); ?>" aria-hidden="true">
                                    <span>FLACSO</span>
                                </div>
                            <?php endif; ?>
                            <div class="seminario-featured__overlay" aria-hidden="true"></div>
                        </a>

                        <div class="seminario-featured__body">
                            <div class="seminario-featured__top">
                                <span class="seminarios-chip seminarios-chip--gold"><?php esc_html_e('Próximo inicio', 'flacso-uruguay'); ?></span>
                                <span class="seminarios-chip seminarios-chip--soft"><?php echo esc_html($featured_badge); ?></span>
                            </div>

                            <h2 id="seminario-destacado-titulo" class="seminario-featured__title">
                                <a href="<?php echo esc_url($seminario_destacado['permalink']); ?>">
                                    <?php echo esc_html($seminario_destacado['title']); ?>
                                </a>
                            </h2>

                            <p class="seminario-featured__description">
                                <?php echo esc_html($seminario_destacado['description']); ?>
                            </p>

                            <ul class="seminario-featured__meta" role="list">
                                <li class="seminario-meta--date">
                                    <i class="bi bi-calendar3" aria-hidden="true"></i>
                                    <span><?php echo esc_html($seminario_destacado['date_long']); ?></span>
                                </li>
                                <li class="seminario-meta--modality">
                                    <i class="bi bi-laptop" aria-hidden="true"></i>
                                    <span><?php echo esc_html($seminario_destacado['modality']); ?></span>
                                </li>
                                <li class="seminario-meta--teachers">
                                    <i class="bi bi-people" aria-hidden="true"></i>
                                    <span><?php echo esc_html($seminario_destacado['docentes_label']); ?></span>
                                </li>
                            </ul>

                            <div class="seminario-featured__footer">
                                <span class="seminario-featured__credits">
                                    <?php
                                    if (!empty($seminario_destacado['credits'])) {
                                        echo esc_html($seminario_destacado['credits'] . ' ' . __('créditos', 'flacso-uruguay'));
                                    } else {
                                        esc_html_e('Créditos a confirmar', 'flacso-uruguay');
                                    }
                                    ?>
                                </span>
                                <a class="seminarios-btn seminarios-btn--primary" href="<?php echo esc_url($seminario_destacado['permalink']); ?>">
                                    <?php esc_html_e('Ver seminario', 'flacso-uruguay'); ?>
                                    <i class="bi bi-arrow-right" aria-hidden="true"></i>
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endif; ?>

                <div class="seminarios-catalog" aria-label="<?php esc_attr_e('Catálogo de seminarios', 'flacso-uruguay'); ?>">
                    <div class="seminarios-catalog__header">
                        <div>
                            <h2 id="seminarios-catalogo-titulo"><?php esc_html_e('Próximas propuestas', 'flacso-uruguay'); ?></h2>
                            <p class="seminarios-catalog__lead"><?php esc_html_e('Explora el cronograma completo de seminarios con inscripción abierta.', 'flacso-uruguay'); ?></p>
                        </div>
                        <span class="seminarios-catalog__total">
                            <?php printf(esc_html__('%d seminarios disponibles', 'flacso-uruguay'), (int) $seminarios_total); ?>
                        </span>
                    </div>

                    <?php if (!empty($seminarios_agrupados)) : ?>
                        <div class="seminarios-groups" data-seminarios-groups>
                            <?php
                            $seminarios_group_index = 0;
                            // Abrir el mes en curso y el siguiente; si no hay ninguno, abrir el primer mes disponible.
                            $mes_actual_key = $hoy->format('Y-m');
                            $mes_siguiente_key = $hoy->modify('first day of next month')->format('Y-m');
                            $hay_meses_proximos = isset($seminarios_agrupados[$mes_actual_key]) || isset($seminarios_agrupados[$mes_siguiente_key]);
                            ?>
                            <?php foreach ($seminarios_agrupados as $month_key => $grupo) : ?>
                                <?php $mes_abierto = $month_key === $mes_actual_key || $month_key === $mes_siguiente_key || (!$hay_meses_proximos && $seminarios_group_index === 0); ?>
                                <details class="seminarios-group" data-month-group="<?php echo esc_attr($month_key); ?>"<?php echo $mes_abierto ? ' open' : ''; ?>>
                                    <summary class="seminarios-group__header">
                                        <span id="grupo-<?php echo esc_attr($month_key); ?>" class="seminarios-group__title"><?php echo esc_html($grupo['month_label']); ?></span>
                                        <span class="seminarios-group__header-meta">
                                            <span class="seminarios-group__count"><?php printf(esc_html__('%d seminarios', 'flacso-uruguay'), count($grupo['items'])); ?></span>
                                            <i class="bi bi-chevron-down seminarios-group__toggle" aria-hidden="true"></i>
                                        </span>
                                    </summary>

                                    <div class="seminarios-grid flacso-formacion-grid">
                                        <?php foreach ($grupo['items'] as $seminario_item) : ?>
                                            <article
                                                class="seminario-card flacso-formacion-card<?php echo $seminario_item['is_started_recent'] ? ' is-recent' : ''; ?>"
                                                data-seminar-card
                                                data-month="<?php echo esc_attr($month_key); ?>"
                                                data-search="<?php echo esc_attr(strtolower(remove_accents($seminario_item['title'] . ' ' . $seminario_item['description'] . ' ' . $seminario_item['modality']))); ?>"
                                            >
                                                <a class="seminario-card__media-link flacso-formacion-card__media" href="<?php echo esc_url($seminario_item['permalink']); ?>">
                                                    <?php if (!empty($seminario_item['image_url'])) : ?>
                                                        <?php
                                                        if (!empty($seminario_item['image_id'])) {
                                                            echo wp_get_attachment_image(
                                                                (int) $seminario_item['image_id'],
                                                                'flacso-seminario-4x5',
                                                                false,
                                                                [
                                                                    'alt'      => $seminario_item['title'],
                                                                    'class'    => 'seminario-card__image',
                                                                    'loading'  => 'lazy',
                                                                    'decoding' => 'async',
                                                                    'sizes'    => '(max-width: 767px) 104px, (max-width: 1180px) 30vw, 320px',
                                                                ]
                                                            );
                                                        } else {
                                                            ?>
                                                            <img
                                                                src="<?php echo esc_url($seminario_item['image_url']); ?>"
                                                                alt="<?php echo esc_attr($seminario_item['title']); ?>"
                                                                class="seminario-card__image"
                                                                loading="lazy"
                                                                decoding="async"
                                                            >
                                                            <?php
                                                        }
                                                        ?>
                                                    <?php else : ?>
                                                        <div class="seminario-card__fallback seminario-card__fallback--<?php echo esc_attr($seminario_item['fallback']); ?>" aria-hidden="true">
                                                            <span>FLACSO</span>
                                                        </div>
                                                    <?php endif; ?>

                                                    <div class="seminario-card__overlay" aria-hidden="true"></div>

                                                    <div class="seminario-card__badges" aria-hidden="true">
                                                        <?php if ($seminario_item['is_started_recent']) : ?>
                                                            <span class="seminarios-chip seminarios-chip--dark"><?php esc_html_e('Iniciado recientemente', 'flacso-uruguay'); ?></span>
                                                        <?php elseif ($seminario_item['is_upcoming']) : ?>
                                                            <span class="seminarios-chip seminarios-chip--sky"><?php esc_html_e('Próximamente', 'flacso-uruguay'); ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <span class="flacso-formacion-card__badge"><?php esc_html_e('Seminario', 'flacso-uruguay'); ?></span>
                                                </a>

                                                <div class="seminario-card__content flacso-formacion-card__body">
                                                    <h4 class="seminario-card__title flacso-formacion-card__title">
                                                        <a href="<?php echo esc_url($seminario_item['permalink']); ?>"><?php echo esc_html($seminario_item['title']); ?></a>
                                                    </h4>

                                                    <p class="seminario-card__description flacso-formacion-card__excerpt"><?php echo esc_html($seminario_item['description']); ?></p>

                                                    <ul class="seminario-card__meta" role="list">
                                                        <li class="seminario-meta--date">
                                                            <i class="bi bi-calendar3" aria-hidden="true"></i>
                                                            <span><?php echo esc_html($seminario_item['date_long']); ?></span>
                                                        </li>
                                                        <?php if ($seminario_item['is_upcoming']) : ?>
                                                            <li class="seminario-meta--urgency">
                                                                <i class="bi bi-clock" aria-hidden="true"></i>
                                                                <span>
                                                                    <?php
                                                                    if ($seminario_item['days_left'] === 0) {
                                                                        echo esc_html(__('Comienza hoy', 'flacso-uruguay'));
                                                                    } elseif ($seminario_item['days_left'] === 1) {
                                                                        echo esc_html(__('Falta 1 día', 'flacso-uruguay'));
                                                                    } else {
                                                                        echo esc_html(sprintf(__('Faltan %d días', 'flacso-uruguay'), $seminario_item['days_left']));
                                                                    }
                                                                    ?>
                                                                </span>
                                                            </li>
                                                        <?php elseif ($seminario_item['is_started_recent']) : ?>
                                                            <li class="seminario-meta--urgency">
                                                                <i class="bi bi-clock" aria-hidden="true"></i>
                                                                <span>
                                                                    <?php
                                                                    if ($seminario_item['days_since_start'] === 1) {
                                                                        echo esc_html(__('Inició hace 1 día', 'flacso-uruguay'));
                                                                    } else {
                                                                        echo esc_html(sprintf(__('Inició hace %d días', 'flacso-uruguay'), $seminario_item['days_since_start']));
                                                                    }
                                                                    ?>
                                                                </span>
                                                            </li>
                                                        <?php endif; ?>
                                                        <li class="seminario-meta--modality">
                                                            <i class="bi bi-laptop" aria-hidden="true"></i>
                                                            <span><?php echo esc_html($seminario_item['modality']); ?></span>
                                                        </li>
                                                        <?php if (!empty($seminario_item['credits'])) : ?>
                                                            <li class="seminario-meta--credits">
                                                                <i class="bi bi-journal-check" aria-hidden="true"></i>
                                                                <span><?php echo esc_html($seminario_item['credits'] . ' ' . __('créditos', 'flacso-uruguay')); ?></span>
                                                            </li>
                                                        <?php endif; ?>
                                                        <li class="seminario-meta--teachers">
                                                            <i class="bi bi-people" aria-hidden="true"></i>
                                                            <span><?php echo esc_html($seminario_item['docentes_label']); ?></span>
                                                        </li>
                                                    </ul>

                                                    <div class="seminario-card__footer flacso-formacion-card__footer">
                                                        <span class="flacso-formacion-card__status<?php echo $seminario_item['is_open'] ? ' is-open' : ''; ?>">
                                                            <?php echo esc_html($seminario_item['is_open'] ? __('Inscripciones abiertas', 'flacso-uruguay') : __('Próxima apertura', 'flacso-uruguay')); ?>
                                                        </span>
                                                        <a class="seminario-card__cta flacso-formacion-card__action" href="<?php echo esc_url($seminario_item['permalink']); ?>">
                                                            <?php esc_html_e('Conocer más', 'flacso-uruguay'); ?> →
                                                        </a>
                                                    </div>
                                                </div>
                                            </article>
                                        <?php endforeach; ?>
                                    </div>
                                </details>
                                <?php $seminarios_group_index++; ?>
                            <?php endforeach; ?>
                        </div>
                        <div class="seminarios-empty" role="status" data-seminarios-filter-empty hidden>
                            <span class="seminarios-empty__icon" aria-hidden="true"><i class="bi bi-search"></i></span>
                            <h3><?php esc_html_e('No encontramos seminarios', 'flacso-uruguay'); ?></h3>
                            <p><?php esc_html_e('Probá con otra palabra o seleccioná otro mes.', 'flacso-uruguay'); ?></p>
                        </div>
                    <?php else : ?>
                        <div class="seminarios-empty" role="status">
                            <span class="seminarios-empty__icon" aria-hidden="true"><i class="bi bi-search"></i></span>
                            <h3><?php esc_html_e('No hay seminarios disponibles', 'flacso-uruguay'); ?></h3>
                            <p>
                                <?php if ($posgrado_filtro > 0) : ?>
                                    <?php esc_html_e('No se encontraron seminarios para el posgrado seleccionado.', 'flacso-uruguay'); ?>
                                <?php else : ?>
                                    <?php esc_html_e('No se encontraron seminarios para mostrar en este momento.', 'flacso-uruguay'); ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="seminarios-contacto" aria-labelledby="seminarios-contacto-titulo">
            <div class="site-container">
                <div class="seminarios-contacto__panel">
                    <div class="seminarios-contacto__text">
                        <p class="seminarios-contacto__kicker"><?php esc_html_e('Contacto', 'flacso-uruguay'); ?></p>
                        <h2 id="seminarios-contacto-titulo"><?php esc_html_e('¿Necesitás orientación para elegir un seminario?', 'flacso-uruguay'); ?></h2>
                        <p><?php esc_html_e('Nuestro equipo responde consultas sobre contenidos, fechas, modalidad e inscripción.', 'flacso-uruguay'); ?></p>
                    </div>
                    <div class="seminarios-contacto__actions">
                        <a class="seminarios-btn seminarios-btn--primary" href="mailto:inscripciones@flacso.edu.uy">
                            <?php esc_html_e('Escribínos a inscripciones@flacso.edu.uy', 'flacso-uruguay'); ?>
                        </a>
                        <a class="seminarios-btn seminarios-btn--ghost" href="https://flacso.edu.uy/preguntas-frecuentes/" target="_blank" rel="noopener noreferrer">
                            <?php esc_html_e('Ver preguntas frecuentes', 'flacso-uruguay'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>

<?php get_footer(); ?>
