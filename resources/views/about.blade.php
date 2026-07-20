```html
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Conoce la historia, el propósito y los valores detrás de Mundo Yuri.">
    <title>Quién soy · Mundo Yuri</title>
    <x-portal-favicon />
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=DM+Sans:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet"
        href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
</head>

<body>
    <x-navbar />

    <main class="about-page">
        <div class="about-ambient about-ambient-one"></div>
        <div class="about-ambient about-ambient-two"></div>

        <div class="container-xl px-4 position-relative">
            <header class="about-hero">
                <span class="about-eyebrow">Conoce el proyecto</span>
                <h1>¿Quién <em>soy?</em></h1>
                <p>La historia detrás de Mundo Yuri.</p>
            </header>

            <article class="about-card">
                <p>
                    Mundo Yuri nació de una idea muy simple: crear un lugar donde cualquier persona pueda descubrir,
                    disfrutar y enamorarse del género <strong>Girls' Love</strong>.
                    Un espacio creado con pasión, donde la experiencia siempre sea lo más importante:
                    <strong>0 publicidad. 0 membresías. 0 donativos.</strong>
                </p>

                <p>
                    Todo lo que ves aquí ha sido diseñado, programado y es mantenido por una sola persona.
                    Desde la primera línea de código hasta el último detalle visual, cada parte del sitio ha sido
                    desarrollada con dedicación y cariño para la comunidad GL.
                </p>

                <p>
                    Mi propósito nunca ha sido competir con las plataformas oficiales ni obtener ganancias.
                    Al contrario, busco acercar este maravilloso género a más personas y apoyar a quienes hacen posible
                    cada historia.
                </p>

                <aside class="about-highlight">
                    <span class="about-highlight-icon" aria-hidden="true">♡</span>
                    <p>
                        <strong>Ningún video está alojado en mis servidores.</strong>
                        Mundo Yuri únicamente recopila enlaces disponibles en otras plataformas para facilitar el acceso
                        al contenido.
                    </p>
                </aside>

                <p>
                    Siempre que exista una opción oficial, procuraré dirigir a los usuarios hacia ella e invitarlos a
                    apoyar a los creadores mediante suscripciones, reproducciones o cualquier medio oficial disponible.
                    Estoy convencido de que apoyar a las productoras, plataformas, directoras, directores, actrices y
                    todo el equipo creativo es la mejor manera de que continúen naciendo nuevas historias.
                </p>

                <p>
                    Este proyecto es completamente gratuito. No vendo membresías, no solicito donativos y no muestro
                    publicidad. Mi objetivo es ofrecer un espacio limpio, seguro y agradable, donde puedas disfrutar del
                    contenido sin interrupciones ni preocuparte por software malicioso.
                </p>

                <p>
                    También creo en la transparencia y la honestidad. Todo el desarrollo del portal es
                    <strong>código abierto</strong> y puede consultarse libremente en GitHub. Compartir el código es mi
                    forma de aportar a la comunidad de desarrolladores y demostrar que este proyecto ha sido construido
                    con total transparencia.
                </p>

                <p>
                    Más que una página web, este es un proyecto personal hecho con pasión. Si gracias a Mundo Yuri
                    descubres una nueva serie, decides apoyar una plataforma oficial o simplemente encuentras una
                    historia que te haga sonreír, entonces todo el esfuerzo habrá valido la pena.
                </p>

                <p>
                    Gracias por formar parte de esta comunidad.
                </p>

                <footer class="about-signature">
                    <span>Hecho con ❤️ para todos los fans del Girls' Love.</span>
                    <a href="https://github.com/sts19813" target="_blank" rel="noopener noreferrer">
                        sts19813
                    </a>
                </footer>
            </article>
        </div>
    </main>

    <x-footer />

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const aboutNavToggler = document.getElementById('navToggler');
        const aboutNavLinks = document.getElementById('navLinks');

        aboutNavToggler?.addEventListener('click', () => aboutNavLinks?.classList.toggle('active'));
        aboutNavLinks?.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => aboutNavLinks.classList.remove('active'));
        });
    </script>
</body>

</html>
```
