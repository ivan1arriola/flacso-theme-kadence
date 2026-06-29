<?php
/**
 * Template takeover para la categoría Novedades.
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Asegurarse de que el renderizador esté disponible
if (!function_exists('flacso_section_novedades_render')) {
    require_once FLACSO_MAIN_PAGE_MODULE_PATH . 'sections/novedades-section.php';
}
?>

<main id="main" class="site-main flacso-novedades-archive" role="main">
    
    <!-- Premium Hero Section -->
    <header class="flacso-novedades-archive-hero">
        <div class="flacso-content-shell">
            <div class="flacso-novedades-archive-hero-content">
                <nav class="flacso-breadcrumbs" aria-label="Breadcrumb">
                    <ol>
                        <li><a href="<?php echo esc_url(home_url('/')); ?>">Inicio</a></li>
                        <li aria-current="page">Novedades</li>
                    </ol>
                </nav>
                <h1 class="flacso-novedades-archive-title">Todas las Novedades</h1>
                <p class="flacso-novedades-archive-subtitle">
                    Entérate de las últimas noticias, comunicados y artículos de interés de FLACSO Uruguay.
                </p>
            </div>
        </div>
    </header>

    <!-- Novedades Standard Grid -->
    <div class="flacso-novedades-archive-content container pt-5 pb-5">
        <?php if (have_posts()) : ?>
            <div class="novedades-responsivas">
                <div class="novedades-grid">
                    <?php 
                    $index = 0;
                    while (have_posts()) : the_post(); 
                        global $post;
                        echo flacso_section_novedades_render_post_card($post, false, $index);
                        $index++;
                    endwhile; 
                    ?>
                </div>
            </div>

            <!-- Paginación de WordPress -->
            <div class="flacso-pagination mt-5 mb-5 d-flex justify-content-center">
                <?php 
                the_posts_pagination([
                    'mid_size'  => 2,
                    'prev_text' => '&larr; ' . __('Anterior', 'flacso-main-page'),
                    'next_text' => __('Siguiente', 'flacso-main-page') . ' &rarr;',
                    'class' => 'flacso-pagination-links'
                ]); 
                ?>
            </div>
            
        <?php else : ?>
            <div class="alert alert-info text-center p-4">
                <?php esc_html_e('No se encontraron novedades.', 'flacso-main-page'); ?>
            </div>
        <?php endif; ?>
    </div>

</main>

<style>
/* Estilos para el Hero de Archivo */
.flacso-novedades-archive-hero {
    background: linear-gradient(135deg, var(--global-palette1, #1d3a72) 0%, #152b55 100%);
    color: #ffffff;
    padding: 6rem 0 4rem;
    position: relative;
    overflow: hidden;
}

.flacso-novedades-archive-hero::after {
    content: '';
    position: absolute;
    bottom: -20px;
    right: -5%;
    width: 40%;
    height: 120%;
    background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0) 70%);
    border-radius: 50%;
    pointer-events: none;
}

.flacso-novedades-archive-hero-content {
    max-width: 800px;
    position: relative;
    z-index: 2;
}

.flacso-novedades-archive-title {
    color: #ffffff;
    font-size: clamp(2.5rem, 5vw, 4rem);
    font-weight: 800;
    margin: 1rem 0;
    line-height: 1.1;
    letter-spacing: -0.02em;
}

.flacso-novedades-archive-subtitle {
    font-size: clamp(1.1rem, 2vw, 1.25rem);
    color: rgba(255,255,255,0.85);
    margin: 0;
    line-height: 1.6;
}

/* Breadcrumbs */
.flacso-breadcrumbs ol {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 600;
    opacity: 0.8;
}

.flacso-breadcrumbs a {
    color: #ffffff;
    text-decoration: none;
    transition: opacity 0.2s ease;
}

.flacso-breadcrumbs a:hover {
    opacity: 0.7;
}

.flacso-breadcrumbs li:not(:last-child)::after {
    content: '/';
    margin-left: 0.5rem;
    opacity: 0.5;
}

/* Sobrescribir el estilo del módulo de portada para que se adapte al archivo */
.flacso-novedades-archive .flacso-novedades-archive-content {
    padding-top: 3rem;
    padding-bottom: 5rem;
}

/* Pagination styles */
.flacso-pagination .nav-links {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    justify-content: center;
    align-items: center;
}

