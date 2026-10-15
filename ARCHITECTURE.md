# Arquitectura del tema hijo FLACSO

El tema hijo contiene presentación, plantillas y compatibilidad con Kadence. Los tipos de contenido, campos, formularios, consultas y reglas académicas pertenecen al plugin `flacso-uruguay-plugin`.

## Punto de entrada y módulos

`functions.php` protege el acceso directo y carga los módulos del tema en orden explícito. Las funciones públicas y los callbacks conservan sus nombres y contratos.

- `inc/theme-setup.php`: eslogan institucional y normalización de eslóganes heredados.
- `inc/navigation.php`: ubicaciones de menú, menús de respaldo y CTA de cabecera.
- `inc/media.php`: tamaños de imagen, miniaturas destacadas y renovación de caché al editar imágenes.
- `inc/assets.php`: limpieza de cabecera y carga condicional de CSS/JavaScript, Bootstrap, jQuery UI y recursos de páginas.
- `inc/contact.php`: opciones del personalizador, datos de contacto y ruta virtual `/contactos/`.
- `inc/academic-public-model.php`: adaptador de sólo lectura al modelo académico del plugin.
- `inc/academic-team-context.php`: adaptación de equipos académicos para la presentación.
- `inc/home-v2.php`: componentes y View Models de la portada renovada.
- `inc/open-graph.php`: metadatos Open Graph y compatibilidad con plugins SEO.

Las plantillas que WordPress detecta por nombre permanecen en la raíz. Las secciones reutilizables se colocan en `template-parts/<dominio>/` y reciben sus datos por `context` de `get_template_part()`.

## Recursos

- `style.css` contiene metadatos del tema, tokens visuales y reglas globales.
- `assets/css/<plantilla>.css` y `assets/js/<plantilla>.js` contienen recursos específicos.
- `inc/assets.php` determina qué recursos cargar para cada plantilla y para rutas virtuales, como `/formacion/`.
- `flacso_child_asset_version()` usa `filemtime()` para invalidar caché cuando cambia un recurso.

No agregar bloques `<style>` o dependencias después de `get_header()`. Mantener los recursos de página dentro del registro condicional existente.

## Adaptador académico

`inc/academic-public-model.php` es la frontera del tema con las clases canónicas del plugin. Produce View Models estables para las plantillas; no contiene datos académicos legacy, no escribe información del catálogo y no calcula la apertura de preinscripciones. Los CTA usan el destino provisto por el plugin y apuntan al portal externo centralizado.

## Contacto y rutas virtuales

`inc/contact.php` mantiene las opciones editables del personalizador y las rutas que el plugin/tema resuelven sin una página almacenada. `inc/assets.php` reconoce `/formacion/` para que los recursos de esa presentación virtual sigan cargándose. Al cambiar estas rutas hay que revisar también el título, el estado HTTP y los recursos asociados.

## Verificación

Ejecutar `bash bin/check-theme.sh`. El script valida sintaxis PHP, pruebas contractuales incluidas, enlaces externos seguros, ausencia de estilos embebidos y contratos de CTA/modelo. El workflow `.github/workflows/deploy-theme.yml` lo ejecuta antes de crear el artifact.