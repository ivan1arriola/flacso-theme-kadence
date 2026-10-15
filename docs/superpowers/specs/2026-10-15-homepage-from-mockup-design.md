# Portada FLACSO Uruguay basada en la maqueta

Estado: diseño para revisión editorial. Fecha: 15/10/2026.

## Objetivo y referencia

Reconstruir el cuerpo de la página principal de WordPress a partir de `maqueta-pagina-principal.html`, ubicada en la carpeta contenedora del tema, manteniendo noticias, oferta académica, actividades y contenido institucional conectados a sus fuentes actuales. La maqueta define composición, textos aprobados y comportamiento adaptable. El encabezado y el pie existentes quedan fuera de este rediseño.

Esta especificación sustituye, para la portada visual, las decisiones de `docs/toDo/2026-home-redesign.md` que difieren de la maqueta: Manrope/Sora, azul `#1d3a72`, índice académico en filas, bloque de preinscripciones abiertas, newsletter e imagen institucional. El modelo de contenido y los contratos del plugin se aprovechan cuando correspondan.

## Orden de lectura y contenido

1. **Hero.** Etiqueta «Programa Uruguay», título «Formación que conecta, conocimiento que transforma.», una acción principal hacia la oferta académica y la fotografía de trabajo en equipo de la maqueta. No lleva párrafo introductorio ni segundo botón. La imagen ocupa una tarjeta amplia de dos columnas en escritorio, con radio de 20 px y acento amarillo lateral; en móvil queda debajo del texto. El contenido dinámico del hero se obtiene de la campaña activa; el fallback institucional reproduce el texto y la imagen aprobados.
2. **Oferta académica.** Título «Encontrá tu próxima formación», enlace a toda la oferta y tarjetas para Maestrías, Especializaciones, Diplomados, Diplomas y Seminarios. Cada destino procede del catálogo o configuración pública de WordPress; se omiten categorías sin destino válido.
3. **Próximas actividades.** Título único «Próximas actividades» y hasta tres actividades futuras, publicadas y visibles. Cada tarjeta enlaza a la página pública de su actividad cuando existe. Las imágenes destacadas se recortan visualmente a 4:5 sin alterar el archivo original. Si no hay actividades, la sección permanece visible y muestra un estado vacío sin enlace. No hay datos de demostración ni control para ocultarla. `agenda.flacso.edu.uy` es la plataforma de reserva de fechas y nunca se usa como destino de esta sección.
4. **Noticias.** Título «Noticias», dos noticias destacadas y hasta cuatro publicaciones recientes, sin duplicados. La imagen destacada se presenta en formato 4:5; si falta o falla, aparece un respaldo visual. Títulos, fechas, extractos, imágenes y enlaces vienen de publicaciones reales.
5. **¿Qué es FLACSO Uruguay?** Panel con el logo oficial, sin ilustración adicional, y los dos párrafos institucionales aprobados en la maqueta. Mantener una única fuente editorial para el texto si WordPress ya la expone; no duplicar copias que puedan divergir. Los enlaces apuntan a la institución y a investigaciones.
6. **Impacto.** Franja «+20 países», explicación breve y acción de contacto, como cierre del cuerpo de la página.

El orden 3 y 4 responde a la corrección del 15/10/2026: actividades antes de noticias.

## Presentación adaptable

- Usar Roboto y el azul principal `#2a4676`, con amarillo para acentos y acciones. Los estilos se limitan a la portada para evitar cambios involuntarios en otras páginas del tema.
- Mantener la jerarquía y las proporciones de la maqueta, con tarjetas limpias y radios coherentes. El hero debe caber completo al abrir la página en un escritorio habitual, considerando la altura del encabezado.
- En móvil, apilar hero, oferta, actividades, noticias e institución sin desbordamiento horizontal. Conservar títulos legibles, enlaces táctiles y las imágenes 4:5 de actividades y noticias.
- Mostrar contenido esencial sin depender de JavaScript. Un H1 para la portada, H2 para secciones y fechas semánticas en publicaciones y actividades. Conservar foco visible y texto alternativo apropiado.
- Las imágenes del contenido provienen de adjuntos de WordPress para aprovechar tamaños adaptables. La fotografía del hero se añade a la biblioteca de medios o como recurso del tema hasta configurar el adjunto editorial definitivo.

## Integración con WordPress

`front-page.php` sigue siendo el punto de entrada. El tema hijo compone la nueva portada mediante componentes PHP bajo `template-parts/home/` y un CSS propio, con datos preparados por adaptadores de lectura. El plugin conserva consultas, CPT, filtros de visibilidad, configuración editorial y reglas académicas. Los componentes no consultan metadatos académicos ni imprimen el HTML de los renderers antiguos.

Se pueden reutilizar los getters existentes para campañas, categorías académicas, noticias fijadas y recientes, y actividades futuras. Si una fuente no ofrece datos suficientes para la maqueta, se añade un adaptador de lectura pequeño en el plugin, sin cambiar las publicaciones existentes. El hero usa los campos de campaña que la nueva composición muestra: etiqueta, título, imagen y acción principal. Los campos heredados de bajada y segunda acción permanecen guardados para compatibilidad, pero no aparecen en esta portada.

La opción de selección de portada existente permite preparar el nuevo renderer y conservar una ruta de regreso durante el despliegue. Se evita cargar CSS heredado de portada junto con el nuevo CSS cuando se muestre la nueva composición. Encabezado, pie, buscador y páginas interiores mantienen sus contratos actuales.

## Transición

1. Preparar los componentes y estilos en el tema; revisar los getters y adaptar únicamente los datos que falten en el plugin.
2. Configurar la fotografía del hero y confirmar imágenes destacadas y destinos reales en WordPress. El contenido estático de la maqueta no reemplaza publicaciones.
3. Revisar la portada en staging a 320, 375, 768, 1024 y 1440 px, y los estados con cero, una y varias actividades; revisar noticias sin imagen y una campaña sin fotografía.
4. Publicar mediante la opción reversible existente, con una revisión final del HTML anónimo y de los enlaces. Conservar la portada anterior mientras se confirma el comportamiento público.

## Criterios de aceptación

- El orden visible es Hero → Oferta → Próximas actividades → Noticias → Institucional → Impacto en escritorio y móvil.
- Las actividades nunca desaparecen por falta de eventos; su estado vacío informa que no hay actividades próximas y no enlaza a la plataforma de reserva de fechas.
- La portada refleja nuevas publicaciones, actividades y categorías válidas sin editar HTML.
- El hero no presenta bajada introductoria ni segunda acción.
- Noticias y actividades usan imágenes 4:5 y respaldo visual ante ausencia de imagen.
- La tipografía, color principal, logo, foto y textos acordados corresponden a la maqueta.
- El encabezado, el pie y las páginas interiores siguen funcionando con sus recursos actuales.
