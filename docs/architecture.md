# Arquitectura y Fuentes de Verdad: `kadence-child-flacso`

## 1. Responsabilidad Principal
`kadence-child-flacso` es la capa de presentación pública y experiencia de usuario (UX) basada en el tema Kadence.

## 2. Fuentes de Verdad Asignadas
- **Presentación Pública y UX**: Estilos CSS, diseño responsive, animaciones, tipografía, acordeones e interacciones de la interfaz pública.
- **Scope de Estilos**: Clases de CSS estrictamente delimitadas (`.flacso-offer-card`, `.flacso-seminar`, etc.).

## 3. Lo que NO debe hacer este repositorio
- **NO tomar decisiones de negocio ni autorización**.
- **NO realizar consultas complejas a la base de datos o mutaciones de estado académico**.
- **NO implementar envíos directos a Meta CAPI o Pixel**. Toda la emisión de eventos de tracking debe consumirse mediante la API central del plugin.
- **NO definir selectores CSS globales invasivos sobre etiquetas nativas** (`img`, `a`, `p`, `.card`).

## 4. Comunicación e Integraciones
- **Hacia el Plugin (`flacso-uruguay-plugin`)**: Consume datos y emite eventos de tracking llamando a `flacsoMetaTrackCustom(...)`.

