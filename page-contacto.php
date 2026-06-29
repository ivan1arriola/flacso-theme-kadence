<?php
/**
 * Template para la página de Contacto
 *
 * @package kadence-child-flacso
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$telefono = get_theme_mod('flacso_contacto_telefono', '2481 7459');
$correo   = get_theme_mod('flacso_contacto_email', 'secretaria@flacso.edu.uy');
$direccion = get_theme_mod('flacso_contacto_direccion', '8 de Octubre 2882, CP 11600, Montevideo');
$maps_url  = get_theme_mod('flacso_contacto_maps_url', 'https://maps.google.com/?q=FLACSO+Uruguay+8+de+Octubre+2882+Montevideo');
$embed_url = get_theme_mod('flacso_contacto_embed_url', '');
$agenda_url = get_theme_mod('flacso_contacto_agenda_url', 'https://agenda.flacso.edu.uy/');
$contactos  = flacso_get_contactos();
?>

<main class="flacso-page-contacto">
    <div class="flacso-page-container">

        <header class="flacso-page-hero">
            <div class="flacso-hero-body">
                <nav class="flacso-breadcrumb" aria-label="Navegación">
                    <a href="<?php echo esc_url(home_url('/')); ?>">Inicio</a>
                    <span>/</span>
                    <span>Contacto</span>
                </nav>

                <h1 class="flacso-page-hero-title">Contacto</h1>
                <p class="flacso-page-hero-desc">
                    Encontrá aquí los datos de Secretaría, el formulario de consulta y la ubicación de FLACSO Uruguay.
                </p>
            </div>
        </header>

        <div class="flacso-contacto-grid">

            <section class="flacso-contacto-card">
                <h2 class="flacso-contacto-card-title">Datos de Secretaría</h2>
                <p class="flacso-contacto-item">
                    <strong>Teléfono:</strong>
                    <a href="tel:<?php echo esc_attr(preg_replace('/\D/', '', $telefono)); ?>"><?php echo esc_html($telefono); ?></a>
                </p>
                <p class="flacso-contacto-item">
                    <strong>Correo:</strong>
                    <a href="mailto:<?php echo esc_attr($correo); ?>"><?php echo esc_html($correo); ?></a>
                </p>
            </section>

            <section class="flacso-contacto-card flacso-contacto-card--form">
                <h2 class="flacso-contacto-card-title">Formulario de consulta</h2>
                <?php
                if (function_exists('fc_render_form')) {
                    echo fc_render_form(array('titulo' => '', 'descripcion' => ''));
                } else {
                    echo '<p class="flacso-contacto-error">El formulario de consulta no está disponible en este momento.</p>';
                }
                ?>
            </section>

        </div>

        <?php if ($embed_url) : ?>
            <section class="flacso-mapa-contacto" aria-label="Ubicación">
                <div class="fmc-card">
                    <header class="fmc-head">
                        <div>
                            <h2 class="fmc-title">Ubicación</h2>
                            <p class="fmc-label">FLACSO Uruguay</p>
                        </div>
                        <span class="fmc-badge">FLACSO Uruguay</span>
                    </header>

                    <div class="fmc-body">
                        <div class="fmc-panel">
                            <div class="fmc-panel-card">
                                <p class="fmc-panel-kicker">Dirección</p>
                                <p class="fmc-address"><?php echo esc_html($direccion); ?></p>

                                <div class="fmc-actions">
                                    <?php if ($maps_url) : ?>
                                        <a class="fmc-btn fmc-btn-primary" href="<?php echo esc_url($maps_url); ?>" target="_blank" rel="noopener noreferrer">
                                            Abrir en Google Maps
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($agenda_url) : ?>
                                        <a class="fmc-btn fmc-btn-secondary" href="<?php echo esc_url($agenda_url); ?>" target="_blank" rel="noopener noreferrer">
                                            Agenda web
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="fmc-map-wrap">
                            <iframe
                                class="fmc-map"
                                title="Ubicación de FLACSO Uruguay"
                                src="<?php echo esc_url($embed_url); ?>"
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($contactos)) : ?>
            <section class="flacso-otros-contactos" aria-label="Otros contactos">
                <div class="foc-shell">
                    <header class="foc-header">
                        <h2 class="foc-title">Otros contactos</h2>
                    </header>

                    <div class="foc-grid">
                        <?php foreach ($contactos as $item) : ?>
                            <article class="foc-card">
                                <h3 class="foc-area"><?php echo esc_html($item['area']); ?></h3>
                                <?php if (!empty($item['persona'])) : ?>
                                    <p class="foc-persona"><?php echo esc_html($item['persona']); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($item['email'])) : ?>
                                    <a class="foc-email" href="mailto:<?php echo esc_attr($item['email']); ?>">
                                        <?php echo esc_html($item['email']); ?>
                                    </a>
                                <?php else : ?>
                                    <span class="foc-email foc-email--empty">Correo no disponible</span>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

    </div>
</main>

<style>
    .flacso-page-contacto {
        --flacso-blue: #1d3a72;
        --flacso-blue-dark: #0f1a2d;
        --flacso-yellow: #fed222;
        --flacso-text: #1f2937;
        --flacso-muted: #64748b;
        --flacso-border: #e2e8f0;
        --flacso-bg: #f8fafc;

        background: var(--flacso-bg);
        color: var(--flacso-text);
        padding: 0 0 4.5rem;
    }

    .flacso-page-container {
        width: min(100% - 2rem, 1080px);
        margin: 0 auto;
    }

    .flacso-breadcrumb {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.45rem;
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.7);
    }

    .flacso-breadcrumb a {
        color: #ffffff;
        text-decoration: none;
        font-weight: 500;
    }

    .flacso-breadcrumb a:hover {
        text-decoration: underline;
    }

    .flacso-breadcrumb span {
        color: rgba(255, 255, 255, 0.6);
    }

    .flacso-page-hero {
        background:
            radial-gradient(circle at top right, rgba(254, 210, 34, 0.16), transparent 34%),
            linear-gradient(135deg, var(--flacso-blue-dark), var(--flacso-blue));
        color: #ffffff;
        border-radius: 22px;
        padding: clamp(2rem, 4vw, 3.25rem);
        margin-bottom: 2rem;
        box-shadow: 0 18px 45px rgba(15, 26, 45, 0.14);
    }

    .flacso-page-hero .flacso-breadcrumb {
        padding: 0 0 1rem;
    }

    .flacso-page-hero-title {
        color: #ffffff;
        font-size: clamp(2rem, 4.2vw, 3.4rem);
        line-height: 1.08;
        letter-spacing: -0.035em;
        margin: 0;
        font-weight: 800;
    }

    .flacso-page-hero-desc {
        margin-top: 1rem;
        font-size: 1.1rem;
        color: rgba(255, 255, 255, 0.85);
        line-height: 1.6;
        max-width: 680px;
    }

    .flacso-contacto-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.25rem;
        margin-bottom: 2rem;
    }

    .flacso-contacto-card {
        background: #ffffff;
        border: 1px solid var(--flacso-border);
        border-radius: 22px;
        padding: 1.75rem;
        box-shadow: 0 8px 28px rgba(15, 26, 45, 0.05);
    }

    .flacso-contacto-card--form {
        overflow: visible;
    }

    .flacso-contacto-card-title {
        color: var(--flacso-blue);
        font-size: 1.25rem;
        font-weight: 800;
        margin: 0 0 1.25rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid var(--flacso-yellow);
    }

    .flacso-contacto-item {
        margin: 0.75rem 0;
        font-size: 1.05rem;
        line-height: 1.6;
    }

    .flacso-contacto-item strong {
        color: var(--flacso-blue-dark);
        min-width: 80px;
        display: inline-block;
    }

    .flacso-contacto-item a {
        color: var(--flacso-blue);
        font-weight: 600;
        text-decoration: none;
    }

    .flacso-contacto-item a:hover {
        text-decoration: underline;
    }

    .flacso-contacto-error {
        color: var(--flacso-muted);
        font-style: italic;
    }

    /* ── Map block styles (replica) ── */
    .flacso-mapa-contacto {
        --fmc-primary: var(--flacso-blue);
        --fmc-accent: var(--flacso-yellow);
        --fmc-ink: var(--flacso-blue-dark);
        --fmc-muted: var(--flacso-muted);
        --fmc-border: rgba(29, 58, 114, 0.12);
        --fmc-shadow: 0 18px 42px rgba(13, 30, 60, 0.08);
        --fmc-radius-xl: clamp(18px, 4vw, 32px);
        --fmc-radius-lg: 24px;
        margin-bottom: 2rem;
    }

    .flacso-mapa-contacto .fmc-card {
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.92), rgba(255, 255, 255, 0.84));
        border: 1px solid rgba(255, 255, 255, 0.75);
        border-radius: var(--fmc-radius-xl);
        box-shadow: var(--fmc-shadow);
        overflow: hidden;
    }

    .flacso-mapa-contacto .fmc-head {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 1rem;
        align-items: start;
        padding: clamp(1.1rem, 2vw, 1.4rem) clamp(1.1rem, 2vw, 1.5rem);
        border-bottom: 1px solid var(--fmc-border);
        background: radial-gradient(circle at top right, rgba(254, 210, 34, 0.18), transparent 28%),
            linear-gradient(180deg, rgba(29, 58, 114, 0.04), rgba(29, 58, 114, 0));
    }

    .flacso-mapa-contacto .fmc-title {
        margin: 0;
        color: var(--fmc-ink);
        font-size: clamp(1.25rem, 2vw, 1.6rem);
        line-height: 1.15;
        font-weight: 700;
    }

    .flacso-mapa-contacto .fmc-label {
        margin: 0.3rem 0 0;
        color: var(--fmc-muted);
        font-size: 0.95rem;
        line-height: 1.5;
    }

    .flacso-mapa-contacto .fmc-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 0.8rem;
        border-radius: 999px;
        background: rgba(29, 58, 114, 0.08);
        color: var(--fmc-primary);
        font-size: 0.76rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .flacso-mapa-contacto .fmc-body {
        display: grid;
        grid-template-columns: minmax(260px, 0.9fr) minmax(0, 1.1fr);
    }

    .flacso-mapa-contacto .fmc-panel {
        padding: clamp(1.1rem, 2vw, 1.5rem);
        background: linear-gradient(135deg, rgba(29, 58, 114, 0.04) 0%, rgba(255, 255, 255, 0.9) 100%);
        border-right: 1px solid var(--fmc-border);
    }

    .flacso-mapa-contacto .fmc-panel-card {
        background: rgba(255, 255, 255, 0.76);
        border: 1px solid var(--fmc-border);
        border-radius: var(--fmc-radius-lg);
        padding: 1rem;
    }

    .flacso-mapa-contacto .fmc-panel-kicker {
        margin: 0;
        color: var(--fmc-muted);
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
    }

    .flacso-mapa-contacto .fmc-address {
        margin: 0.55rem 0 0;
        color: var(--fmc-ink);
        font-size: 1rem;
        line-height: 1.65;
        font-weight: 500;
    }

    .flacso-mapa-contacto .fmc-actions {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        margin-top: 1rem;
    }

    .flacso-mapa-contacto .fmc-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 46px;
        padding: 0.85rem 1.1rem;
        border-radius: 999px;
        font-size: 0.95rem;
        font-weight: 700;
        text-decoration: none;
        transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease, border-color 0.18s ease;
    }

    .flacso-mapa-contacto .fmc-btn:hover {
        transform: translateY(-2px);
    }

    .flacso-mapa-contacto .fmc-btn-primary {
        background: var(--fmc-accent);
        color: var(--fmc-ink);
    }

    .flacso-mapa-contacto .fmc-btn-secondary {
        background: #fff;
        color: var(--fmc-primary);
        border: 1px solid rgba(29, 58, 114, 0.22);
    }

    .flacso-mapa-contacto .fmc-map-wrap {
        position: relative;
        min-height: 320px;
        background: linear-gradient(135deg, rgba(29, 58, 114, 0.08) 0%, rgba(255, 255, 255, 0.92) 50%, rgba(254, 210, 34, 0.16) 100%);
    }

    .flacso-mapa-contacto .fmc-map {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        border: 0;
    }

    /* ── Otros contactos block styles (replica) ── */
    .flacso-otros-contactos {
        --foc-primary: var(--flacso-blue);
        --foc-ink: var(--flacso-blue-dark);
        --foc-muted: var(--flacso-muted);
        --foc-bg: #e9edf2;
        --foc-surface: #ffffff;
        --foc-border: rgba(29, 58, 114, 0.12);
        --foc-radius-xl: clamp(18px, 4vw, 32px);
        --foc-radius-lg: 22px;

        background: linear-gradient(180deg, rgba(233, 237, 242, 0.95) 0%, rgba(235, 240, 247, 0.95) 100%);
        border-radius: var(--foc-radius-xl);
        border: 1px solid var(--foc-border);
        padding: clamp(1.2rem, 2.8vw, 2rem);
        overflow: hidden;
        box-shadow: 0 20px 40px -32px rgba(16, 36, 78, 0.4);
    }

    .flacso-otros-contactos .foc-shell {
        width: 100%;
        margin: 0 auto;
    }

    .flacso-otros-contactos .foc-header {
        margin: 0 0 1.35rem;
        text-align: center;
    }

    .flacso-otros-contactos .foc-title {
        margin: 0;
        color: var(--foc-ink);
        font-size: clamp(1.7rem, 2.8vw, 2.4rem);
        line-height: 1.15;
        font-weight: 700;
        letter-spacing: 0.01em;
    }

    .flacso-otros-contactos .foc-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.95rem;
    }

    .flacso-otros-contactos .foc-card {
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid rgba(29, 58, 114, 0.12);
        border-radius: var(--foc-radius-lg);
        padding: 1rem 1rem 1.05rem;
        box-shadow: 0 14px 30px -24px rgba(15, 23, 42, 0.3);
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .flacso-otros-contactos .foc-card:hover,
    .flacso-otros-contactos .foc-card:focus-within {
        transform: translateY(-2px);
        box-shadow: 0 18px 34px -24px rgba(15, 23, 42, 0.38);
    }

    .flacso-otros-contactos .foc-area {
        margin: 0;
        color: var(--foc-ink);
        font-size: 1rem;
        line-height: 1.4;
        font-weight: 700;
    }

    .flacso-otros-contactos .foc-persona {
        margin: 0.55rem 0 0;
        color: rgba(15, 26, 45, 0.78);
        font-size: 0.95rem;
        line-height: 1.5;
    }

    .flacso-otros-contactos .foc-email {
        display: inline-block;
        margin-top: 0.7rem;
        color: var(--foc-primary);
        font-size: 0.94rem;
        font-weight: 700;
        text-decoration: none;
        word-break: break-word;
    }

    .flacso-otros-contactos .foc-email:hover,
    .flacso-otros-contactos .foc-email:focus-visible {
        text-decoration: underline;
    }

    .flacso-otros-contactos .foc-email--empty {
        color: var(--foc-muted);
        font-weight: 600;
    }

    @media (max-width: 860px) {
        .flacso-page-container {
            width: min(100% - 1.25rem, 1080px);
        }

        .flacso-page-hero {
            border-radius: 18px;
        }

        .flacso-page-hero-title {
            font-size: clamp(1.85rem, 9vw, 2.6rem);
        }

        .flacso-contacto-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767px) {
        .flacso-mapa-contacto .fmc-head {
            grid-template-columns: 1fr;
        }

        .flacso-mapa-contacto .fmc-body {
            grid-template-columns: 1fr;
        }

        .flacso-mapa-contacto .fmc-panel {
            border-right: 0;
            border-bottom: 1px solid var(--fmc-border);
        }

        .flacso-mapa-contacto .fmc-actions {
            flex-direction: row;
            flex-wrap: wrap;
        }

        .flacso-mapa-contacto .fmc-actions .fmc-btn {
            flex: 1 1 220px;
        }

        .flacso-mapa-contacto .fmc-map-wrap {
            min-height: 380px;
        }

        .flacso-otros-contactos .foc-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .flacso-mapa-contacto .fmc-actions {
            flex-direction: column;
        }

        .flacso-mapa-contacto .fmc-actions .fmc-btn {
            width: 100%;
            flex: initial;
        }

        .flacso-mapa-contacto .fmc-map-wrap {
            min-height: 300px;
        }
    }
</style>

<?php
get_footer();
