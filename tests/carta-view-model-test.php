<?php
/** Modelo de vista de la carta: textos del plugin, reglas, contacto y color. */

define('ABSPATH', __DIR__ . '/../');

$GLOBALS['opts'] = [];
$GLOBALS['posts'] = [
    77 => ['type' => 'docente', 'title' => 'Ana Pérez', 'thumb' => 'https://x.test/ana.jpg', 'meta' => ['prefijo_abrev' => ['Dra.'], 'correo' => ['ana@flacso.edu.uy']]],
];

function __($s, $d = '') { return $s; }
function esc_attr($s) { return (string) $s; }
function sanitize_text_field($s) { return trim(strip_tags((string) $s)); }
function sanitize_email($s) { return (string) $s; }
function wp_kses_post($s) { return (string) $s; }
function wp_strip_all_tags($s) { return trim(strip_tags((string) $s)); }
function wp_trim_words($t, $n = 55, $m = '…') { return $t; }
function wp_date($f) { return '2027'; }
function absint($v) { return abs((int) $v); }
function get_option($n, $d = false) { return array_key_exists($n, $GLOBALS['opts']) ? $GLOBALS['opts'][$n] : $d; }
function get_post($id) { $p = $GLOBALS['posts'][$id] ?? null; return $p ? (object) ['ID' => $id, 'post_type' => $p['type']] : null; }
function get_post_meta($id, $k = '', $s = false) { return $GLOBALS['posts'][$id]['meta'] ?? []; }
function get_the_title($id) { return $GLOBALS['posts'][$id]['title'] ?? ''; }
function get_the_post_thumbnail_url($id, $size = '') { return $GLOBALS['posts'][$id]['thumb'] ?? false; }
function flacso_theme_calendar_and_curriculum_share_document($a, $b) { return $a !== '' && $a === $b; }

$plugin_root = realpath(__DIR__ . '/../../flacso-uruguay-plugin');
if (is_string($plugin_root) && $plugin_root !== '') {
    $carta_settings_candidates = [
        $plugin_root . '/modules/oferta-academica/includes/class-carta-settings.php',
        $plugin_root . '/includes/class-carta-settings.php',
    ];
    foreach ($carta_settings_candidates as $candidate) {
        if (is_file($candidate)) {
            require_once $candidate;
        }
        if (class_exists('FLACSO_Carta_Settings')) {
            break;
        }
    }
    if (!class_exists('FLACSO_Carta_Settings')) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($plugin_root, FilesystemIterator::SKIP_DOTS)
        );
        foreach ($iterator as $entry) {
            if ($entry instanceof SplFileInfo && $entry->isFile() && $entry->getFilename() === 'class-carta-settings.php') {
                require_once $entry->getPathname();
                if (class_exists('FLACSO_Carta_Settings')) {
                    break;
                }
            }
        }
    }
}
require_once __DIR__ . '/../inc/carta-view-model.php';

