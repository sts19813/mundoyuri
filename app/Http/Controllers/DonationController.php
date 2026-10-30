<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;
use Stripe\Webhook;

class DonationController extends Controller
{
    public function index()
    {
        return view('donations.index', ['enabled' => filled(config('services.stripe.key')) && filled(config('services.stripe.secret')) && filled(config('services.stripe.webhook_secret'))]);
    }

    public function store(Request $request)
    {
        abort_unless(filled(config('services.stripe.key')) && filled(config('services.stripe.secret')) && filled(config('services.stripe.webhook_secret')), 503);
        $data = $request->validate(['amount' => ['required', 'integer', 'min:1500', 'max:1000000'], 'show_star' => ['boolean'], 'message' => ['nullable', 'string', 'max:500']]);
        $donation = Donation::create(['user_id' => $request->user()?->id, 'show_star' => $request->user() && ($data['show_star'] ?? false), 'amount' => $data['amount'], 'currency' => 'mxn', 'message' => $data['message'] ?? null]);
        try {
            $intent = (new StripeClient(config('services.stripe.secret')))->paymentIntents->create([
                'amount' => $donation->amount, 'currency' => $donation->currency,
                'automatic_payment_methods' => ['enabled' => true],
                'metadata' => ['donation_id' => (string) $donation->id],
                'description' => 'Apoyo voluntario a Mundo Yuri',
            ], ['idempotency_key' => 'donation-'.$donation->id]);
            Donation::whereKey($donation->id)->where('status', 'started')->update(['stripe_payment_intent_id' => $intent->id, 'status' => 'pending']);
            return response()->json(['clientSecret' => $intent->client_secret]);
        } catch (\Throwable $e) {
            $donation->update(['status' => 'error']);
            Log::error('Stripe donation creation failed', ['donation_id' => $donation->id, 'exception' => $e]);
            return response()->json(['message' => 'No pudimos preparar el pago. Inténtalo de nuevo.'], 502);
        }
    }

    public function webhook(Request $request)
    {
        if (! filled(config('services.stripe.webhook_secret')) || ! filled(config('services.stripe.secret'))) return response('', 503);
        try {
            $event = Webhook::constructEvent($request->getContent(), $request->header('Stripe-Signature', ''), config('services.stripe.webhook_secret'));
        } catch (\UnexpectedValueException|\Stripe\Exception\SignatureVerificationException $e) {
            return response('', 400);
        }
        if (! in_array($event->type, ['payment_intent.succeeded', 'payment_intent.payment_failed', 'payment_intent.processing', 'payment_intent.canceled', 'charge.refunded', 'charge.updated'], true)) return response('', 200);
        $object = $event->data->object;
        $intentId = str_starts_with($event->type, 'payment_intent.') ? $object->id : $object->payment_intent;
        if (! $intentId) return response('', 200);
        try {
            $stripe = new StripeClient(config('services.stripe.secret'));
            $intent = $stripe->paymentIntents->retrieve($intentId, ['expand' => ['latest_charge.balance_transaction']]);
            $donation = Donation::where('stripe_payment_intent_id', $intent->id)->first();
            // A webhook can arrive before the create request has saved the PaymentIntent ID.
            if (! $donation && isset($intent->metadata->donation_id)) {
                $donation = Donation::whereKey($intent->metadata->donation_id)->whereNull('stripe_payment_intent_id')->first();
            }
            if (! $donation || $intent->amount !== $donation->amount || $intent->currency !== $donation->currency) return response('', 200);
            $charge = $intent->latest_charge;
            $balance = is_object($charge) ? $charge->balance_transaction : null;
            $donation->update([
                'stripe_payment_intent_id' => $intent->id,
                'status' => $intent->status,
                'stripe_charge_id' => is_object($charge) ? $charge->id : null,
                'stripe_fee' => is_object($balance) ? $balance->fee : null,
                'stripe_net' => is_object($balance) ? $balance->net : null,
                'balance_currency' => is_object($balance) ? $balance->currency : null,
                'refunded_amount' => is_object($charge) ? $charge->amount_refunded : 0,
                'paid_at' => $intent->status === 'succeeded' ? ($donation->paid_at ?? now()) : $donation->paid_at,
            ]);
        } catch (\Throwable $e) {
            Log::error('Stripe donation webhook failed', ['exception' => $e]);
            return response('', 500);
        }
        return response('', 200);
    }
}
