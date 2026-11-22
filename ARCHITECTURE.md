# Arquitectura del tema hijo FLACSO

El tema hijo contiene presentación, plantillas y compatibilidad con Kadence. Los tipos de contenido, campos, formularios, consultas y reglas académicas pertenecen al plugin `flacso-uruguay-plugin`.

## Punto de entrada y módulos

`functions.php` protege el acceso directo y carga los módulos del tema en orden explícito. Las funciones públicas y los callbacks conservan sus nombres y contratos.

- `inc/theme-setup.php`: eslogan institucional y normalización de eslóganes heredados.
- `inc/kadence-compat.php`: opción reversible para utilizar los constructores nativos de cabecera y pie de Kadence.
- `inc/navigation.php`: ubicaciones de menú, menús de respaldo y CTA de cabecera.
- `inc/media.php`: tamaños de imagen, miniaturas destacadas y renovación de caché al editar imágenes.
- `inc/assets.php`: limpieza de cabecera y carga condicional de CSS/JavaScript, Bootstrap, jQuery UI y recursos de páginas.
- `inc/contact.php`: opciones del personalizador, datos de contacto y ruta virtual `/contactos/`.
- `inc/academic-public-model.php`: adaptador de sólo lectura al modelo académico del plugin.
- `inc/academic-team-context.php`: adaptación de equipos académicos para la presentación.
- `inc/home.php`: datos, View Models y render de la portada.
- `inc/open-graph.php`: metadatos Open Graph y compatibilidad con plugins SEO.

Las plantillas que WordPress detecta por nombre permanecen en la raíz. Las secciones reutilizables se colocan en `template-parts/<dominio>/` y reciben sus datos por `context` de `get_template_part()`.

La ficha pública de una Oferta Académica se mantiene en `oferta-academica/`: `page.php` genera el marcado, `style.css` reúne sus estilos y `script.js` sus interacciones. `single-oferta-academica.php` permanece en la raíz como entrada exigida por la jerarquía de plantillas de WordPress y despacha a `page.php`; `inc/assets.php` carga los dos recursos de la carpeta. La subpágina Carta conserva su plantilla independiente.

## Recursos

- `style.css` contiene metadatos del tema, tokens visuales y reglas globales.
- `assets/css/<plantilla>.css` y `assets/js/<plantilla>.js` contienen recursos específicos.
- `inc/assets.php` determina qué recursos cargar para cada plantilla y para rutas virtuales, como `/formacion/`.
- `flacso_child_asset_version()` usa `filemtime()` para invalidar caché cuando cambia un recurso.

No agregar bloques `<style>` o dependencias después de `get_header()`. Mantener los recursos de página dentro del registro condicional existente.

## Catálogo de formación

La página virtual `/formacion/` se implementa en sólo dos archivos de presentación:

- `formacion-page.php`: consultas mediante el adaptador académico y marcado HTML de ofertas y seminarios.
- `assets/css/formacion-page.css`: composición responsive, gestos cromáticos y marcos fotográficos 4:5 con `object-fit: cover` (se permite recorte, no deformación).

`page-formacion.php` es exclusivamente un puente de compatibilidad: WordPress lo selecciona por nombre y el plugin también lo incluye directamente. No introducir lógica ni estilos en ese puente. `inc/assets.php` carga la hoja de estilos únicamente para la ruta de formación, aunque ésta se resuelva como página virtual. No se necesita JavaScript propio para el recorte.

## Adaptador académico

`inc/academic-public-model.php` es la frontera del tema con las clases canónicas del plugin. Produce View Models estables para las plantillas; no contiene datos académicos legacy, no escribe información del catálogo y no calcula la apertura de preinscripciones. Los CTA usan el destino provisto por el plugin y apuntan al portal externo centralizado.

## Contacto y rutas virtuales

`inc/contact.php` mantiene las opciones editables del personalizador y las rutas que el plugin/tema resuelven sin una página almacenada. `inc/assets.php` reconoce `/formacion/` para que los recursos de esa presentación virtual sigan cargándose. Al cambiar estas rutas hay que revisar también el título, el estado HTTP y los recursos asociados.

## Verificación

Ejecutar `bash bin/check-theme.sh`. El script valida sintaxis PHP, pruebas contractuales incluidas, enlaces externos seguros, ausencia de estilos embebidos y contratos de CTA/modelo. El workflow `.github/workflows/deploy-theme.yml` lo ejecuta antes de crear el artifact.

## Compatibilidad con Kadence

Los colores, las familias tipográficas, los colores de botones y el ancho máximo de los contenedores institucionales utilizan variables globales de Kadence. Los breakpoints y las composiciones editoriales de la portada siguen definidos por el tema hijo.

En **Apariencia → Personalizar → Integración con Kadence** se puede activar **Usar cabecera y pie nativos de Kadence**. Se mantiene desactivado por defecto para evitar reemplazar la navegación institucional en producción sin una vista previa. Al activarlo se delegan `header.php` y `footer.php` al tema padre: sus constructores administran logo, filas, menús, botones, widgets y responsive. Desactivar la opción recupera el chrome institucional.

Los menús y widgets propios de Kadence deben configurarse en el constructor del tema padre: no se transfieren automáticamente los menús y los campos de contacto del pie personalizado. Las fuentes, colores y botones de Kadence siguen funcionando en la portada con cualquiera de los dos modos. Los tamaños editoriales del hero y Bento Grid continúan a cargo del tema hijo.
