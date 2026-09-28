# Portada pública FLACSO Uruguay — especificación de diseño y frontend

Estado: propuesta revisada el 28/09/2026. No autoriza implementación ni despliegue. Contrato de datos y administración: [campañas en el plugin](../../../flacso-uruguay-plugin/docs/toDo/2026-home-campaigns.md). Ejecución futura: [plan](2026-home-redesign-implementation-plan.md).

## Objetivo y límites

Una portada académica institucional, editorial y legible, con fotografía, tipografía y jerarquía de contenidos. El tema hijo es dueño exclusivo del HTML y CSS de la **nueva** portada; el plugin aporta campañas y colecciones normalizadas. No se altera el modelo académico, las URLs públicas ni los flujos de preinscripción. La etiqueta pública del bloque derivado será «Preinscripciones abiertas»: una preinscripción no equivale a una inscripción concluida.

No se crea un page builder, no se incorpora React para contenido estático y no se diseña una nueva integración de correo. La activación debe ser reversible y coexistir temporalmente con la portada anterior.

## Diagnóstico verificable

El recorrido actual es `front-page.php` → `flacso_homepage_builder_render()` del plugin → renderers de secciones → marcado final del plugin. El shortcode y el bloque del builder también pueden activar `front-page.php` mediante `template_include`. El tema conserva un fallback con otro mapa de renderers. La visibilidad y el orden proceden de `Flacso_Main_Page_Settings`. Si se activa el filtro `flacso_main_page_use_react`, el builder puede enviar HTML/datos a `flacso-main-page-react.js`; por defecto devuelve HTML de servidor.

`functions.php` incluye `template-parts/front-page/hero.php` antes del `init` que carga los shortcodes del plugin. Ambos declaran condicionalmente `flacso_section_hero_render()`: con este tema normalmente prevalece el hero del tema; el del plugin sigue siendo fallback para otros contextos. Este acoplamiento por nombre global impide saber por la interfaz quién es dueño de cada pieza. El hero del tema lee la opción legacy, usa una imagen como CSS inline, pinta un globo decorativo y transforma cualquier año del título en la etiqueta «Inscripciones»; esto falla para eventos o campañas institucionales.

Capas visuales actuales: `front-page-base.css` (442 líneas), `front-page-hero.css` (436), `front-page-refresh.css` (368), `site-chrome.css` (974), además de `mobile-ux.css` (238). El plugin carga `flacso-main-page.css` (4784), `flacso-main-page-mobile-fixes.css` (199), `flacso-novedades-45.css` (110), fuente Google Manrope/Sora y, según contexto, `flacso-mobile-first.css` (438). `front-page-refresh.css` contiene múltiples reglas de mayor especificidad y `!important` contra el markup del plugin. Los renderers plugin de hero, novedades y eventos incluyen bloques `<style>`; los dos primeros también contienen `<script>` inline. Esto no significa que todo sea eliminable: varios shortcodes del plugin se usan fuera de la home.

El JS `front-page-hero.js` agrega estado visual a enlaces y un botón flotante observado por `IntersectionObserver`; su necesidad desaparece si se elimina ese botón. `site-chrome.js` sí sostiene menú/búsqueda y debe conservarse, simplificándolo solo tras pruebas. `flacso-main-page.js` es **administrativo** para orden/novedades, no JS público. `flacso-main-page-react.js` queda fuera de la ruta crítica por defecto, pero puede tener consumidores mediante filtro y no se borra sin inventario.

