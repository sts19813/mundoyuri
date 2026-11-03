@extends('layouts.portal')

@section('head')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <x-seo
        title="Derechos de autor y retiros de contenido"
        description="Procedimiento de Mundo Yuri para avisos de derechos de autor, retiro de contenido y contra-avisos."
        :canonical="route('dmca')"
    />

    <x-portal-favicon />

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
@endsection

@section('body')
    <x-navbar />

    <main class="about-page legal-page">
        <div class="about-ambient about-ambient-one"></div>
        <div class="about-ambient about-ambient-two"></div>

        <div class="container-xl px-4 position-relative">
            <header class="about-hero">
                <span class="about-eyebrow">Derechos de autor</span>
                <h1>Retiros de <em>contenido</em></h1>
                <p>Un canal claro para titulares de derechos, representantes autorizados y personas usuarias.</p>
            </header>

            <article class="about-card legal-card">
                <aside class="about-highlight">
                    <div>
                        <p><strong>Canal de derechos de autor:</strong> <a href="mailto:admin@mundoyuri.com">admin@mundoyuri.com</a></p>
                        <p>Confirmaremos la recepción de avisos completos en un plazo de <strong>24 a 48 horas hábiles</strong>. El plazo para resolver depende de la información recibida y de la complejidad del caso.</p>
                    </div>
                </aside>

                <section>
                    <h2>Alcance de este procedimiento</h2>
                    <p>Mundo Yuri respeta los derechos de autor y derechos conexos. Este procedimiento atiende avisos de presunta infracción, incluido el mecanismo de aviso y retirada aplicable en México y las comunicaciones comúnmente conocidas como avisos DMCA.</p>
                    <p>Cuando corresponda, podremos deshabilitar de manera expedita el acceso a un material mientras lo revisamos. No es una determinación definitiva de titularidad ni sustituye un procedimiento ante autoridad competente.</p>
                </section>

                <section>
                    <h2>Cómo enviar un aviso de retiro</h2>
                    <p>Envía un correo a <a href="mailto:admin@mundoyuri.com">admin@mundoyuri.com</a> con el asunto <strong>“Aviso de derechos de autor”</strong> e incluye, para cada obra reclamada:</p>
                    <ol>
                        <li>Tu nombre completo, empresa u organización (si aplica), cargo o relación con la persona titular, correo y un medio de contacto.</li>
                        <li>Identificación clara de la obra protegida y, si es posible, una URL oficial o documentación que ayude a identificarla.</li>
                        <li>Las URL exactas de Mundo Yuri que deseas reportar. Una URL por línea facilita una atención más rápida.</li>
                        <li>Una explicación de tu titularidad o de tu autorización para actuar en nombre de la persona titular.</li>
                        <li>Una declaración de buena fe de que el uso reportado no está autorizado por la persona titular, su agente o la ley.</li>
                        <li>Una declaración de que la información es correcta y, cuando actúes por otra persona, que estás autorizada para hacerlo.</li>
                        <li>Tu firma electrónica: tu nombre legal completo al final del mensaje es suficiente para este canal.</li>
                    </ol>
                    <p>Los avisos incompletos pueden requerir información adicional antes de que podamos actuar. No envíes contraseñas, datos bancarios ni documentos de identidad completos por correo.</p>
                </section>

                <section>
                    <h2>Qué hacemos al recibirlo</h2>
                    <p>Registramos la recepción y la revisión del caso. Si el aviso es suficiente, aplicamos una medida proporcional: normalmente deshabilitamos la fuente o enlace específico del episodio y conservamos la ficha de la serie con un aviso de no disponibilidad. Solo retiramos una ficha completa cuando el reclamo realmente la abarque.</p>
                    <p>Mantenemos un registro interno de cada caso —fecha de recepción, reclamante, obra, URLs, medida y resolución— para fines de cumplimiento y moderación. Ese registro no se publica y se conserva aun si posteriormente se restaura el contenido.</p>
                    <p>También podremos notificar a la persona que publicó el material o a quien corresponda, sin divulgar más información de la necesaria para atender el caso.</p>
                </section>

                <section>
                    <h2>Contra-aviso y restauración</h2>
                    <p>Si consideras que una retirada fue un error, tienes autorización o el uso está permitido por una limitación o excepción legal, responde al aviso que recibiste o escribe a <a href="mailto:admin@mundoyuri.com">admin@mundoyuri.com</a> con el asunto <strong>“Contra-aviso”</strong>.</p>
                    <p>Incluye tu nombre y contacto, las URL retiradas, una explicación y evidencia de tu titularidad, autorización o fundamento legal, y una declaración de que la información es correcta. Revisaremos el caso y, cuando proceda, restauraremos el acceso. No restauraremos material si recibimos una orden de autoridad competente o información suficiente de que existe un procedimiento aplicable que impida hacerlo.</p>
                </section>

                <section>
                    <h2>Reincidencia y aportes de la comunidad</h2>
                    <p>Las personas que envían aportes deben contar con los derechos o autorizaciones necesarios. Podemos retirar aportes infractores, limitar su visibilidad, rechazar futuras publicaciones o suspender cuentas que reincidan, conforme a las circunstancias y a la ley aplicable.</p>
                </section>

                <aside class="legal-note">
                    <strong>Importante:</strong> esta página explica el procedimiento operativo de Mundo Yuri y no constituye asesoría legal. Para un caso concreto, consulta a una persona profesional del derecho.
                </aside>

                <footer class="about-signature legal-signature">
                    <span>Última actualización: 3 de noviembre de 2026</span>
                    <a href="mailto:admin@mundoyuri.com">admin@mundoyuri.com</a>
                </footer>
            </article>
        </div>
    </main>

    <x-footer />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@endsection
