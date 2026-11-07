# Portada según maqueta "Laboratorio de portada" (7/11/2026)

## Objetivo
Reemplazar la portada actual (`inc/home-v2.php` y el builder del plugin) por la maqueta aprobada
(`flacso-portada-vscode.zip`: hero de la variante B + estructura editorial de la A), con datos reales de WordPress.

## Decisiones acordadas
- La maqueta reemplaza la portada actual; se retiran `home-v2`, el builder y el CSS que queden sin uso.
- Hero, campaña, Investigación y "¿Qué es FLACSO Uruguay?" son editables desde el admin.
- Se mantienen header y footer de Kadence; solo cambia el contenido de `<main>`.

## Arquitectura
1. **Plugin** (`flacso-uruguay-plugin/modules/main-page`): pantalla de ajustes "Portada" con
   - hero: título, énfasis, bajada, texto y enlace del botón;
   - campaña: interruptor, etiqueta, título, texto, texto y enlace del CTA;
   - Investigación: título, bajada, 3 enlaces numerados;
   - "¿Qué es FLACSO Uruguay?": dos párrafos con negritas permitidas (`wp_kses`).
   Valores por defecto = textos de la maqueta (texto institucional aprobado precargado).
2. **Tema**: `front-page.php` con una sola ruta. Template-parts: hero, oferta, campaña, agenda, noticias,
   investigación, sobre. Leen los ajustes y la capa de datos existente (eventos, noticias fijadas y recientes).
3. **CSS**: un único `assets/css/front-page.css` derivado de `home.css` de la maqueta, sin reglas de variantes,
   topbar ni header propios. Roboto local (licencia OFL) y marcos 4:5 sin recorte.

## Fuera de alcance
Comparador de variantes, favorita/notas, búsqueda local de la maqueta (el botón usa la búsqueda de WordPress/Kadence).

## Comportamiento dinámico
- Agenda: próxima actividad; se oculta si no hay.
- Campaña: se oculta con el interruptor.
- Noticias: sin fijadas, solo recientes. Imagen = destacada de la entrada.
- Los 5 niveles de oferta mantienen el orden existente.

## Pruebas
Tests PHP de ajustes (defaults, sanitización) y de ocultación de agenda/campaña; revisión visual a
320/390/768/1440 px contra la maqueta sin desborde horizontal; `bin/check-theme.sh`.

## Entrega
Plugin: commit directo a main. Tema: rama `feat/portada-maqueta-20261107`.
