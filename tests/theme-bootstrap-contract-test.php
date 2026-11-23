<?php
$root = dirname(__DIR__);

function theme_bootstrap_assert(bool $condition, string $message): void {
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$bootstrap = (string) file_get_contents($root . '/functions.php');
theme_bootstrap_assert(strpos($bootstrap, "defined( 'ABSPATH' )") !== false, 'functions.php protege el acceso directo');

$modules = array(
    'inc/theme-setup.php' => array('flacso_theme_brand_slogan', 'flacso_theme_brand_slogan_replace_legacy'),
    'inc/navigation.php' => array('flacso_child_register_navigation_locations', 'flacso_child_primary_menu_fallback', 'flacso_child_utility_menu_fallback', 'flacso_child_get_header_cta'),
    'inc/media.php' => array('flacso_child_register_seminar_image_sizes', 'flacso_child_needs_featured_images', 'flacso_child_enqueue_featured_images', 'flacso_child_version_post_thumbnail_html', 'flacso_child_flush_posts_using_attachment'),
    'inc/assets.php' => array('child_theme_configurator_css', 'flacso_child_enqueue_site_chrome', 'flacso_child_enqueue_front_page_assets', 'child_theme_needs_bootstrap', 'child_theme_register_bootstrap', 'child_theme_enqueue_bootstrap', 'flacso_child_async_bootstrap_icons', 'child_theme_enqueue_jquery_ui', 'flacso_child_asset_version', 'flacso_child_enqueue_template_assets'),
    'inc/contact.php' => array('flacso_sanitize_contactos_json', 'flacso_get_contactos'),
);

foreach ($modules as $module => $functions) {
    $path = $root . '/' . $module;
    theme_bootstrap_assert(file_exists($path), 'existe el módulo ' . $module);
    theme_bootstrap_assert(strpos($bootstrap, $module) !== false, 'functions.php carga ' . $module);
    $source = (string) file_get_contents($path);
    theme_bootstrap_assert(strpos($source, "defined( 'ABSPATH' )") !== false, $module . ' protege el acceso directo');
    foreach ($functions as $function_name) {
        theme_bootstrap_assert((bool) preg_match('/function\s+' . preg_quote($function_name, '/') . '\s*\(/', $source), $function_name . ' permanece en ' . $module);
        theme_bootstrap_assert(!preg_match('/function\s+' . preg_quote($function_name, '/') . '\s*\(/', $bootstrap), $function_name . ' no queda en functions.php');
    }
}

$assets = (string) file_get_contents($root . '/inc/assets.php');
$contact = (string) file_get_contents($root . '/inc/contact.php');
theme_bootstrap_assert(strpos($assets, "formacion") !== false && strpos($assets, '$request_path') !== false, 'se conserva la detección de la ruta virtual formacion');
theme_bootstrap_assert(strpos($contact, "^contactos/?$") !== false, 'se conserva la ruta virtual contactos');
theme_bootstrap_assert(strpos($assets, 'cdn.jsdelivr.net/npm/bootstrap') === false, 'inc/assets.php no contiene cdn.jsdelivr.net/npm/bootstrap');

$vendor_assets = array(
    'assets/vendor/bootstrap/css/bootstrap.min.css',
    'assets/vendor/bootstrap-icons/font/bootstrap-icons.min.css',
    'assets/vendor/bootstrap-icons/font/fonts/bootstrap-icons.woff2',
    'assets/vendor/bootstrap-icons/font/fonts/bootstrap-icons.woff',
);
foreach ($vendor_assets as $vendor_asset) {
    theme_bootstrap_assert(file_exists($root . '/' . $vendor_asset), 'existe el asset local ' . $vendor_asset);
}

echo "OK theme-bootstrap-contract-test\n";