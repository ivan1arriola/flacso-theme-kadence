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

$telefono  = get_theme_mod('flacso_contacto_telefono', '2481 7459');
$correo    = get_theme_mod('flacso_contacto_email', 'secretaria@flacso.edu.uy');
$direccion = get_theme_mod('flacso_contacto_direccion', '8 de Octubre 2882, CP 11600, Montevideo');
$maps_url  = get_theme_mod('flacso_contacto_maps_url', 'https://maps.google.com/?q=FLACSO+Uruguay+8+de+Octubre+2882+Montevideo');
$embed_url = get_theme_mod('flacso_contacto_embed_url', '');
$agenda_url = get_theme_mod('flacso_contacto_agenda_url', 'https://agenda.flacso.edu.uy/');

$contactos = function_exists('flacso_get_contactos') ? flacso_get_contactos() : array();

$telefono_tel = preg_replace('/\D/', '', $telefono);
?>

<<?php echo flacso_child_main_tag(); ?> class="flacso-contact-page" id="main">
    <div class="flacso-contact-shell">

        <header class="flacso-contact-hero">
            <div class="flacso-contact-hero__content">
                <nav class="flacso-contact-breadcrumb" aria-label="Navegación">
                    <a href="<?php echo esc_url(home_url('/')); ?>">Inicio</a>
                    <span aria-hidden="true">/</span>
                    <span>Contacto</span>
                </nav>

                <p class="flacso-contact-eyebrow">FLACSO Uruguay</p>
                <h1 class="flacso-contact-title">Contacto</h1>
                <p class="flacso-contact-lead">
                    Comunicate con Secretaría, enviá tu consulta o encontrá los datos de referencia de nuestras áreas.
                </p>
            </div>

            <aside class="flacso-contact-hero__panel" aria-label="Datos principales de contacto">
                <div class="flacso-contact-quick">
                    <span class="flacso-contact-quick__label">Secretaría</span>

                    <a class="flacso-contact-quick__link" href="mailto:<?php echo esc_attr($correo); ?>">
                        <?php echo esc_html($correo); ?>
                    </a>

                    <?php if ($telefono_tel) : ?>
                        <a class="flacso-contact-quick__link" href="tel:<?php echo esc_attr($telefono_tel); ?>">
                            <?php echo esc_html($telefono); ?>
                        </a>
                    <?php else : ?>
                        <span class="flacso-contact-quick__link"><?php echo esc_html($telefono); ?></span>
                    <?php endif; ?>

                    <span class="flacso-contact-quick__address">
                        <?php echo esc_html($direccion); ?>
                    </span>
                </div>
            </aside>
        </header>

        <section class="flacso-contact-main" aria-label="Formulario y datos de contacto">
            <div class="flacso-contact-form-card">
                <div class="flacso-contact-section-head">
                    <p class="flacso-contact-section-kicker">Consultas</p>
                    <h2>Formulario de consulta</h2>
                    <p>
                        Completá tus datos y el equipo de FLACSO Uruguay responderá tu consulta.
                    </p>
                </div>

                <div class="flacso-contact-form-wrap">
                    <?php
                    if (function_exists('fc_render_form')) {
                        echo fc_render_form(array('titulo' => '', 'descripcion' => '', 'mostrar_encabezado' => false));
                    } else {
                        echo '<p class="flacso-contact-error">El formulario de consulta no está disponible en este momento.</p>';
                    }
                    ?>
                </div>
            </div>

            <aside class="flacso-contact-info-card" aria-label="Datos de Secretaría">
                <div class="flacso-contact-info-card__inner">
                    <p class="flacso-contact-section-kicker">Secretaría</p>
                    <h2>Datos de contacto</h2>

                    <div class="flacso-contact-data-list">
                        <div class="flacso-contact-data">
                            <span class="flacso-contact-data__label">Correo</span>
                            <a href="mailto:<?php echo esc_attr($correo); ?>">
                                <?php echo esc_html($correo); ?>
                            </a>
                        </div>

                        <div class="flacso-contact-data">
                            <span class="flacso-contact-data__label">Teléfono</span>
                            <?php if ($telefono_tel) : ?>
                                <a href="tel:<?php echo esc_attr($telefono_tel); ?>">
                                    <?php echo esc_html($telefono); ?>
                                </a>
                            <?php else : ?>
                                <span><?php echo esc_html($telefono); ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="flacso-contact-data">
                            <span class="flacso-contact-data__label">Dirección</span>
                            <span><?php echo esc_html($direccion); ?></span>
                        </div>
                    </div>

                    <div class="flacso-contact-actions">
                        <?php if ($maps_url) : ?>
                            <a class="flacso-contact-btn flacso-contact-btn--primary" href="<?php echo esc_url($maps_url); ?>" target="_blank" rel="noopener noreferrer">
                                Abrir en Google Maps
                            </a>
                        <?php endif; ?>

                        <?php if ($agenda_url) : ?>
                            <a class="flacso-contact-btn flacso-contact-btn--secondary" href="<?php echo esc_url($agenda_url); ?>" target="_blank" rel="noopener noreferrer">
                                Agenda web
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </aside>
        </section>

        <?php if ($embed_url) : ?>
            <section class="flacso-contact-map-section" aria-label="Ubicación de FLACSO Uruguay">
                <div class="flacso-contact-map-head">
                    <div>
                        <p class="flacso-contact-section-kicker">Ubicación</p>
                        <h2>Visitá FLACSO Uruguay</h2>
                        <p><?php echo esc_html($direccion); ?></p>
                    </div>

                    <?php if ($maps_url) : ?>
                        <a class="flacso-contact-map-link" href="<?php echo esc_url($maps_url); ?>" target="_blank" rel="noopener noreferrer">
                            Ver recorrido
                        </a>
                    <?php endif; ?>
                </div>

                <div class="flacso-contact-map-frame">
                    <iframe
                        title="Ubicación de FLACSO Uruguay"
                        src="<?php echo esc_url($embed_url); ?>"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($contactos)) : ?>
            <section class="flacso-contact-directory" aria-label="Otros contactos">
                <div class="flacso-contact-directory-head">
                    <p class="flacso-contact-section-kicker">Áreas</p>
                    <h2>Otros contactos</h2>
                    <p>Referencias específicas para consultas académicas, institucionales y administrativas.</p>
                </div>

                <div class="flacso-contact-directory-grid">
                    <?php foreach ($contactos as $item) : ?>
                        <?php
                        $area    = isset($item['area']) ? trim((string) $item['area']) : '';
                        $persona = isset($item['persona']) ? trim((string) $item['persona']) : '';
                        $email   = isset($item['email']) ? sanitize_email($item['email']) : '';
                        ?>
                        <article class="flacso-contact-directory-card">
                            <?php if ($area) : ?>
                                <h3><?php echo esc_html($area); ?></h3>
                            <?php endif; ?>

                            <?php if ($persona) : ?>
                                <p class="flacso-contact-directory-person">
                                    <?php echo esc_html($persona); ?>
                                </p>
                            <?php endif; ?>

                            <?php if ($email) : ?>
                                <a class="flacso-contact-directory-email" href="mailto:<?php echo esc_attr($email); ?>">
                                    <?php echo esc_html($email); ?>
                                </a>
                            <?php else : ?>
                                <span class="flacso-contact-directory-empty">Correo no disponible</span>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

    </div>
</<?php echo flacso_child_main_tag(); ?>>


<?php
get_footer();