La [portada pública observada el 27/09/2026](https://flacso.edu.uy/) presenta hero de inscripciones del segundo semestre de 2026, noticias, institución, Instagram y un mosaico de formación. No presenta en el HTML consultado un bloque autónomo de preinscripciones abiertas ni una agenda editorial. El bloque de formación actual ofrece Maestrías, Especializaciones, Diplomas, Diplomados y Seminarios; el pedido propone Doctorados pero no se comprobó una URL/categoría pública equivalente. Se conservarán Diplomas y Diplomados como distinción del catálogo hasta que el equipo académico confirme si Doctorados tiene destino y oferta reales. No se inventará `/formacion/doctorados/`.

El header ya mantiene logo, navegación y búsqueda, pero su CTA por defecto es «Agenda Web», no «Inscripciones»; un menú `header_cta` puede cambiarlo. El header tiene topbar y barra principal. El footer ya tiene menús, contacto y legales; no se sustituye. El `front-page.php` cierra `#primary` sin apertura visible en `header.php` y repite `kadence_before_content`/`kadence_after_content` respecto de header/footer: auditar el DOM real y hooks antes de modificar la estructura, sin dar por comprobado un fallo visual.

## Arquitectura de presentación

`front-page.php` selecciona **un solo** renderer: nueva portada si la opción de despliegue `flacso_home_v2_enabled` es `true`, `1` o `'1'` y el contrato plugin declara `schema_version=1` con la estructura mínima válida; de lo contrario, mantiene la portada legacy. El valor por defecto es `0`; la operación administrativa/CLI que lo cambie exige respaldo. `flacso_child_home_render_context(): array` decide `mode` (`v2|legacy|emergency`) y `view_model` una vez por petición, antes de los enqueues; esa decisión gobierna HTML y CSS/JS. El tema responde al filtro `flacso_main_page_requires_legacy_assets` con `mode !== 'v2'`; el plugin aplica el filtro con valor seguro por defecto `true`. Así conserva los assets legacy cuando el tema cae a ese renderer, incluso si la opción dice v2. El rollback vuelve la opción a `0` y muestra una copia institucional legacy aprobada y no fechada, preparada antes de activar v2.

Las secciones nuevas viven en `template-parts/home/` y reciben arrays de datos, no consultan `post_meta` ni interpretan estados académicos. La obtención/normalización pertenece a servicios del plugin descritos en el documento enlazado. Si falta el plugin completo, el tema presenta un fallback institucional mínimo, sin enlaces a rutas inexistentes; no simula datos de campañas ni deja la home vacía.

Contrato de vista propuesto: `flacso_get_home_view_model(): array` devuelve `campaign`, `formation`, `open_preinscriptions`, `news`, `events`, `institution`, `newsletter` y `links`. Cada recurso incluye texto, URL y attachment ID cuando corresponda; no incluye HTML, clases CSS ni colores. El tema escapa en la salida y el plugin valida en escritura/lectura. Cada colección trae como máximo lo que la home muestra. `campaign` siempre existe: campaña activa o fallback institucional, con `source = campaign|fallback`. El CTA de cabecera se obtiene de navegación/configuración existente; se define explícitamente su destino editorial y no se lo infiere de una campaña.

La jerarquía visual y de lectura será:

1. Header compacto: logo, navegación, búsqueda accesible y CTA «Ver preinscripciones abiertas» únicamente si existe un destino real. Mantener accesos institucionales, pero evaluar colapsar la topbar en móvil.
2. Hero: una campaña editorial activa o fallback institucional. Un H1, kicker, párrafo, hasta dos CTA e imagen auténtica. Variantes fijas `institutional`, `enrollment`, `event`, `call`; ninguna opción libre de CSS. La imagen no es requisito: el diseño textual conserva contraste y proporción.
3. Formación: índice editorial, líneas y números discretos, no cinco tarjetas. Tipos vinculados solo a destinos publicados; Doctorados queda condicionado. Se puede mantener Diplomas y Diplomados separados (seis filas) si ese es el catálogo real; no se sacrifica exactitud para cumplir un número visual.
4. Preinscripciones abiertas: hasta tres propuestas derivadas automáticamente de cohorte/edición publicada y habilitada, con URL pública de preinscripción válida. Tipo, título, instancia, modalidad y fecha **solo si constan y tienen precisión suficiente**; enlazar al programa y ofrecer CTA de preinscripción. Si faltan ofertas accionables, omitir el bloque y no dirigir el CTA de cabecera a él. Enlace «Ver todas» al catálogo/recorrido existente, no a una página inventada.
5. Actualidad: una noticia destacada y dos o tres secundarias, con contenido `post`/categoría existente. Imagen opcional sin hueco roto; el criterio de destaque respeta los mecanismos actuales mientras se migra.
6. Agenda: hasta tres eventos públicos próximos, lista de fecha/título/hora/modalidad y enlace. Sin carrusel; si no hay eventos, omitir bloque o mostrar enlace a Agenda Web según contenido disponible.
7. Institución: breve explicación de FLACSO Uruguay y su pertenencia al sistema, formación, investigación y cooperación. Una fotografía institucional bien elegida y CTA al contenido existente. No duplicar texto largo de «Quiénes somos».
8. Newsletter: encabezado breve y el formulario existente de `Flacso_Mailing_Subscription`, sin segunda lógica de alta. Si el módulo no está disponible, omitir el formulario público; no mostrar uno inerte.
9. Footer: conservar enlaces y datos de contacto, reducir decoración; verificar que enlaces legales/externos sigan presentes.

No se añade una franja de campaña secundaria en v1. La única campaña activa ocupa el hero; cualquier anuncio adicional deberá justificar un mensaje/destino distinto, accesibilidad y regla de prioridad propia antes de incorporarse. El anuncio superior actual se trata por separado y no se reutiliza automáticamente como segundo hero.

## Sistema visual único

Usar variables del tema bajo `.flacso-home-v2`, sin alterar globalmente Kadence ni los shortcodes legacy. Manrope para cuerpo y navegación y Sora para títulos mientras una prueba de rendimiento no justifique un cambio; cargar fuentes una vez, con fallback del sistema y `font-display: swap` cuando se controlen los assets. Paleta: `#1d3a72` azul principal, `#0f1a2d` navy, `#ffffff`, `#f8f9fa`, `#e9edf2`, acento `#fed222`; verde `#248138` únicamente en acciones/estados con contraste comprobado. No usar gradiente como recurso ornamental ni sombras de tarjetas.

| Token | Móvil | Escritorio / uso |
| --- | --- | --- |
| `--home-container` | ancho disponible | máximo 1200px |
| `--home-gutter` | 16px | 24px a ≥768px; 32px a ≥1200px |
| `--home-space-*` | 8, 16, 24, 32, 48, 64px | misma escala; elegir por contexto |
| display/H1 | `clamp(2.5rem, 7vw, 5rem)` | interlineado 1.05, máximo 15ch orientativo |
| H2 | `clamp(2rem, 4vw, 3rem)` | interlineado 1.15 |
| H3 | `clamp(1.25rem, 2vw, 1.5rem)` | interlineado 1.25 |
| cuerpo | 1rem / 1.6 | ancho de lectura 65ch |
| pequeño | .875rem / 1.45 | fechas y metadatos |
| eyebrow | .75rem / 1.3 | peso 700, espaciado .08em |
| navegación | .9375rem / 1.3 | etiquetas directas |
| radios | 0–8px | solo imagen/botón si es útil |
| sombras | ninguna por defecto | leve solo menú desplegado |

Breakpoints de composición: 768px para dos columnas y 1024px para navegación/hero amplio; 1200px solo ajusta gutters. No atar lógica de negocio a breakpoints. Mantener `focus-visible` visible, subrayado de enlaces en contenido y contraste AA verificado por variante.

### Adaptación mobile-first

A 320–375px: header de una fila con logo, búsqueda y menú; CTA accesible en el menú si no cabe. Hero texto primero, imagen en bloque con proporción fijada o fondo seguro y CTA apilados con alto ≥44px. Formación como lista completa sin carrusel. Preinscripciones, noticias y agenda como bloques verticales separados por líneas, sin columnas estrechas. Institución texto antes de imagen; newsletter etiqueta, campo, consentimiento y botón sin desbordar. A ≥768px: hero e institución en dos columnas, noticia principal con secundarias laterales, preinscripciones en 2 columnas si caben. A ≥1024px: navegación expandida y hasta 3 propuestas, preservando jerarquía principal/secundaria.

HTML: un `<main id="main">`, un H1 en el hero, H2 por sección, H3 por ítem; `section` con `aria-labelledby` cuando aporta nombre, `article` solo para piezas autónomas, lista para índices/eventos. Imagen hero con `<picture>`/`wp_get_attachment_image` y `srcset`/`sizes`, dimensiones y `fetchpriority="high"`; las demás `loading="lazy"`. Alt vacío para decoración, descriptivo para fotografía informativa. No duplicar texto como imagen y alt. Respetar `prefers-reduced-motion`; ningún dato ni CTA depende de animación, hover o JS. Objetivo: sin desplazamiento horizontal a 320px y sin CLS atribuible a imágenes nuevas. Medir LCP/CLS y presupuesto CSS/JS en entorno equivalente antes de fijar umbrales de Lighthouse, no prometer puntuación sin baseline.

## Archivos y tratamiento futuro

| Actual | Decisión al implementar | Condición |
| --- | --- | --- |
| `front-page.php` | modificar para switch y luego simplificar | conservar rollback durante el rollout; auditar hooks/DOM |
| `header.php`, `footer.php`, `template-parts/header/navbar.php`, `template-parts/footer/site-footer.php` | mantener y ajustar | sin romper menús, búsqueda, contacto ni Kadence |
| `functions.php` | modificar enqueues y retirar inclusión hero legacy al final | no afectar páginas no-home |
| `template-parts/front-page/hero.php` | reemplazar por `template-parts/home/hero.php` | retirar función global solo cuando no haya consumidores |
| `assets/css/front-page-base.css`, `front-page-hero.css`, `front-page-refresh.css` | reemplazar en home v2 por `assets/css/home/home.css` | retirar archivos solo tras verificar usos y rollback |
| `assets/css/site-chrome.css`, `mobile-ux.css` | mantener, reducir overrides en fase posterior | afectan sitio completo |
| `assets/js/front-page-hero.js` | eliminar después | sin FAB ni consumidores |
| `assets/js/site-chrome.js`, `featured-images.js` | mantener | JS funcional; revisar alcance en home |
| `template-parts/home/{hero,formation,open-preinscriptions,news,agenda,institution,newsletter}.php` | crear | solo presentación; secciones finitas |

El plugin debe mantener sus renderers/shortcodes antiguos hasta comprobar usos fuera de portada; el mapa de eliminación detallado está en [su especificación](../../../flacso-uruguay-plugin/docs/toDo/2026-home-campaigns.md). No borrar bloques CSS por número de líneas: primero verificar en qué páginas se cargan y si existen consumidores externos.

## Migración y aceptación

Publicar primero API/modelo plugin sin cambiar render público; después templates/CSS v2 con flag apagado; preparar campaña, fallback nuevo y copia institucional legacy; probar con datos reales anonimizados en staging; activar flag con rollback de una operación; comparar DOM, enlaces y métricas; retirar legacy en entrega posterior. Mantener la URL de la portada, shortcodes/bloques legacy y todas las URLs de destino. Durante una incompatibilidad de DTO se conserva el renderer y sus assets legacy; si falta el plugin por completo, se presenta el fallback mínimo del tema. Verificar que la caché pública refleje cada cambio de campaña y rollback en un máximo de 60 segundos antes de activar la automatización.

Aceptación: contenidos y campañas editables sin deploy; solo una campaña visible en cada instante; preinscripciones derivadas de instancia publicada/habilitada y con enlace válido, no editadas a mano; noticias/eventos reales; formulario de mailing existente; semántica y teclado verificados; 320/375/768/1024/1440px sin overflow; reduced-motion; imágenes sin CLS; HTML base útil sin JS; Lighthouse y Web Vitals medidos contra baseline; rollback con hero institucional vigente y CSS correcto. Revisar contraste, fechas, enlaces externos, rastreo de analytics y búsqueda.

## Decisiones abiertas antes de programar

1. Confirmar categoría/destino real de Doctorados o mantener la taxonomía pública de seis tipos; no reetiquetar Diplomados como Doctorados.
2. Definir enlace canónico «Ver todas las preinscripciones abiertas» y CTA de cabecera; no asumir que un mismo destino sirve para ambos.
3. Revisar contenido e imagen institucional aprobados, derechos de uso y recortes móvil/escritorio.
4. Verificar disponibilidad/capacidades de los roles editoriales reales frente al modo `FLACSO_Editor_Admin_Mode` y el WordPress desplegado.
5. Medir CSS/JS y DOM reales por ruta; distinguir código muerto de compatibilidad con shortcodes/bloques.
