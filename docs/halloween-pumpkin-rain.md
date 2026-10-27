# Lluvia de calabacitas anime

Assets nuevos creados con la herramienta integrada de imágenes de Codex,
inspeccionados y convertidos a WebP transparente, 192 × 192 y calidad 88:

- `public/assets/img/seasonal/pumpkin-anime-grin.webp`: aproximadamente 12 KB.
- `public/assets/img/seasonal/pumpkin-anime-smile.webp`: aproximadamente 11 KB.

Ilustraciones anime con contornos limpios, sombras cel, tallos curvos y dos caras
talladas distintas. Las variantes realistas anteriores se conservan en disco, pero
ya no se referencian ni se cargan en la lluvia.

Tamaños de 32–48 px en escritorio y 26–36 px en móvil, con opacidad de .64–.84.
Se aleatorizan imagen, tamaño, posición, giro, duración, desplazamiento lateral y
curva intermedia. Máximo de doce calabazas simultáneas en escritorio y seis en móvil.
Se reutiliza un pool de doce elementos `img`, sin crecimiento indefinido del DOM.

La primera caída empieza tras .5–1.4 segundos. Las siguientes se intentan cada
.7–1.8 segundos en escritorio y 1.1–2.4 segundos en móvil. En escritorio pueden
aparecer parejas ocasionales (24 %), siempre respetando el límite. Cada caída dura
14–24 segundos. Las distintas duraciones evitan filas uniformes o un patrón en bucle.

Solo se animan transformaciones y opacidad mediante Web Animations API. No hay
canvas, filtros, sombras, intervalos por fotograma ni sonido. Los elementos no
capturan clics ni bloquean la lectura o los botones.

Las URLs permanecen en atributos `data-` hasta que empieza una caída. Con movimiento
reducido desde el inicio no se solicitan estas imágenes. La lluvia se pausa al ocultar
la pestaña o entrar en pantalla completa y se cancelan animaciones y temporizadores
al abandonar la página. Al pasar a móvil se cancelan las partículas que exceden el límite.

La plantilla y sus URLs solo se renderizan con Halloween activo. Al desactivar el
interruptor y recargar no hay HTML, imágenes, CSS ni JavaScript de Halloween.
No se han agregado recursos al precache del service worker.

Pruebas: `tests/JavaScript/HalloweenRain.test.mjs` cubre carga diferida, límites,
reutilización, variación, movimiento reducido, pausa y limpieza.
`SeasonThemeIsolationTest` verifica ausencia completa de los recursos al desactivar
y su restauración al activar.

## Prompt: sonrisa traviesa

Use case: illustration-story. Asset type: premium Japanese anime Halloween particle sprite for a tasteful website, shown at 32-48px. One isolated cute chibi jack-o-lantern pumpkin only, plump squat ribbed orange pumpkin with small curved stem. Beautiful clean hand-drawn anime cel illustration, crisp deep warm brown line art, soft cel shadows and selective painted highlights, golden orange with muted peach highlights, warm glowing carved face clearly recognizable at small size. Generous big angular carved eyes and a carved grin; charming rather than frightening, genuine Halloween jack-o-lantern, not a generic orange vegetable. High-quality game sprite polish, clean edges, strong silhouette. Entire pumpkin including stem in a square composition, nearly fill the canvas with 6 percent clear padding. Actual transparent alpha background. No background, floor, cast shadow, glow outside the silhouette, text, watermark, photo skin texture, plastic 3D render, emoji, witch hat, extra objects or characters. Three-quarter view, playful mischievous carved grin with two small square teeth and slanted triangular eyes, a short green-brown stem curling right.

## Prompt: sonrisa alegre

Use case: illustration-story. Asset type: premium Japanese anime Halloween particle sprite for a tasteful website, shown at 32-48px. One isolated cute chibi jack-o-lantern pumpkin only, plump squat ribbed orange pumpkin with small curved stem. Beautiful clean hand-drawn anime cel illustration, crisp deep warm brown line art, soft cel shadows and selective painted highlights, golden orange with muted peach highlights, warm glowing carved face clearly recognizable at small size. Generous big angular carved eyes and a carved grin; charming rather than frightening, genuine Halloween jack-o-lantern, not a generic orange vegetable. High-quality game sprite polish, clean edges, strong silhouette. Entire pumpkin including stem in a square composition, nearly fill the canvas with 6 percent clear padding. Actual transparent alpha background. No background, floor, cast shadow, glow outside the silhouette, text, watermark, photo skin texture, plastic 3D render, emoji, witch hat, extra objects or characters. Front view, sweet cheerful carved smile with one little square tooth and wide triangular eyes, a short curled brown stem leaning left. Small warm rosy accents in the pumpkin shading, but no human eyes.
