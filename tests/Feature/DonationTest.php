<?php

namespace Tests\Feature;

use App\Models\Donation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DonationTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_is_available_without_stripe_and_does_not_offer_payment(): void
    {
        config(['services.stripe.key' => null, 'services.stripe.secret' => null, 'services.stripe.webhook_secret' => null]);
        $this->get(route('donations.index'))->assertOk()->assertSee('Siempre gratis, sin publicidad y a un clic de tus historias favoritas.')->assertSee('Las donaciones aún no están disponibles')->assertSee('Quiero dejarte un mensaje')->assertSee('$30');
        $this->postJson(route('donations.store'), ['amount' => 10000])->assertStatus(503);
    }

    public function test_footer_shows_support_link_only_when_stripe_is_configured(): void
    {
        $keys = [
            'services.stripe.key' => 'pk_test_example',
            'services.stripe.secret' => 'sk_test_example',
            'services.stripe.webhook_secret' => 'whsec_example',
        ];

        config($keys);
        $this->blade('<x-footer />')->assertSee('Regálame unas flores');

        foreach (array_keys($keys) as $key) {
            config($keys);
            config([$key => null]);
            $this->blade('<x-footer />')->assertDontSee('Regálame unas flores');
        }
    }

    public function test_amount_is_validated_and_unsigned_webhooks_are_rejected(): void
    {
        config(['services.stripe.key' => 'pk_test_example', 'services.stripe.secret' => 'sk_test_example', 'services.stripe.webhook_secret' => 'whsec_example']);
        $this->postJson(route('donations.store'), ['amount' => 2999])->assertUnprocessable()->assertJsonValidationErrors('amount');
        $this->postJson(route('donations.store'), ['amount' => 3000, 'message' => str_repeat('a', 501)])->assertUnprocessable()->assertJsonValidationErrors('message');
        $this->postJson(route('donations.webhook'), ['type' => 'payment_intent.succeeded'])->assertBadRequest();
        $this->assertDatabaseCount('donations', 0);
    }

    public function test_star_requires_an_opted_in_successful_non_refunded_donation(): void
    {
        $user = User::factory()->create();
        $donation = Donation::create(['user_id' => $user->id, 'amount' => 5000, 'currency' => 'mxn', 'show_star' => true, 'status' => 'pending']);
        $this->get(route('profiles.show', $user))->assertDontSee('title="Apoya a Mundo Yuri"', false);
        $donation->update(['status' => 'succeeded']);
        $this->get(route('profiles.show', $user))->assertSee('title="Apoya a Mundo Yuri"', false);
        $donation->update(['refunded_amount' => 5000]);
        $this->get(route('profiles.show', $user))->assertDontSee('title="Apoya a Mundo Yuri"', false);
    }
}
