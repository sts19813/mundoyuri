<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Conoce la historia, el propósito y los valores detrás de Mundo Yuri.">
    <title>Quiénes somos · Mundo Yuri</title>
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
                <h1>¿Quiénes <em>somos?</em></h1>
                <p>Bienvenido.</p>
            </header>

            <article class="about-card">
                <p>Este sitio nació de una idea muy simple: crear un lugar donde cualquier persona pueda descubrir, disfrutar y enamorarse del género <strong>Girls' Love (GL)</strong>, sin importar si apenas está comenzando o si lleva años siguiéndolo.</p>

                <p>Todo lo que ves aquí ha sido creado por una sola persona. Desde el diseño y la programación hasta el mantenimiento diario, cada detalle ha sido desarrollado con dedicación y mucho cariño por la comunidad GL.</p>

                <p>El propósito de este proyecto nunca ha sido competir con las plataformas oficiales ni obtener ganancias. Al contrario, busca acercar a más personas a este maravilloso género y apoyar a quienes hacen posible cada historia.</p>

                <aside class="about-highlight">
                    <span class="about-highlight-icon" aria-hidden="true">♡</span>
                    <p>Por eso, <strong>ningún video está alojado en nuestros servidores</strong>. El sitio únicamente recopila enlaces disponibles en otras plataformas para facilitar el acceso al contenido.</p>
                </aside>

                <p>Siempre que exista una opción oficial, se procurará dirigir a los usuarios hacia ella e invitarles a apoyar a los creadores mediante suscripciones, visualizaciones o cualquier medio oficial disponible. Creemos que apoyar a las productoras, actrices, directores y plataformas es la mejor forma de que sigan naciendo nuevas historias.</p>

                <p>Este proyecto es completamente gratuito. No vendemos membresías, no solicitamos donativos y no mostramos publicidad invasiva. Queremos que puedas navegar con tranquilidad, sin anuncios molestos y sin preocuparte por software malicioso.</p>

                <p>Además, creemos en la transparencia. Todo el desarrollo del portal es <strong>código abierto</strong> y puede consultarse libremente en GitHub. Compartir el código es nuestra forma de aportar un poco a la comunidad de desarrolladores y demostrar que este proyecto está construido con honestidad.</p>

                <p>Más que una página web, este es un pequeño proyecto hecho con pasión. Si gracias a este sitio descubres una nueva serie, apoyas una plataforma oficial o simplemente encuentras una historia que te haga sonreír, entonces habrá cumplido su propósito.</p>

                <p>Gracias por formar parte de esta comunidad.</p>

                <footer class="about-signature">
                    <span>Hecho con ❤️ para todos los fans del Girls' Love.</span>
                    <a href="https://github.com/sts19813" target="_blank" rel="noopener noreferrer">sts19813</a>
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