.flacso-pagination .page-numbers {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 2.5rem;
    height: 2.5rem;
    padding: 0 0.75rem;
    border-radius: 8px;
    background: #f7faff;
    color: var(--global-palette1, #1d3a72);
    font-weight: 600;
    text-decoration: none;
    border: 1px solid rgba(29, 58, 114, 0.15);
    transition: all 0.2s ease;
}

.flacso-pagination .page-numbers.current {
    background: var(--global-palette1, #1d3a72);
    color: #ffffff;
    border-color: var(--global-palette1, #1d3a72);
}

.flacso-pagination .page-numbers:hover:not(.current) {
    background: #e9f0fc;
    border-color: rgba(29, 58, 114, 0.3);
}

/* Styles for Novedades Grid */
.novedades-responsivas {
    --novedad-radius: 24px;
    --novedad-radius-sm: 18px;
    --novedad-border: rgba(17, 48, 96, 0.10);
    --novedad-border-strong: rgba(29, 58, 114, 0.16);
    --novedad-shadow: 0 10px 30px rgba(15, 26, 45, 0.07);
    --novedad-shadow-hover: 0 18px 40px rgba(15, 26, 45, 0.12);
    --novedad-bg: #ffffff;
    --novedad-bg-soft: linear-gradient(180deg, #ffffff 0%, #f7faff 100%);
    --novedad-text: var(--global-palette3, #0f1a2d);
    --novedad-muted: var(--global-palette5, #7a8696);
    --novedad-line: rgba(17, 48, 96, 0.08);
    --novedad-accent: var(--global-palette1, #1d3a72);
    --novedad-section-gap: clamp(0.9rem, 1.4vw, 1.25rem);
}

.novedades-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--novedad-section-gap);
    margin: 0;
    padding: 0;
    align-items: stretch;
}

.novedades-grid > .novedad-item {
    width: 100%;
    max-width: 100%;
    height: 100%;
}

.novedades-grid .card {
    position: relative;
    display: grid;
    grid-template-columns: 1fr;
    align-items: start;
    align-content: start;
    background: var(--novedad-bg);
    border: 1px solid var(--novedad-border);
    border-radius: var(--novedad-radius-sm);
    overflow: hidden;
    box-shadow: var(--novedad-shadow);
    transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
    height: 100%;
}

.novedades-grid .card:hover {
    transform: translateY(-3px);
    box-shadow: var(--novedad-shadow-hover);
    border-color: rgba(29, 58, 114, 0.20);
}

.novedades-grid .card-img-container {
    position: relative;
    width: 100%;
    aspect-ratio: 1 / 1;
    overflow: hidden;
    background: #ffffff;
    border-bottom: 1px solid rgba(0,0,0,0.04);
}

.novedades-grid .card-img-link {
    position: absolute;
    inset: 0;
    display: block;
}

.novedades-grid .card-img-container img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    object-position: center;
    transition: transform 0.35s ease;
}

.novedades-grid .card:hover .card-img-container img {
    transform: scale(1.03);
}

.novedades-grid .card-body {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    padding: 1rem;
}

.novedades-grid .card h3 {
    margin: 0;
    font-size: 1rem;
    font-weight: 800;
    line-height: 1.24;
    color: var(--novedad-text);
}

.novedades-grid .card h3 a {
    color: inherit;
    text-decoration: none;
}

.novedades-grid .card h3 a:hover {
    color: var(--novedad-accent);
}

.novedades-grid .card-text {
    margin: 0;
    color: var(--global-palette4, #2e2f34);
    font-size: 0.93rem;
    line-height: 1.45;
}

.novedades-grid footer {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: auto;
    padding-top: 0.75rem;
    border-top: 1px solid var(--novedad-line);
    color: var(--novedad-muted);
    font-size: 0.82rem;
}

.novedades-grid footer::before {
    content: "";
    width: 0.48rem;
    height: 0.48rem;
    border-radius: 999px;
    background: var(--novedad-accent);
    opacity: 0.75;
}

@media (min-width: 768px) and (max-width: 1099.98px) {
    .novedades-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }
}

@media (min-width: 1100px) {
    .novedades-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1.25rem;
    }
}

@media (max-width: 767.98px) {
    .novedades-grid {
        grid-template-columns: 1fr;
        gap: 1.25rem;
    }
}
</style>

<?php
get_footer();
