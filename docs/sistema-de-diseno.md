# Sistema de diseño de FLACSO Uruguay (tema hijo de Kadence)

**Estado:** decisiones del dueño vigentes desde el 22 de noviembre de 2026. Los tokens y los componentes de este documento **todavía no están implementados**: se construyen por pasos (ver «Ejecución»). Hasta entonces, este documento es la regla para todo cambio nuevo de interfaz.

## Por qué existe

Cada plantilla se construyó por separado y por eso no comparten un estilo. Medido sobre las 25 hojas CSS propias del tema (línea base en el plugin: `dev/entorno-local/LINEA-BASE.md`):

| Dimensión | Hoy | Meta |
|---|---|---|
| Colores | 410 hex distintos; 1.102 usos fijos contra 106 de `var(--global-palette*)` (8,8 % de Kadence) | los roles de marca solo por variables de Kadence |
| Radios | 69 valores distintos | 5 tokens |
| Sombras | 197 valores distintos | 3 tokens |
| Anchos de contenedor | 10 distintos (900 a 1280 px) | 2 |
| Tamaños de H1 | 11 distintos entre plantillas (de 40 a 77 px, medidos a 1280 px) | 2 escalas |
| Breakpoints | 42 valores distintos | 5 |
| `!important` | 1.119 | menos de 300 |

## Decisiones del dueño (obligatorias)

1. **El estilo es uniforme entre plantillas y hereda de Kadence.** La paleta, la tipografía, los anchos y los botones salen de las variables de Kadence (`var(--global-*)`), editables desde Personalizar. No se crean estilos propios por plantilla o por familia (catálogo, fichas, Seminarios, Convenios, Carta, etc.).
2. **La única excepción de color permitida es el color de cada oferta académica** (el acento configurado por oferta, `--offer-accent`). Ninguna otra superficie tiene color propio. En particular, **Seminarios no tiene celeste propio**: sus botones, insignias y tarjetas usan la misma paleta que el resto.
3. **Dos anchos de contenido:** el general de Kadence (`--global-content-width`, hoy 1180 px, editable) y uno de **lectura** de 1080 px (Novedades, noticia, evento, FAQ y Contacto). Se eliminan 1120, 1140, 1160, 1200, 1240 y 1280.
4. **Un componente, una implementación.** Cada componente tiene una base y modificadores; no se duplica un componente con otro nombre (ver «Componentes»).
5. **Kadence gobierna el diseño y los títulos visibles** de Portada y de las páginas fijas (Formación, Contacto y Preguntas frecuentes); los contenidos siguen en sus editores (ver `docs/auditoria-ui-ux/baseline/2026-11-22-kadence/` y el paquete de plantillas).
6. **No hay doctorados** en FLACSO Uruguay: no se promueven ni se diseñan para ellos.

## Fundamentos (tokens propuestos)

| Token | Valor propuesto |
|---|---|
| Ancho | `--flacso-w-general: var(--global-content-width, 1180px)`; `--flacso-w-lectura: min(1080px, var(--flacso-w-general))`; margen lateral único |
| Color | marca = `palette1`, acento = `palette2`, oscuro = `palette3`, texto = `palette4`, secundario = `palette5`, fondo claro = `palette8`, blanco = `palette9`; estados ok, aviso y error como tokens |
| Tipografía | familia heredada de Kadence (sin Inter ni Arial fijos); H1 de portada, H1 de página (`clamp(2.2rem, 4.5vw, 3.5rem)`), H2, H3 y cuerpo con una escala única |
| Radio | 8, 12, 16, 22 y pastilla (999) |
| Sombra | pequeña, media (`--flacso-shadow-card`) y grande |
| Foco | anillo de dos tonos de `style.css` (azul `palette1` y franja blanca): ya vigente |
| Breakpoints | 480, 767, 992, 1080 (cambio de menú) y 1280; 760, 767.98 y 768 se unifican en 767 |
| Imágenes | tarjetas 4:5 o 16:9 con `cover`; hero 16:9 con `cover`; `contain` solo para logos y afiches con fondo propio |

## Componentes (una base y modificadores)

Encabezado de página (hero) · breadcrumb · encabezado de sección · tarjeta · botón (primario, marca, contorno) · insignia y estado (con ícono y texto, nunca solo color) · campo de formulario · filtro y búsqueda local · estado vacío · acordeón · paginación · enlace y flecha · tabla. El detalle (qué clases absorbe cada uno y qué estados debe tener) está en el plan maestro de la auditoría y se completa aquí cuando se implementa cada componente.

Reglas transversales: foco visible en todo elemento interactivo; contraste de texto de 4,5:1 (3:1 en componentes y texto grande); el estado nunca se comunica solo con color; tarjeta = un enlace envolvente; títulos h2 de sección y h3 de tarjeta, sin saltos.

## Reglas para quien edita el tema

- No agregar `#hex` de marca, radios, sombras, anchos máximos ni `font-family` fuera de los tokens. Si falta un token, se agrega al sistema; no se declara un valor local.
- No agregar `!important`, estilos en línea ni CSS a las hojas apiladas de Seminarios, Carta y la ficha de oferta: se consolidan, no se amplían.
- Antes de tocar CSS compartido: correr el arnés de regresión visual (`dev/entorno-local/regresion.mjs` del plugin) y medir con `uniformidad.py`; cada cambio debe mejorar esos números o no empeorarlos.
- La plantilla abre su contenedor principal con `flacso_child_main_tag()`: un solo `<main>` por documento.

