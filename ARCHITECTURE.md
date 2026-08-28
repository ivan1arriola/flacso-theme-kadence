# Arquitectura del tema FLACSO

El tema hijo contiene únicamente presentación y compatibilidad con Kadence. Los tipos de contenido, campos, formularios, consultas y reglas académicas pertenecen al plugin `flacso-uruguay-plugin`.

## Recursos

- `style.css`: tokens visuales, foco y reglas realmente globales.
- `assets/css/<plantilla>.css`: estilos aislados de cada plantilla.
- `assets/js/<plantilla>.js`: comportamiento progresivo de cada plantilla.
- `functions.php`: registro y carga condicional de recursos.

Los recursos específicos se cargan mediante el nombre real de la plantilla y usan `filemtime()` como versión. No deben agregarse bloques `<style>` a las plantillas ni cargar dependencias después de `get_header()`. `carta-template.php` es la única excepción: su CSS permanece embebido porque el documento se distribuye como carta/HTML autocontenido.

## CTA de preinscripción

El tema no interpreta `flujo_preinscripcion` ni construye destinos. Obtiene
estado, mensajes y URL mediante `flacso_get_preinscription_cta()` o
`flacso_get_preinscription_url()` del plugin. Los enlaces usan
`data-flacso-preinscription-cta="1"`; el tracking GA4/Meta reconoce ese atributo
y agrega al destino los parámetros de campaña conservados en `sessionStorage`.

## Componentes

Cuando una sección de plantilla vuelva a utilizarse, debe trasladarse a `template-parts/<dominio>/` y recibir sus datos mediante el tercer argumento de `get_template_part()`. El componente escapa sus atributos y texto en el punto de salida.

## Verificación

Ejecutar:

```bash
./bin/check-theme.sh
```

Si están instalados PHP_CodeSniffer y WordPress Coding Standards, ejecutar también `phpcs` desde la raíz del tema.
