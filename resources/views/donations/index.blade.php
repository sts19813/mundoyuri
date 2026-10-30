@php($activeSeasonTheme = \App\Models\SeasonThemeSetting::activeTheme())
<!DOCTYPE html>
<html lang="es" @if($activeSeasonTheme) data-season-palette="{{ $activeSeasonTheme }}" @endif>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex">
    <title>Apoya a Mundo Yuri</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/portal-fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/donations.css') }}?v={{ filemtime(public_path('assets/css/donations.css')) }}">
    @if($activeSeasonTheme)<link rel="stylesheet" href="{{ asset('assets/css/season-palette.css') }}?v={{ filemtime(public_path('assets/css/season-palette.css')) }}">@endif
    @if($enabled)<script src="https://js.stripe.com/v3/" defer></script>@endif
</head>
<body>
<main class="page">
    <a class="back" href="{{ route('home') }}"><span aria-hidden="true">←</span> Volver a Mundo Yuri</a>
    <div class="layout">
        <section class="intro">
            <p class="eyebrow"><span aria-hidden="true">✦</span> UN ESPACIO HECHO CON CARIÑO</p>
            <h1>Un pequeño gesto puede mantener <em>vivo este mundo.</em></h1>
            <p class="lead">Cada aportación, por pequeña que sea, significa mucho.</p>
            <div class="promise"><span aria-hidden="true">♡</span><p>Siempre gratis, sin publicidad y a un clic de tus historias favoritas.</p></div>
        </section>
        <section class="card" aria-label="Formulario de apoyo">
            <div id="donation-form-area">
                <div class="card-heading"><p class="card-kicker">APOYA A MUNDO YURI</p><h2>Muchas gracias</h2></div>
                @if(!$enabled)<p class="notice">Las donaciones aún no están disponibles. Gracias por querer apoyarnos.</p>@endif
                <form id="donation-form">
                    <div id="donation-options">
                    <div class="label" id="amount-label">Elige tu aportación <span>MXN</span></div>
                    <div class="amounts" role="group" aria-labelledby="amount-label"><button type="button" data-amount="30" aria-pressed="false">$30</button><button type="button" data-amount="50" class="active" aria-pressed="true">$50</button><button type="button" data-amount="100" aria-pressed="false">$100</button></div>
                    <label class="custom-label" for="amount">O elige otra cantidad</label>
                    <div class="custom"><span aria-hidden="true">$</span><input id="amount" type="number" min="30" max="10000" step="0.01" value="50" required><span aria-hidden="true">MXN</span></div>
                    <label class="check message-toggle"><input type="checkbox" id="include-message" aria-controls="message-field" aria-expanded="false"><span>Quiero dejarte un mensaje <small>Opcional</small></span></label>
                    <div id="message-field" class="message-field" hidden><label class="sr-only" for="message">Tu mensaje</label><textarea id="message" maxlength="500" rows="3" placeholder="Escribe algo bonito..."></textarea></div>
                    @auth
                        <label class="check"><input type="checkbox" id="show-star"><span>Mostrar una flor en mi perfil<small>Un pequeño agradecimiento; no muestra el monto.</small></span></label>
                    @else
                        <p class="guest-note">Puedes apoyar sin una cuenta. Si inicias sesión, podrás mostrar una flor en tu perfil como agradecimiento.</p>
                    @endauth
                    </div>
                    <div id="payment-step" class="payment-step" hidden>
                        <div class="payment-summary"><div><span>Tu aportación</span><strong id="payment-amount"></strong></div><button type="button" id="edit-donation">Editar</button></div>
                        <div id="payment-element" class="payment"></div>
                    </div>
                    <p id="payment-error" class="error" role="alert" aria-live="polite"></p>
                    <button class="submit" id="submit" type="submit" @disabled(!$enabled)>Continuar con mi aportación <span aria-hidden="true">→</span></button>
                    <p class="fine">Pago único y seguro con <strong>Stripe</strong></p>
                </form>
            </div>
            <div class="thanks" id="result" role="status"></div>
        </section>
    </div>
