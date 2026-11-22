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
