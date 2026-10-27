# Paletas estacionales

La paleta es una sobrescritura de colores aislada. No modifica `style.css`,
`episodios.css`, `dialogs.css` ni los estilos originales de componentes.

| Tema | Principal | Secundario | Ambiente |
| --- | --- | --- | --- |
| Halloween | `#f0b368` | `#de873d` | Dorado, naranja y carbón cálido |
| Navidad | `#9acfaf` | `#579b7c` | Verde y bosque oscuro |
| Primavera | `#afd4bd` | `#bfaddf` | Menta y lavanda |
| Verano | `#f5c56b` | `#66bdc4` | Dorado y turquesa |
| Otoño | `#dfaa76` | `#b87748` | Cobre y marrón cálido |

`layouts.portal` solo agrega `data-season-palette` al HTML y carga
`assets/css/season-palette.css` cuando hay un tema activo. La hoja se carga después
de los estilos originales y usa selectores delimitados por ese atributo. Cambia
variables y colores fijos de bordes, enlaces, fondos, menús, tarjetas, formularios,
Miyu, diálogos y controles de episodios. Los avisos semánticos de error, peligro,
éxito y estado conservan sus colores. Las fotos, vídeos, texto escrito por usuarios,
avatares e ilustraciones no se recolorean con filtros.

Los botones principales reciben un degradado y texto oscuro con contraste sobre
los acentos claros. La paleta no agrega JavaScript, animaciones, imágenes ni filtros.
Al desactivarla, no se envía ni la hoja ni el atributo: se usan los colores originales.
Las pestañas abiertas deben recargarse después de guardar en el administrador.

La hoja se genera con `scripts/build-season-palette.mjs` a partir de los estilos
fuente y las cinco paletas. El generador imprime el resultado por stdout; no es
código que se ejecute en el sitio. Su resultado se guarda como el archivo CSS
estático `public/assets/css/season-palette.css`.