## Ejecución (pasos del plan maestro)

U0 arnés de regresión visual (hecho) · U1 tokens y anchos, por familias: lectura, catálogo, fichas, estados · U2 componentes (breadcrumb, insignia, botón, encabezado de página con controles en el Personalizador, tarjeta, campos, filtros, vacíos, acordeón, paginación, enlace, tabla) · U3 consolidar las capas pesadas (Seminarios, Carta, ficha de oferta) · U4 test de contrato que impida valores fuera de los tokens.

## Decisiones aún abiertas

Título de pestaña y buscadores al editar los encabezados en Kadence (supuesto: solo cambia el encabezado visible); radio canónico de tarjeta (propuesta 16 px); si la FAQ pasa a acordeón; Template Elements de Theme Kit Pro.

## Imágenes de las tarjetas (ya implementado)

Medido el 22/11/2026, `/formacion/` descargaba 67,7 MB de imágenes de tarjetas (20 archivos originales de 3 a 5 MB). Hoy descarga 0,50 MB. Cómo funciona y qué no hay que romper:

- **Tamaños:** `flacso-card` (480×600) y los 4:5 del tema (`flacso-oferta-4x5` y `flacso-seminario-4x5`, 800×1000), registrados en `inc/media.php`.
- **Nunca el original:** el filtro `image_downsize` (`flacso_child_image_downsize_fallback`) entrega el mayor tamaño reducido que exista cuando falta el del tema (imágenes subidas antes de que se registraran). Una plantilla nueva de listado usa `flacso_child_card_image_html()`, que agrega `srcset`, `sizes`, `loading="lazy"` y `decoding="async"`.
- **`sizes` según el ancho real de la tarjeta**, medido en producción: 154–159 px a 412 px, 346 px a 768 px, 307 px a 1024 px y 392 px a 1280 px, es decir `(max-width: 600px) 40vw, (max-width: 900px) 46vw, (max-width: 1100px) 31vw, 400px`. Con un `sizes` mayor, un teléfono retina descarga la variante de 800 px (1,2 MB).
- **WebP acotado (`inc/webp-cards.php`):** se genera un `.webp` (calidad 82) junto al PNG o JPEG de los tamaños `-480x600` y `-800x1000`, y se sirve solo en las tarjetas; si no existe, se sirve el PNG. **No usar el filtro global `image_editor_output_format`:** convertiría todas las imágenes del sitio, incluida la de las vistas previas de enlaces (`og_meta_size`). Se genera al subir y se borra con el adjunto.
- Una imagen de listado nueva en otro tamaño no recibe WebP: hay que sumar su tamaño a `FLACSO_CHILD_WEBP_CARD_SIZES` y sus pruebas.

## Plugins del sitio y qué cuidar (revisados en producción el 22/11/2026)

| Plugin | Relación con el tema | Qué cuidar |
|---|---|---|
| **WP Smush** | Optimiza PNG y JPG sin pérdida y hace la carga diferida (clases `lazyload`, atributos `data-src`) | Hoy no genera WebP (`wp-smush-webp` no existe, `webp_mod` en `false`). Si se activa su WebP local (Pro), podría duplicar el trabajo del módulo del tema. Se engancha a los mismos filtros de URL, pero solo para su caché y con prioridad 10 (el tema usa 20). |
| **Content Views** | Su filtro `filter_disable_wp_responsive_image` quita `srcset` y `sizes` | Solo dentro de sus propios bloques: un listado hecho con Content Views no tendrá `srcset`. |
| **Rank Math** | Etiqueta robots de la Carta | El tema corrige su directiva con el filtro `rank_math/frontend/robots`; un cambio de plugin SEO exige revisarlo. |
| **WP Super Cache** | Sirve HTML estático | No reenvía encabezados de PHP (la Carta no emite `X-Robots-Tag` en copias cacheadas; basta la etiqueta). Tras cambiar plantillas o imágenes hay que vaciar la caché o los cambios no se ven. |
| **Code Snippets** | PHP guardado en la base de datos | Revisar sus fragmentos antes de tocar ganchos de imágenes o robots (hoy hay 1 activo y no toca esas zonas). |
| Kadence Blocks, Quick Featured Images, Site Kit, WP Consent API | Sin cruce conocido | — |

## Operaciones en el servidor (procedimientos y advertencias)

Siempre con autorización explícita de la persona responsable, como el usuario `web5` (`sudo -n -u web5 wp --path=<ruta>`), con `nice -n 19`, y verificando antes y después.

- **Generar un tamaño en imágenes existentes:** `wp media regenerate <ids> --image_size=flacso-card --yes`. **Advertencia:** en 16 de 66 adjuntos dejó registrado **un único tamaño** en los metadatos (los archivos siguieron en disco). Se repara con `wp media regenerate <ids> --only-missing --yes`. Antes y después, comparar por adjunto la cantidad de tamaños registrados y de archivos en disco, y probar primero con un solo adjunto.
- **Generar los `.webp`:** `flacso_child_backfill_webp( $ids )` con una lista de IDs. Sin argumentos recorre toda la mediateca: pasar siempre los IDs. Solo agrega archivos; se revierte borrando los `.webp`.
- **Vaciar la caché de páginas:** el plugin no trae comando de WP-CLI (`wp super-cache` no existe). Usar `global $file_prefix; wp_cache_clean_cache( $file_prefix, true );` con `wp eval`.
- Consultas de solo lectura previas: contar adjuntos, tamaños registrados contra archivos en disco, espacio libre y carga del servidor.
