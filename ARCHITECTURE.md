# Arquitectura del tema FLACSO

El tema hijo contiene únicamente presentación y compatibilidad con Kadence. Los tipos de contenido, campos, formularios, consultas y reglas académicas pertenecen al plugin `flacso-uruguay-plugin`.

## Recursos

- `style.css`: tokens visuales, foco y reglas realmente globales.
- `assets/css/<plantilla>.css`: estilos aislados de cada plantilla.
- `assets/js/<plantilla>.js`: comportamiento progresivo de cada plantilla.
- `functions.php`: registro y carga condicional de recursos.

Los recursos específicos se cargan mediante el nombre real de la plantilla y usan `filemtime()` como versión. No deben agregarse bloques `<style>` a las plantillas ni cargar dependencias después de `get_header()`.

## Adaptador académico público

`inc/academic-public-model.php` es la única frontera del tema con `FLACSO_Academic_Catalog`, `FLACSO_Academic_Repository` y las entidades académicas. La capa:

- consume directamente las clases y funciones canónicas del plugin v7;
- arma View Models estables y limpios de sólo lectura para las plantillas;
- no contiene metadatos, funciones ni clases legacy;
- nunca calcula la apertura de preinscripciones ni escribe datos académicos.

## CTA de preinscripción

El tema no construye destinos locales ni gestiona preinscripciones en WordPress. Todo CTA apunta al portal externo centralizado (`https://preinscripciones.flacso.edu.uy`), obtenido directamente de `link_preinscripcion`. Los enlaces usan `data-flacso-preinscription-cta="1"`; el tracking GA4/Meta reconoce ese atributo para la analítica.

## Componentes

Cuando una sección de plantilla vuelva a utilizarse, debe trasladarse a `template-parts/<dominio>/` y recibir sus datos mediante el argumento `context` de `get_template_part()`. El componente escapa sus atributos y texto en el punto de salida.

## Verificación

Ejecutar:

```bash
./bin/check-theme.sh
```

Valida sintaxis PHP, tests contractuales del View Model, enlaces externos y ausencia total de clases o metadatos legacy.
