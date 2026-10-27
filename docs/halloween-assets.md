# Halloween: assets realistas

Generados con la herramienta integrada de imágenes de Codex. Recortes con alpha real,
optimizados a WebP con `cwebp -q 88`, conservando transparencia. Los originales permanecen
en la biblioteca de imágenes generadas; los archivos usados por el sitio están en
`public/assets/img/seasonal/` (aproximadamente 121 KB en total).

- `spider.webp`: araña marrón vista desde arriba, ocho patas articuladas, pelo fino y abdomen moteado.
- `earthworm.webp`: lombriz marrón rojiza, segmentos húmedos y cuerpo completo en S suave.
- `bat.webp`: murciélago marrón visto desde arriba con alas extendidas, membranas y dedos visibles.

## Prompts de generación

Marco común: “Use case: photorealistic-natural. Asset type: isolated photographic cutout
for an animated Halloween website overlay. Style: realistic natural macro wildlife
photography, convincing fine anatomy and texture, restrained neutral studio light from
upper left, readable silhouette on both light and dark webpage content. Scene/backdrop:
true transparent alpha background. Constraints: one animal only, no ground, no text,
no border, no cast shadow, no glowing eyes, no cartoon, no emoji, no illustration.”

Araña: “A single anatomically accurate dark brown house spider viewed directly overhead,
facing upwards. Eight long thin jointed legs splayed naturally, small cephalothorax,
oval abdomen with subtle natural mottling, tiny fine hairs, realistic pedipalps.
Entire spider with every leg in frame and generous clear padding. No spider silk.”

Lombriz: “A single anatomically accurate earthworm, whole body in a gentle shallow S
curve oriented from left to right, viewed directly overhead. Natural muted reddish
brown segmented moist skin, subtle clitellum and fine ring texture, slightly tapered
ends. Entire worm in frame, generous clear padding.”

Murciélago: “A single realistic small brown bat viewed from above flying horizontally
with head pointing upwards, both wings fully spread symmetrically. Anatomically accurate
articulated finger bones visible through leathery dark brown membranes, scalloped wing
trailing edges, small furry torso, small ears, subtle warm highlights. Entire bat in
frame with padding.”

Edición del murciélago: “Remove the ENTIRE background around the bat including all
blurry brown halo, gradients, light and shadow. Keep only the actual bat itself, exactly
the same anatomy, texture, colors and shape. Fully transparent alpha everywhere outside
the animal's precise silhouette, including gaps between ears, limbs and wings. Crisp
clean alpha cutout, no backplate, no shadow, no new background.”

El componente `season-creature` usa una sola imagen por animal, sin máscaras animadas
ni sombras. Dos arañas de 24–29 px (oscurecidas a negro) y tres hormigas SVG de 9–11 px
recorren el viewport mediante Web Animations API, con pausas cortas entre tramos. En móvil
solo se animan una araña y dos hormigas. El murciélago, la telaraña y la lombriz originales
se conservan como assets, pero ya no se cargan en la página.
`jack-o-lanterns.svg` contiene tres calabazas con tallos, gajos y caras talladas diferentes;
es una ilustración vectorial estática, sin filtros ni animaciones.

Se retiraron las capas de niebla con blur y las animaciones de patas y alas. No hay bucles
JavaScript por fotograma: solo se calcula el siguiente destino al terminar un tramo.
Las animaciones se pausan al ocultar la pestaña o entrar en pantalla completa y se cancelan
al abandonar la página. Con movimiento reducido no se muestran animales ni interferencia.

La interferencia usa tres bandas finas durante 420 ms, como máximo dos veces por sesión
de pestaña (sessionStorage). Se aplaza si hay un diálogo visible o un campo en edición.
No filtra ni clona el contenido del sitio, ni reproduce audio.
