<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeasonThemeSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SeasonThemeSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.season-theme', [
            'settings' => SeasonThemeSetting::current(),
            'themes' => SeasonThemeSetting::THEMES,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'theme' => ['required', Rule::in(array_keys(SeasonThemeSetting::THEMES))],
        ]);

        SeasonThemeSetting::query()->updateOrCreate(['id' => 1], [
            'enabled' => $request->boolean('enabled'),
            'theme' => $validated['theme'],
        ]);

        return back()->with('success', 'Tema estacional actualizado.');
    }
}
