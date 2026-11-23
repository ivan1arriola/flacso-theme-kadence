<?php
/**
 * El tema ya no registra ni encola el JavaScript de Bootstrap: ninguna plantilla usa data-bs-* ni sus componentes.
 * Ejecutable sin WordPress: php tests/bootstrap-js-removed-test.php
 */
$root = dirname(__DIR__);
function bsj_check($ok, $d) { if (!$ok) { fwrite(STDERR, "FAIL: " . $d . "\n"); exit(1); } }

$assets = (string) file_get_contents($root . '/inc/assets.php');
bsj_check(strpos($assets, "'bootstrap-js'") === false && strpos($assets, 'bootstrap.bundle') === false, 'inc/assets.php no registra ni encola bootstrap-js');
bsj_check(strpos($assets, "wp_enqueue_style( 'bootstrap-css' )") !== false, 'el CSS de Bootstrap sigue (se retira en otra etapa)');
bsj_check(!is_dir($root . '/assets/vendor/bootstrap/js'), 'el archivo bootstrap.bundle.min.js ya no está en el tema');

$hits = [];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
foreach ($it as $f) {
    $p = str_replace(DIRECTORY_SEPARATOR, '/', $f->getPathname());
    if (!preg_match('/\.(php|js)$/', $p) || strpos($p, '/vendor/') !== false || strpos($p, '/tests/') !== false || strpos($p, '/.git') !== false || strpos($p, '/.worktrees/') !== false || strpos($p, '/node_modules/') !== false || strpos($p, '.min.') !== false) { continue; }
    if (preg_match('/data-bs-|new bootstrap\.|bootstrap\.(Modal|Tooltip|Collapse|Dropdown|Carousel|Offcanvas|Toast)/', (string) file_get_contents($p))) { $hits[] = $p; }
}
bsj_check($hits === [], 'ningún archivo del tema usa componentes JS de Bootstrap: ' . implode(', ', $hits));
echo "OK sin JavaScript de Bootstrap en el tema\n";
