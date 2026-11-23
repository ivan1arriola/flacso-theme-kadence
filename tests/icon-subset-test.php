<?php
/**
 * Todo `bi-*` usado en el tema (y en el plugin hermano, si esta al lado) existe en el CSS subconjunto,
 * y el tema registra el subconjunto con el handle `bootstrap-icons`.
 * Ejecutable sin WordPress: php tests/icon-subset-test.php
 * Si falla por un icono nuevo: python bin/build-icon-subset.py
 */
$root = dirname(__DIR__);
function ics_check($ok, $d) { if (!$ok) { fwrite(STDERR, "FAIL: " . $d . "\n"); exit(1); } }

$assets = (string) file_get_contents($root . '/inc/assets.php');
ics_check(strpos($assets, "'bootstrap-icons',") !== false, "el handle 'bootstrap-icons' sigue registrado");
ics_check(strpos($assets, 'bootstrap-icons-subset.min.css') !== false, 'inc/assets.php apunta al CSS subconjunto');
$css_file = $root . '/assets/vendor/bootstrap-icons/font/bootstrap-icons-subset.min.css';
ics_check(is_file($css_file), 'existe el CSS subconjunto');
ics_check(is_file($root . '/assets/vendor/bootstrap-icons/font/fonts/bootstrap-icons-subset.woff2'), 'existe la fuente subconjunto');
$css = (string) file_get_contents($css_file);
ics_check(strpos($css, 'bootstrap-icons-subset.woff2') !== false, 'el CSS referencia la fuente subconjunto');

$bases = [$root];
$plugin = dirname($root) . '/flacso-uruguay-plugin';
if (is_dir($plugin)) { $bases[] = $plugin; }
$skip = '#/(vendor|node_modules|\.worktrees|dev|tests|\.git)/#';
$missing = [];
foreach ($bases as $base) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) {
        $p = str_replace(DIRECTORY_SEPARATOR, '/', $f->getPathname());
        $rel = substr($p, strlen(str_replace(DIRECTORY_SEPARATOR, '/', $base)));
        if (!preg_match('/\.(php|js|css|html)$/', $p) || preg_match($skip, $rel) || strpos($p, '.min.') !== false || strpos($p, '-subset') !== false) { continue; }
        if (preg_match_all('/(?<![\w-])bi-([a-z0-9]+(?:-[a-z0-9]+)*)(?!-)/', (string) file_get_contents($p), $m)) {
            foreach ($m[1] as $name) {
                if (strpos($css, '.bi-' . $name . '::before') === false) { $missing[$name] = $rel; }
            }
        }
    }
}
ics_check($missing === [], 'iconos usados que faltan en el subconjunto: ' . json_encode($missing));
echo "OK iconos usados incluidos en el subconjunto\n";
