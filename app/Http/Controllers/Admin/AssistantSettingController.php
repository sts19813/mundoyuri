<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssistantSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AssistantSettingController extends Controller
{
    public function edit(): View
    {
        $settings = AssistantSetting::current();

        return view('admin.settings.assistant', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'initial_state' => ['required', Rule::in(['expanded', 'minimized'])],
            'remember_user_state' => ['nullable', 'boolean'],
            'initial_delay_seconds' => ['required', 'integer', 'min:0', 'max:600'],
            'message_interval_seconds' => ['required', 'integer', 'min:5', 'max:3600'],
            'bubble_duration_seconds' => ['required', 'integer', 'min:3', 'max:60'],
            'peek_duration_seconds' => ['required', 'integer', 'min:3', 'max:60'],
            'messages' => ['required', 'array', 'min:1', 'max:20'],
            'messages.*.text' => ['required', 'string', 'min:3', 'max:300'],
            'messages.*.peek' => ['nullable', 'string', 'max:160'],
            'messages.*.audience' => ['required', Rule::in(['all', 'guest', 'authenticated'])],
            'messages.*.action_type' => [
                'required',
                Rule::in(['none', 'report', 'request', 'message', 'register', 'catalog', 'external']),
            ],
            'messages.*.label' => ['nullable', 'string', 'max:80'],
            'messages.*.url' => [
                'nullable',
                'required_if:messages.*.action_type,external',
                'url:http,https',
                'max:2048',
            ],
        ]);

        $messages = collect($validated['messages'])
            ->map(function (array $message): array {
                $actionType = $message['action_type'];

                return [
                    'text' => trim($message['text']),
                    'peek' => trim((string) ($message['peek'] ?? '')),
                    'audience' => $message['audience'],
                    'action_type' => $actionType,
                    'label' => $actionType === 'none' ? '' : trim((string) ($message['label'] ?? '')),
                    'url' => $actionType === 'external' ? trim((string) ($message['url'] ?? '')) : '',
                ];
            })
            ->values()
            ->all();

        AssistantSetting::query()->updateOrCreate(
            ['id' => 1],
            [
                'enabled' => $request->boolean('enabled'),
                'initial_state' => $validated['initial_state'],
                'remember_user_state' => $request->boolean('remember_user_state'),
                'initial_delay_seconds' => $validated['initial_delay_seconds'],
                'message_interval_seconds' => $validated['message_interval_seconds'],
                'bubble_duration_seconds' => $validated['bubble_duration_seconds'],
                'peek_duration_seconds' => $validated['peek_duration_seconds'],
                'messages' => $messages,
            ]
        );

        return back()->with('success', 'Configuración de Miyu guardada.');
    }
}
