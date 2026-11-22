<?php
/**
 * La imagen del encabezado de Seminarios es una fotografía guardada como PNG (1080x1080, 1,9 MB). Se mostraba con un
 * <img src> sin srcset del tamaño medium_large (897 KB), a 224x280 px en un teléfono y 576x720 px en escritorio, siempre
 * recortada a 4:5. Debe pasar por wp_get_attachment_image con el tamaño 4:5 del tema y sizes según el ancho real, para que
 * reciba srcset y, cuando existan, los .webp de tarjeta.
 * Ejecutable sin WordPress: php tests/seminarios-hero-image-test.php
 */
$root = dirname(__DIR__);

function hero_check($ok, $description) {
    if (!$ok) { fwrite(STDERR, "FAIL: " . $description . "\n"); exit(1); }
}

$php = str_replace("\r\n", "\n", (string) file_get_contents($root . '/seminarios-listado.php'));

hero_check(strpos($php, "'https://flacso.edu.uy/wp-content/uploads/2026/02/seminarios-artwork.png'") === false, 'la URL del encabezado no está fija al dominio de producción');
hero_check(strpos($php, 'wp_get_upload_dir()') !== false, 'la URL del encabezado se deriva de la carpeta de subidas del sitio');
hero_check(strpos($php, '2026/02/seminarios-artwork.png') !== false, 'sigue apuntando a seminarios-artwork.png');

// El bloque del visual usa el tamaño 4:5 con srcset (wp_get_attachment_image) y sizes medidos.
hero_check(preg_match('/seminarios-hero__visual[\s\S]{0,900}?wp_get_attachment_image\(\s*\(int\) \$hero_image_id,\s*\'flacso-seminario-4x5\'/', $php) === 1, 'el encabezado usa wp_get_attachment_image con flacso-seminario-4x5');
hero_check(strpos($php, "'(max-width: 600px) 55vw, (max-width: 900px) 96vw, (max-width: 1100px) 550px, 576px'") !== false, 'sizes refleja el ancho real medido (224/412, 736/768, 550/1024 y 576 px)');
hero_check(strpos($php, "'fetchpriority' => 'high'") !== false, 'es la imagen principal de la página: fetchpriority alto');
hero_check(strpos($php, "'class'         => 'seminarios-hero__image'") !== false || strpos($php, "'class' => 'seminarios-hero__image'") !== false, 'conserva la clase seminarios-hero__image');
hero_check(strpos($php, "'alt'           => ''") !== false || strpos($php, "'alt' => ''") !== false, 'sigue siendo decorativa (alt vacío)');

// Respaldo: sin adjunto sigue existiendo el <img src> con la URL.
hero_check(strpos($php, 'class="seminarios-hero__image"') !== false, 'queda el <img> de respaldo cuando no hay adjunto');

echo "OK imagen del encabezado de Seminarios\n";
