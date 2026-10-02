<?php

namespace App\Http\Controllers;

use App\Services\CommunityPostImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RichEditorUploadController extends Controller
{
    public function store(Request $request, CommunityPostImageService $images): JsonResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:20480'],
        ]);

        $path = $images->store($validated['image']);

        return response()->json([
            'url' => Storage::disk('public')->url($path),
        ], 201);
    }
}