</main>
<script>
document.addEventListener('DOMContentLoaded', async () => {
    const form = document.querySelector('#donation-form'), amount = document.querySelector('#amount'), message = document.querySelector('#message'), submit = document.querySelector('#submit'), error = document.querySelector('#payment-error');
    const includeMessage = document.querySelector('#include-message'), messageField = document.querySelector('#message-field');
    includeMessage.addEventListener('change', () => { messageField.hidden = !includeMessage.checked; includeMessage.setAttribute('aria-expanded', String(includeMessage.checked)); });
    const amountButtons = document.querySelectorAll('[data-amount]');
    const syncAmounts = () => amountButtons.forEach(button => {
        const selected = amount.value !== '' && Number(button.dataset.amount) === Number(amount.value);
        button.classList.toggle('active', selected);
        button.setAttribute('aria-pressed', String(selected));
    });
    amountButtons.forEach(button => button.addEventListener('click', () => { amount.value = button.dataset.amount; syncAmounts(); }));
    amount.addEventListener('input', syncAmounts);
    @if($enabled)
    const stripe = Stripe(@json(config('services.stripe.key')));
    let elements = null, paymentElement = null;
    const options = document.querySelector('#donation-options'), paymentStep = document.querySelector('#payment-step');
    document.querySelector('#edit-donation').addEventListener('click', () => {
        paymentElement?.destroy();
        paymentElement = null; elements = null;
        options.hidden = false; paymentStep.hidden = true; error.textContent = '';
        submit.innerHTML = 'Continuar con mi aportación <span aria-hidden="true">→</span>';
        amount.focus();
    });
    const secret = new URLSearchParams(location.search).get('payment_intent_client_secret');
    if (secret) {
        history.replaceState({}, '', location.pathname);
        const result = await stripe.retrievePaymentIntent(secret);
        const status = result.paymentIntent?.status;
        const box = document.querySelector('#result');
        document.querySelector('#donation-form-area').style.display = 'none'; box.style.display = 'block';
        if (status === 'succeeded') box.innerHTML = '<h2>Gracias de corazón ♥</h2><p>Tu apoyo me ayuda a seguir cuidando este espacio.</p><a href="{{ route('home') }}">Volver a Mundo Yuri →</a>';
        else if (status === 'processing') box.innerHTML = '<h2>Tu apoyo está en proceso</h2><p>Stripe está confirmando tu pago. Gracias por acompañarnos.</p><a href="{{ route('home') }}">Volver a Mundo Yuri →</a>';
        else box.innerHTML = '<h2>No se completó el pago</h2><p>Puedes volver a intentarlo cuando quieras.</p><a href="{{ route('donations.index') }}">Intentar de nuevo →</a>';
        return;
    }
    form.addEventListener('submit', async event => {
        event.preventDefault(); error.textContent = ''; submit.disabled = true;
        try {
            if (!elements) {
                const cents = Math.round(Number(amount.value) * 100);
                if (!Number.isFinite(cents) || cents < 3000 || cents > 1000000) throw new Error('Elige una cantidad entre $30 y $10,000 MXN.');
                const response = await fetch(@json(route('donations.store')), {method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}, body:JSON.stringify({amount:cents, message:includeMessage.checked ? message.value : null, show_star:document.querySelector('#show-star')?.checked || false})});
                const data = await response.json(); if (!response.ok) throw new Error(data.message || 'No pudimos preparar el pago.');
                const theme = getComputedStyle(document.documentElement);
                const themeColor = name => theme.getPropertyValue(name).trim();
                elements = stripe.elements({clientSecret:data.clientSecret, appearance:{theme:'night',variables:{colorPrimary:themeColor('--rose'),colorBackground:themeColor('--dark-surface'),colorText:themeColor('--text'),borderRadius:'10px',fontFamily:'DM Sans, system-ui, sans-serif'}}});
                paymentElement = elements.create('payment');
                paymentElement.mount('#payment-element');
                document.querySelector('#payment-amount').textContent = new Intl.NumberFormat('es-MX', {style:'currency', currency:'MXN'}).format(cents / 100) + ' MXN';
                options.hidden = true; paymentStep.hidden = false;
                submit.innerHTML = 'Confirmar aportación <span aria-hidden="true">→</span>';
            } else {
                const result = await stripe.confirmPayment({elements, confirmParams:{return_url:@json(route('donations.index'))}});
                if (result.error) throw new Error(result.error.message);
            }
        } catch (e) { error.textContent = e.message || 'Ocurrió un error. Inténtalo de nuevo.'; }
        submit.disabled = false;
    });
    @endif
});
</script>
</body>
</html>
