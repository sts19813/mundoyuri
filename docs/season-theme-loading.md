# Activación y aislamiento de los temas

El interruptor del administrador se evalúa antes de renderizar el HTML público.

- Desactivado: sin CSS estacional, scripts estacionales, elementos decorativos,
  imágenes ni preloads estacionales. La portada y su vídeo configurados vuelven a usarse.
- Halloween: carga `halloween-theme.css`, `halloween-theme.js` y sus assets.
- Otros temas: cargan `season-themes.css`; no cargan JavaScript ni imágenes de Halloween.
- Fuera del entorno local: no se carga ningún recurso estacional ni se consulta su configuración.

La comprobación mínima del interruptor sigue existiendo en el servidor. Se utiliza la
caché de archivos para evitar consultas repetidas de esquema/configuración en la base
de datos. Guardar o eliminar los ajustes invalida la caché automáticamente. La primera
consulta después de guardar o limpiar la caché lee el valor actualizado de la base de datos.

No hay polling, listeners ni scripts estacionales en el navegador cuando el tema está
desactivado. Las pestañas que ya tenían un tema cargado deben recargarse; el cambio no
inyecta ni retira código remotamente de pestañas abiertas.

Verificación: `SeasonThemeIsolationTest` cubre el apagado en varias páginas, el ciclo
de activación/desactivación desde el admin, la restauración de portada/vídeo, el
aislamiento entre temas, la lectura desde caché y la restricción al entorno local.
