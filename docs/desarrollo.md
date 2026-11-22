# Guía técnica del tema hijo para desarrolladores y agentes

El checkout se llama `flacso-theme-kadence` y se instala como `kadence-child-flacso`. Kadence es el padre. El plugin institucional provee los tipos de contenido, los datos y las reglas que consumen las plantillas. Para un cambio cruzado, partí del [repositorio del plugin](https://github.com/ivan1arriola/flacso-uruguay-plugin) y su guía común de desarrollo. Aplicá las [reglas del repositorio](../AGENTS.md) antes de tocar código.

## Entrada, plantillas y recursos

`functions.php` carga módulos en orden fijo. Seguí la secuencia al reorganizar archivos: configuración y compatibilidad, contraste y documentos, adaptadores, SEO, portada, navegación, medios, assets y contacto. `inc/academic-team-context.php` se carga desde la ficha de oferta. Los archivos que elige WordPress por nombre siguen en la raíz. Los parciales viven en `template-parts/` y reciben datos con el tercer argumento de `get_template_part()`. Verificá la clave real: oferta y seminario suelen usar `context`, y las secciones de Carta usan `carta`.

| Superficie | Entrada y datos | Software de presentación |
|---|---|---|
| Inicio | `front-page.php` llama a `inc/home.php`. Los ajustes de portada, las campañas y el contenido publicado llegan desde el plugin. | `template-parts/home/`, `assets/css/front-page.css`, `assets/js/front-page.js`. El hero anterior tiene un renderer de compatibilidad en `template-parts/front-page/hero.php`. |
| Cabecera y pie | `header.php` y `footer.php` delegan al padre cuando `flacso_use_kadence_chrome` está activo. El modo institucional usa menús y respaldo de `inc/navigation.php` y datos de `inc/contact.php`. | `template-parts/header/navbar.php`, `template-parts/footer/site-footer.php`, `site-chrome.css/js`. Verificar logo, menús, CTA, búsqueda y tecla Escape en ambos modos. |
| Formación y taxonomía | `page-formacion.php` es puente a `formacion-page.php`, también seleccionado desde la ruta virtual del plugin. `taxonomy-tipo-oferta-academica.php` resuelve listados de tipos. | `formacion-page.css` y CSS de tarjetas/listados se comparten en varias entradas. Conservar formato de imagen, color por oferta, orden, estados y destinos. |
| Oferta y Carta | `single-oferta-academica.php` llama al adaptador y al modelo de equipos. Despacha ficha o Carta según la ruta/query var. | `oferta-academica/{page.php,style.css,script.js}`. Carta usa `inc/carta-view-model.php`, `template-parts/oferta/carta.php`, `template-parts/carta/` y sus hojas base, editorial y referencia. |
| Seminarios | `archive-seminario.php` prepara y delega el listado a `seminarios-listado.php`. `single-seminario.php` usa `template-parts/oferta/seminario.php`. | El archivo carga tres hojas de Seminarios más los estilos compartidos. La ficha usa `single-seminario.css/js`. Probar edición, integrado y componente, filtros, estado vacío y CTA. |
| Programas, equipo y convenios | `archive-programa-academico.php`, `single-programa-academico.php`, `archive-docente.php`, `single-docente.php`, `archive-convenio.php` y `single-convenio.php`. | Mantener relaciones y filtros provenientes del plugin, títulos, búsqueda y estados vacíos. |
| Eventos, entradas y novedades | `single-evento.php`, `single.php`, `category-novedades.php`, `template-parts/evento/expositores.php`. | Cuerpo de bloques y metadatos. Probar paginación, imágenes, evento cancelado y enlace a actividades relacionadas. |
| Contactos y FAQ | `page-contacto.php` y `inc/contact.php` para ruta y ajustes. `page-preguntas-frecuentes.php` delega el listado al plugin. | CSS/JS de páginas en assets. Mantener renderer del formulario y de las FAQ, enlaces y estado cuando el plugin falta. |
| Rutas restantes y confirmaciones | Las rutas comunes caen en la jerarquía de WordPress/Kadence. `gracias/` contiene fragmentos invocados desde fuera del tema. | Usar una página estándar, búsqueda y 404 como control al cambiar CSS global. Comprobar confirmaciones solo en prueba con datos ficticios. |

`inc/assets.php` registra dependencias y rutas con `flacso_child_asset_version()` para versionar recursos. Revisá prioridades, condición de plantilla y si el recurso llega efectivamente al navegador. La carga existente de Bootstrap global, el enqueue específico del archivo de Seminarios y la capa móvil en la cabecera institucional son casos a comprobar antes de consolidar. Los estilos de contenido compartidos tienen que sobrevivir a ambos modos de cabecera. Colocá los recursos nuevos en el registro adecuado antes de `wp_head`.

## Integración con Kadence

El ajuste está en **Apariencia → Personalizar → Integración con Kadence** y empieza desactivado. Prepará primero las asignaciones de menús, logo, buscador, CTA y widgets del modo nativo. Compará ambos modos con los datos de Contacto, navegación móvil y el salto al contenido. El modo institucional tiene sus propios parciales y ajustes.

Para una propiedad visual, inspeccioná el elemento, el valor calculado, la declaración ganadora y la variable o clase original. Modificá el control real en staging y repetí la medición. Los tokens de Kadence alimentan parte del hijo y su cobertura depende de la plantilla. Preservá los acentos académicos y la tinta calculada en `inc/color-contrast.php`. Los estilos de editor de bloques y frontal se verifican en su contexto real. Seguí los criterios de [AGENTS.md](../AGENTS.md) al tocar selectores, estilos en línea y `!important`.

## Contratos académicos y rutas especiales

- El adaptador en `inc/academic-public-model.php` consulta el catálogo del plugin y forma modelos públicos para las plantillas. Mantener programa/oferta/cohorte separados de seminario/edición. Los aranceles llegan de la tabla asociada y la Carta reutiliza el contexto de la ficha.
- La preinscripción depende del estado y el destino que expone el modelo. Preservá el portal externo, el atributo `data-flacso-preinscription-cta` y la alternativa de consulta que corresponda. El formulario de consulta sirve un propósito distinto y su handler pertenece al plugin.
- Para Carta, mantené la URL de subruta, la decisión HTTP/HTML de robots, el índice, el formato impreso y los documentos. La comparación entre calendario de cohorte y malla de oferta está en `inc/academic-document-presentation.php`.
- Para Formación y Contactos verificá una respuesta anónima con HTTP, título, canonical, query vars, plantilla y recursos. En Contactos el puente vive en `inc/contact.php`, con ajuste de código HTTP y título.
- Los metadatos SEO en `inc/open-graph.php` y `inc/theme-setup.php` interactúan con proveedores externos instalados. Probá ficha y Carta como pareja cuando cambies la detección.

## Verificar por problema

En la raíz del tema, desde Bash con PHP en PATH y grep compatible con -P. En Windows, el comando se verificó con Git Bash:

```bash
bash bin/check-theme.sh
php tests/kadence-compat-contract-test.php
php tests/academic-public-model-test.php
```

La primera orden ejecuta lint y contratos PHP. Chrome o Chromium se usa en la revisión visual por separado. La prueba del modelo de Carta requiere un checkout hermano del plugin. Leé las salidas `SKIP` aunque el proceso termine con código 0. Algunos contratos de `tests/` corren por separado y la expresión regular de Menciones es sensible a CRLF en la versión documentada.

| Cambio | Contratos de partida | Comprobación en WordPress |
|---|---|---|
| Kadence, portada o cabecera | `kadence-compat-contract-test.php`, `front-page-layout-contract-test.php`, `front-page-legacy-assets-test.php`, `home-contract-test.php`, `home-content-fallback-test.php` | Cambiar una opción del Personalizador, comparar estilos en desktop/móvil, activar y restaurar el modo nativo. |
| Formación, archivo y ficha | `page-formacion-color-contract-test.php`, `archive-four-five-contract-test.php`, `type-listing-design-test.php`, `academic-seminar-alignment-test.php`, `offer-contrast-ink-test.php` | Tarjetas, imagen 4:5, acento y CTA en estados abierto/cerrado, enlaces, filtros y vacío. |
| Datos académicos, Carta o documentos | `academic-public-model-test.php`, `academic-team-scope-test.php`, `carta-view-model-test.php`, `shared-calendar-curriculum-test.php`, `carta-reference-layout-contract-test.php` | Cohorte/edición actual y ausente, arancel, Carta y ficha normal, robots HTTP/HTML, navegación e impresión. |
| FAQ, contacto o SEO de Seminarios | `faq-page-test.php`, `seminarios-document-title-test.php`, `theme-bootstrap-contract-test.php` | Página virtual, título, foco, formulario ficticio y recursos en pantalla. |

Revisá el [workflow de CI](../.github/workflows/theme-ci.yml) para saber qué activa el PR. Un PR de solo CSS requiere ejecución manual de la suite y evidencia visual. Para comparar interfaz, registrá URL, dimensiones, versión, modo de cabecera y datos del fixture. Revisá teclado, foco, zoom y movimiento reducido cuando se vea afectada la interacción.

## Entregar y diagnosticar

- Si un cambio no aparece, compará SHA y versión servida, ruta de stylesheet, dependencias, caché y declaración CSS ganadora. Para datos, seguí la clave desde la plantilla hasta el adaptador y el catálogo.
- Si aparece en modo institucional y desaparece en modo nativo, inspeccioná la delegación en los archivos de cabecera/pie y el enqueue de recursos compartidos. Probá primero un control real y un elemento de contenido.
- Un PR del tema incluye las rutas verificadas, el par tema/plugin utilizado, los resultados de sus contratos y capturas antes/después si aplica. El [workflow de despliegue](../.github/workflows/deploy-theme.yml) toma la rama principal del plugin para validar y no provee rollback automático. La persona que aprueba el deploy debe contar con el artefacto o revisión previa y el ajuste afectado.
- Consultá [Arquitectura](../ARCHITECTURE.md), [Contribución](../CONTRIBUTING.md), [Modelo académico en el tema](academic-model-compatibility.md) y el [archivo de la auditoría UI/UX](auditoria-ui-ux/LEEME.md) para el detalle especializado.
