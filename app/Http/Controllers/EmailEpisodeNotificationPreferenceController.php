<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailEpisodeNotificationPreferenceController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
        ]);

        $enabled = (bool) $validated['enabled'];

        $request->user()->update([
            'episode_email_notifications_enabled' => $enabled,
        ]);

        return back()->with(
            'success',
            $enabled
                ? 'Listo: volverás a recibir avisos cuando haya episodios nuevos.'
                : 'Los avisos de nuevos episodios por correo quedaron pausados.'
        );
    }
}
