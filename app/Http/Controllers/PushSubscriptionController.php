<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        abort_unless($this->webPushConfigured(), 503, 'Las notificaciones del dispositivo todavía no están configuradas.');

        $validated = $request->validate([
            'endpoint' => ['required', 'string', 'max:2048'],
            'keys' => ['required', 'array'],
            'keys.p256dh' => ['required', 'string'],
            'keys.auth' => ['required', 'string'],
            'contentEncoding' => ['nullable', 'string', 'max:50'],
        ]);

        $request->user()->updatePushSubscription(
            $validated['endpoint'],
            $validated['keys']['p256dh'],
            $validated['keys']['auth'],
            $validated['contentEncoding'] ?? 'aes128gcm'
        );

        $request->user()->update(['push_notifications_enabled' => true]);

        return response()->json([
            'enabled' => true,
            'message' => 'Las notificaciones del dispositivo quedaron activadas.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'string', 'max:2048'],
        ]);

        $request->user()->deletePushSubscription($validated['endpoint']);

        if (! $request->user()->pushSubscriptions()->exists()) {
            $request->user()->update(['push_notifications_enabled' => false]);
        }

        return response()->json([
            'enabled' => (bool) $request->user()->fresh()->push_notifications_enabled,
            'message' => 'Las notificaciones del dispositivo quedaron desactivadas en este navegador.',
        ]);
    }

    public function preference(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
        ]);

        $request->user()->update([
            'push_notifications_enabled' => (bool) $validated['enabled'],
        ]);

        return response()->json([
            'enabled' => (bool) $request->user()->fresh()->push_notifications_enabled,
        ]);
    }

    private function webPushConfigured(): bool
    {
        return filled(config('webpush.vapid.public_key'))
            && filled(config('webpush.vapid.private_key'))
            && filled(config('webpush.vapid.subject'));
    }
}
