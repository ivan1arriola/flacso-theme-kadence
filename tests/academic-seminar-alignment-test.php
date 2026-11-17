<?php
/** Regression: academic offers and seminars share the same content rails. */
declare(strict_types=1);

$root = dirname(__DIR__);
$offer = file_get_contents($root . '/oferta-academica/style.css');
$seminar = file_get_contents($root . '/assets/css/single-seminario.css');

function check_alignment(bool $ok, string $message): void {
    if (!$ok) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

preg_match('/\.flacso-oa-container\s*\{([^}]*)\}/s', $offer, $offer_match);
preg_match('/\.flacso-seminario-shell\s*\{([^}]*)\}/s', $seminar, $seminar_match);

check_alignment(isset($offer_match[1], $seminar_match[1]), 'ambas páginas tienen contenedor de contenido');
foreach (['width: 100%;', 'max-width: 1180px;', 'padding-left: clamp(24px, 5vw, 40px);', 'padding-right: clamp(24px, 5vw, 40px);'] as $rule) {
    check_alignment(strpos($seminar_match[1], $rule) !== false, "seminario: {$rule}");
}
// La oferta toma ancho y márgenes de dos variables; deben valer lo mismo que el contenedor del seminario.
check_alignment(strpos($offer_match[1], 'max-width: var(--oa-max);') !== false, 'oferta: max-width desde --oa-max');
check_alignment(strpos($offer_match[1], 'padding-inline: var(--oa-gutter);') !== false, 'oferta: padding desde --oa-gutter');
check_alignment(strpos($offer, '--oa-max: 1180px;') !== false, 'oferta: --oa-max = 1180px');
check_alignment(strpos($offer, '--oa-gutter: clamp(24px, 5vw, 40px);') !== false, 'oferta: --oa-gutter = clamp(24px, 5vw, 40px)');
check_alignment(strpos($seminar, 'width: min(100% - 24px, 680px);') === false, 'sin retícula móvil divergente');
check_alignment(strpos($seminar, 'width: calc(100% - 20px);') === false, 'sin retícula estrecha divergente');

echo "Ofertas y seminarios: retícula y padding lateral alineados.\n";