function carta_view_assert(bool $condition, string $message): void {
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

// Textos del plugin con el año de la cohorte.
$texts_default = flacso_theme_carta_texts('2027', []);
carta_view_assert(strpos($texts_default['pago_pesos'], 'durante todo 2027,') !== false, 'el marcador {anio} se reemplaza por el año de la cohorte');
carta_view_assert($texts_default['telefono_href'] === 'tel:+59824817459', 'el enlace telefónico se arma con los dígitos del teléfono');
carta_view_assert($texts_default['correo_inscripciones'] === 'inscripciones@flacso.edu.uy', 'correo institucional por defecto');

$GLOBALS['opts']['flacso_carta_vencimiento_cuotas'] = '';
$GLOBALS['opts']['flacso_carta_telefono'] = '';
$texts = flacso_theme_carta_texts('2027', []);
carta_view_assert($texts['vencimiento_cuotas'] === '', 'un texto guardado vacío no se muestra');
carta_view_assert($texts['telefono'] === '' && $texts['telefono_href'] === '', 'sin teléfono no hay enlace');
$GLOBALS['opts'] = [];

// Reglas: los textos ya vienen resueltos según la oferta.
$con_apostilla = flacso_theme_carta_texts('2027', ['reconocimiento_internacional' => true, 'reconocido_mec' => true]);
carta_view_assert($con_apostilla['titulo_exterior'] !== '' && $con_apostilla['titulo_sin_costo'] === '', 'con Apostillado: título con costo, no el de sin costo');
carta_view_assert($con_apostilla['badge_apostilla'] !== '' && $con_apostilla['badge_mec'] !== '' && $con_apostilla['badge_iin'] === '', 'los distintivos siguen las marcas de la oferta');
carta_view_assert(strpos($con_apostilla['titulo_exterior'], 'envío') === false, 'el título con costo no menciona el envío');
carta_view_assert($texts_default['titulo_exterior'] === '' && strpos($texts_default['titulo_sin_costo'], 'no tiene costo adicional') !== false, 'sin Apostillado: título sin costo');
carta_view_assert($con_apostilla['envio_titulos'] !== '' && $texts_default['envio_titulos'] === $con_apostilla['envio_titulos'], 'el envío es común a todas las cartas');

// Contacto: persona elegida, con su rol y su correo.
$contact = flacso_theme_carta_contact([
    'asistente_academica_docente_id' => 77,
    'asistente_academica_rol' => 'Asistencia Técnica',
    'asistente_academica_correo' => 'tecnica@flacso.edu.uy',
], $texts_default);
carta_view_assert($contact['nombre'] === 'Dra. Ana Pérez', 'usa el docente seleccionado con su prefijo');
carta_view_assert($contact['rol'] === 'Asistencia Técnica', 'usa el rol del integrante');
carta_view_assert($contact['correo'] === 'tecnica@flacso.edu.uy', 'usa el correo de contacto del integrante');
carta_view_assert($contact['foto_url'] === 'https://x.test/ana.jpg', 'usa la foto del docente');

$contact = flacso_theme_carta_contact(['asistente_academica_docente_id' => 77], $texts_default);
carta_view_assert($contact['correo'] === 'ana@flacso.edu.uy', 'sin correo propio usa el de la persona');
carta_view_assert($contact['rol'] === 'Asistente Académica', 'sin rol usa el rol por defecto');

// Sin contacto elegido: no hay personas escritas en el código, solo el contacto institucional editable.
$contact = flacso_theme_carta_contact([], $texts_default);
carta_view_assert($contact['nombre'] === 'Inscripciones FLACSO Uruguay', 'sin contacto se usa el institucional');
carta_view_assert($contact['rol'] === 'Asistencia Académica' && $contact['correo'] === 'inscripciones@flacso.edu.uy', 'rol y correo institucionales');

$GLOBALS['opts']['flacso_carta_contacto_nombre'] = 'Posgrados';
$contact = flacso_theme_carta_contact([], flacso_theme_carta_texts('2027', []));
carta_view_assert($contact['nombre'] === 'Posgrados', 'el contacto de respaldo sale de los ajustes');
$GLOBALS['opts'] = [];

$source = file_get_contents(__DIR__ . '/../inc/carta-view-model.php');
carta_view_assert(strpos($source, 'Bombau') === false && strpos($source, 'Quartino') === false, 'no quedan contactos de respaldo escritos por oferta');

// Marca de color.
$styles = flacso_theme_carta_brand_styles(['color_principal' => '#7a1fa2'], [], 1);
carta_view_assert(strpos($styles, '--fc-brand: #7a1fa2;') !== false && strpos($styles, '--fc-brand-contrast: #ffffff;') !== false, 'el color de la oferta genera sus variables');
$styles = flacso_theme_carta_brand_styles(['color_principal' => 'rojo'], [], 1);
carta_view_assert(strpos($styles, '--fc-brand: #16396f;') !== false, 'un color inválido usa el azul institucional');

echo "OK carta view model\n";
