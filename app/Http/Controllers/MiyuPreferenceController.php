<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MiyuPreferenceController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
        ]);

        $enabled = (bool) $validated['enabled'];

        $request->user()->update([
            'miyu_enabled' => $enabled,
        ]);

        return back()->with(
            'success',
            $enabled
                ? 'Miyu quedó activada para tu cuenta.'
                : 'Miyu quedó desactivada para tu cuenta.'
        );
    }
}
