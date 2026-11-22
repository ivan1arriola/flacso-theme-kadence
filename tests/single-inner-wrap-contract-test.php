<?php
/**
 * La página debe tener un solo #inner-wrap (lo abre header.php, lo cierra footer.php).
 * El enlace "Saltar al contenido" apunta a ese id, y un id repetido vuelve ambiguo el destino.
 * Ejecutable sin WordPress: php tests/single-inner-wrap-contract-test.php
 */
$root = dirname(__DIR__);

function inner_wrap_check($ok, $description) {
    if (!$ok) { fwrite(STDERR, "FAIL: " . $description . "\n"); exit(1); }
}

$header = (string) file_get_contents($root . '/header.php');
$oferta = (string) file_get_contents($root . '/oferta-academica/page.php');

inner_wrap_check(substr_count($header, 'id="inner-wrap"') === 1, 'header.php abre un único #inner-wrap');
inner_wrap_check(strpos($header, 'href="#inner-wrap"') !== false, 'el salto al contenido apunta a #inner-wrap');
inner_wrap_check(strpos($oferta, 'id="inner-wrap"') === false, 'la ficha de oferta no repite #inner-wrap: ya lo abre header.php');
inner_wrap_check(strpos($oferta, 'flacso-oferta-responsive') !== false, 'la ficha conserva la clase que usan su CSS y su JS');

foreach (['/oferta-academica/style.css', '/oferta-academica/script.js'] as $asset) {
    inner_wrap_check(strpos((string) file_get_contents($root . $asset), '#inner-wrap') === false, basename($asset) . ' no depende del id #inner-wrap');
}

echo "OK un solo #inner-wrap\n";
