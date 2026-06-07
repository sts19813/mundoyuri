<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show()
    {
        return view('admin.profile.show', [
            'user' => auth()->user(),
        ]);
    }

    public function edit()
    {
        return redirect()->route('admin.profile.show');
    }

    public function update(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'alias' => ['nullable', 'string', 'max:50', Rule::unique(User::class)->ignore($user->id)],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'avatar_remove' => ['nullable', 'boolean'],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'alias' => $validated['alias'] ?? null,
            'email' => $validated['email'],
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($request->boolean('avatar_remove')) {
            $this->deleteProfileImage($user);
            $user->profile_image = null;
        }

        if ($request->hasFile('profile_image')) {
            $this->deleteProfileImage($user);
            $user->profile_image = $request->file('profile_image')->store('profile-images', 'public');
        }

        $user->save();

        return $this->profileResponse($request, 'Perfil actualizado exitosamente');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|min:8|confirmed',
        ]);

        auth()->user()->update([
            'password' => Hash::make($validated['password'])
        ]);

        return $this->profileResponse($request, 'Contraseña actualizada exitosamente');
    }

    private function deleteProfileImage(User $user): void
    {
        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }
    }

    private function profileResponse(Request $request, string $message): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $user = $request->user()->fresh();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => $message,
                'user' => [
                    'name' => $user->name,
                    'alias' => $user->alias,
                    'email' => $user->email,
                    'role' => $user->role,
                    'initials' => $user->initials(),
                    'avatar_url' => $user->avatarUrl(),
                ],
            ]);
        }

        $routeName = $request->route()?->getName() === 'profile.update'
            ? 'profile.edit'
            : 'admin.profile.show';

        return redirect()->route($routeName)->with('success', $message);
    }
}
