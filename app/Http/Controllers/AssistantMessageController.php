<?php
 /*test codigo comentado */
/* edición de archivos en Mobile*/
namespace App\Http\Controllers;

use App\Models\AssistantMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AssistantMessageController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        abort_if($request->user() && ! $request->user()->miyu_enabled, 403);

        $validated = $request->validate([
            'type' => ['required', Rule::in(['report', 'message', 'request'])],
            'contact_email' => $request->user()
                ? ['nullable', 'email:rfc', 'max:255']
                : ['required', 'email:rfc', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:1500'],
            'page_url' => ['nullable', 'url:http,https', 'max:2048'],
        ]);

        AssistantMessage::create([
            ...$validated,
            'contact_email' => $request->user()?->email ?? $validated['contact_email'],
            'user_id' => $request->user()?->id,
            'status' => 'unread',
        ]);

        return response()->json([
            'message' => '¡Listo! Tu mensaje llegó al equipo.',
        ], 201);
    }
}
