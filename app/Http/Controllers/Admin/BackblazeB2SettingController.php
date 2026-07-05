<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BackblazeB2Setting;
use App\Services\BackblazeB2Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class BackblazeB2SettingController extends Controller
{
    public function edit(): View
    {
        $settings = BackblazeB2Setting::current();

        return view('admin.settings.backblaze-b2', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $settings = BackblazeB2Setting::current();
        $enabled = $request->boolean('enabled');

        $validated = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'key_id' => [Rule::requiredIf($enabled), 'nullable', 'string', 'max:255'],
            'application_key' => [
                Rule::requiredIf($enabled && blank($settings->application_key)),
                'nullable',
                'string',
                'max:1000',
            ],
            'bucket_name' => [Rule::requiredIf($enabled), 'nullable', 'string', 'min:6', 'max:63'],
            'bucket_id' => ['nullable', 'string', 'max:255'],
            'token_ttl_seconds' => ['required', 'integer', 'min:60', 'max:604800'],
        ]);

        $data = [
            'enabled' => $enabled,
            'key_id' => trim((string) ($validated['key_id'] ?? '')) ?: null,
            'bucket_name' => trim((string) ($validated['bucket_name'] ?? '')) ?: null,
            'bucket_id' => trim((string) ($validated['bucket_id'] ?? '')) ?: null,
            'token_ttl_seconds' => $validated['token_ttl_seconds'],
        ];

        if (filled($validated['application_key'] ?? null)) {
            $data['application_key'] = trim($validated['application_key']);
        }

        $settings->fill($data)->save();

        return back()->with('success', 'Configuración de Backblaze B2 guardada.');
    }

    public function verify(BackblazeB2Service $backblaze): RedirectResponse
    {
        $settings = BackblazeB2Setting::current();

        if (! $settings->hasRequiredCredentials()) {
            $missing = collect([
                'Key ID' => $settings->key_id,
                'Application Key' => $settings->application_key,
                'nombre del bucket' => $settings->bucket_name,
            ])->filter(fn (mixed $value) => blank($value))->keys()->join(', ');

            return back()->with('error', 'Falta completar: '.$missing.'. Guarda la configuración y vuelve a probar.');
        }

        try {
            $result = $backblaze->verify($settings);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        $settings->update([
            'bucket_id' => $result['bucket_id'],
            'bucket_name' => $result['bucket_name'],
            'download_url' => $result['download_url'],
            'last_verified_at' => now(),
        ]);

        $activationNotice = $settings->enabled ? '' : ' Activa la reproducción y guarda para comenzar a usarla.';

        return back()->with('success', 'Conexión correcta. Bucket '.$result['bucket_name'].' ('.$result['bucket_type'].').'.$activationNotice);
    }
}
