# Logos de Mundo Yuri por estación

Variantes SVG derivadas directamente de `mundo-yuri-logo.svg`.
Conservan los contornos vectoriales originales de la rosa y las letras, sin fuentes,
JavaScript, filtros ni dependencias externas.
El nombre cambia solo de color: los nueve trazados, sus transformaciones y sus
posiciones son idénticos al original. Halloween usa ámbar `#F0B368`, Navidad verde
salvia `#A8CFB2`, Primavera rosa suave `#EBA9D1`, Verano dorado `#F5C56B` y Otoño
cobre `#DCA16C`. Al desactivar el tema vuelve el logo original con su rosa `#ED287A`.

Cada logo tiene dos pequeños destellos vectoriales que se iluminan lentamente, con
dos ciclos por carga (16–19 segundos) y luego quedan estáticos. No hay GIFs, vídeos,
brillos sobre las letras ni JavaScript adicional. Los destellos se animan solo con
opacidad y escala; con movimiento reducido quedan estáticos desde el principio.

| Tema del administrador | Archivo en `public/assets/img/logos/` | Detalles |
| --- | --- | --- |
| Halloween | `mundo-yuri-halloween.svg` | Sombrero de punta doblada, ala elíptica, pliegues, cinta con hebilla, calabaza con gajos y luna |
| Navidad | `mundo-yuri-christmas.svg` | Gorro con pliegues y ribete, acebo con bayas y copo ramificado |
| Primavera | `mundo-yuri-spring.svg` | Flores con pétalos y estambres, hojas con nervaduras |
| Verano | `mundo-yuri-summer.svg` | Gafas con reflejos, sol degradado, brote y ondas de agua |
| Otoño | `mundo-yuri-autumn.svg` | Hojas de roble y arce, nervaduras y tonos cobre |
| Desactivado | `mundo-yuri-logo.svg` | Original intacto |

El componente `season-logo` selecciona un único archivo según `SeasonThemeSetting::activeTheme()`.
Se usa en la cabecera y el pie del portal, con las medidas responsive existentes.
El cambio ocurre al recargar tras guardar en **Admin → Tema estacional**.
El archivo activo lleva versión para actualizarlo aunque el navegador o el PWA
tengan guardada una versión anterior del diseño.
La consulta de ajustes está memoizada por petición sobre la caché de archivo;
guardar en el admin invalida ambas cachés.

No se precargan las otras variantes. Con el tema desactivado solo se referencia el
logo original y sigue sin cargarse CSS, JavaScript ni imágenes estacionales.
Los logos del panel, correos e iconos PWA conservan su identidad permanente.
