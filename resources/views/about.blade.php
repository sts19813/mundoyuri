<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <x-seo
        title="Sobre Mundo Yuri"
        description="Conoce la filosofía y la historia de Mundo Yuri, un proyecto independiente dedicado al Girls' Love y con raíces que se remontan a 2007."
        :canonical="route('about')"
    />

    <x-portal-favicon />

    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=DM+Sans:wght@300;400;500;600&display=swap"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}"
    >
</head>

<body>

    <x-navbar />

    <main class="about-page">

        <div class="about-ambient about-ambient-one"></div>
        <div class="about-ambient about-ambient-two"></div>

        <div class="container-xl px-4 position-relative">

            {{-- HERO --}}
            <header class="about-hero">
                <span class="about-eyebrow">Sobre el proyecto</span>

                <h1>
                    Mundo <em>Yuri</em>
                </h1>
            </header>


            {{-- CONTENIDO --}}
            <article class="about-card">

                <p>
                    Mundo Yuri nació de una idea muy simple: un sitio donde cualquiera pueda
                    <strong>descubrir, disfrutar y enamorarse del yuri</strong>
                </p>

                <p>
                    Un lugar creado con mucho cariño y mucha paciencia, donde tu experiencia
                    sea lo más importante.
                </p>


                {{-- FILOSOFÍA --}}
                <aside class="about-highlight">

                    <p>
                        <strong>0 publicidad. 0 membresías. </strong><br>
                        Nunca solicitaré ningún donativo ni mostraré anuncios dentro del portal.
                    </p>
                </aside>


                <p>
                    Mi objetivo no es fomentar la piratería, ni competir con las plataformas oficiales,
                    mucho menos obtener ganancias. Al contrario.
                    Siempre que exista una opción oficial, procuraré ofrecerla.
                    Creo que apoyar a las productoras, plataformas, directoras, directores,
                    actrices y a todo el equipo creativo detrás de estas historias es la mejor
                    manera de ayudar a que continúen naciendo nuevas obras.
                </p>

                <p>
                    Todo el portal de Mundo Yuri y sus subdominios únicamente recopilan y
                    organizan enlaces para facilitar el acceso al contenido.
                    <strong>Ningún video ni material está alojado en mis servidores.</strong>
                </p>


                {{-- SOBRE EL DESARROLLO --}}
                <p>
                    Todo lo que ves aquí lo he diseñado, programado y mantenido yo mismo.
                    Soy una sola persona detrás de este proyecto.
                    Por eso, si encuentras algún error, información incorrecta o algo que
                    simplemente no funciona como debería, siempre puedes reportarlo con
                    <strong>✦ MIYU · ASISTENTE</strong>.
                    Lo revisaré tan pronto como me sea posible.
                </p>

                <p>
                    También creo mucho en la transparencia y la honestidad.
                    Todo el desarrollo de Mundo Yuri es <strong>código abierto</strong>
                    y puede consultarse libremente en GitHub.
                    Compartir el código es mi forma de aportar un poco a la comunidad de
                    desarrolladores y, al mismo tiempo, demostrar con transparencia cómo
                    está construido este proyecto.
                </p>


                {{-- EXPERIENCIA PERSONAL --}}
                <p>
                    Llevo mas de <strong>15 años compartiendo yuri</strong>.
                    Durante todo ese tiempo he colaborado en distintas páginas y también
                    he trabajado en traducciones, dando soporte a sitios.
                </p>

                <p>
                    Pero he visto repetirse muchas veces la misma historia:
                    proyectos que comienzan por gusto y por compartir, pero que con el tiempo
                    terminan girando alrededor del dinero y la monetización.
                </p>

                <p>
                    Mundo Yuri nace también para hacer las cosas de otra manera y conservar
                    esa filosofía.
                </p>

                <p>
                    No quiero que entrar aquí signifique cerrar anuncios, pagar una membresía
                    o encontrarte con contenido bloqueado detrás de un pago.
                    Quiero que simplemente puedas entrar, descubrir algo que te guste
                    y disfrutarlo.
                </p>

                <p>
                    Si gracias a Mundo Yuri descubres una nueva serie, decides apoyar una
                    plataforma oficial o simplemente encuentras una historia que te haga
                    sonreír, entonces todo el esfuerzo habrá valido la pena.
                </p>


                {{-- HISTORIA --}}
                <hr class="my-5">

                <section class="about-history">

                    <span class="about-eyebrow">Un poco de historia</span>

                    <h2>
                        Una pequeña parte de la historia
                    </h2>

                    <p>
                        Hay algo curioso detrás de <strong>Mundo Yuri</strong>.
                    </p>

                    <p>
                        En <strong>2007</strong> se creó la primera versión de este portal.
                        Por eso decidí recuperar a los primeros usuarios de aquella época y conservar
                        una pequeña parte de su historia.
                    </p>

                    <p>
                        También quiero dejar un profundo agradecimiento a la fundadora de
                        Mundo Yuri de aquella época. Sin saberlo, muchos años después,
                        su trabajo sigue dejando una pequeña huella en este proyecto.
                    </p>

                    <p>
                        Es increíble pensar que, mucho antes de la versión actual,
                        ya hubo otro Mundo Yuri: un sitio y un foro dedicado al
                        <strong>yuri y al shoujo-ai</strong>, con su propia comunidad,
                        usuarios, perfiles, conversaciones y personas que compartían
                        el mismo gusto por estas historias.
                    </p>

                    <p>
                        Aunque hay una diferencia importante: aquel Mundo Yuri no estaba
                        dedicado principalmente a un público hispanohablante.
                    </p>

                    <p>
                        Internet ha cambiado muchísimo desde entonces.
                        Muchas de aquellas páginas desaparecieron y buena parte de esa
                        época quedó perdida con ellas.
                    </p>

                    <p>
                        La versión actual de Mundo Yuri
                        <strong>no es aquel mismo sitio</strong>.
                        Es un proyecto nuevo, construido desde cero muchos años después.
                    </p>

                    <p>
                        Pero después de descubrir y recorrer lo que todavía queda de
                        aquella comunidad, me pareció bonito conservar una pequeña parte
                        de su historia.
                    </p>

                    <p>
                        Porque al final, aunque hayan pasado casi veinte años,
                        la idea sigue siendo sorprendentemente parecida:
                    </p>


                    {{-- MENSAJE FINAL --}}
                    <aside class="about-highlight">
                        

                        <p>
                            <strong>
                                Un lugar para quienes disfrutamos del yuri.
                            </strong>
                        </p>
                    </aside>

                </section>


                {{-- FIRMA --}}
                <footer class="about-signature">

                    <span>
                        Hecho con mucho cariño para todos los fans del GL. yuri, shoujo-ai<br>
                    </span>

                    <a
                        href="https://github.com/sts19813"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        sts19813
                    </a>

                </footer>

            </article>

        </div>

    </main>


    <x-footer />


    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    </script>

</body>

</html>