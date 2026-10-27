<?php

namespace App\Http\Controllers;

use App\Models\Sticker;
use App\Services\StickerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StickerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $viewer = $request->user();
        $stickers = Sticker::query()->visibleTo($viewer)->latest('id')->get();

        return response()->json([
            'general' => $stickers->where('is_general', true)->map(fn (Sticker $sticker) => $this->payload($sticker))->values(),
            'mine' => $stickers->where('owner_id', $viewer->id)->where('is_general', false)->map(fn (Sticker $sticker) => $this->payload($sticker))->values(),
        ]);
    }

    public function store(Request $request, StickerService $stickers): JsonResponse
    {
        $validated = $request->validate([
            'sticker' => [
                'required',
                'file',
                'max:'.config('messenger.sticker_max_kilobytes'),
                'mimes:png,webp,gif,jpg,jpeg',
            ],
        ]);

        $sticker = $stickers->store($validated['sticker'], $request->user());

        return response()->json(['sticker' => $this->payload($sticker)], 201);
    }

    public function destroy(Request $request, Sticker $sticker): JsonResponse
    {
        abort_unless($sticker->owner_id === $request->user()->id && ! $sticker->is_general, 403);
        $sticker->delete();

        return response()->json(status: 204);
    }

    public function file(Request $request, Sticker $sticker): StreamedResponse
    {
        abort_unless($sticker->canBeViewedBy($request->user()), 404);
        abort_unless(Storage::disk('local')->exists($sticker->path), 404);

        return Storage::disk('local')->response($sticker->path, $sticker->name, [
            'Content-Type' => $sticker->mime,
            'Cache-Control' => 'private, max-age=86400',
        ], 'inline');
    }

    private function payload(Sticker $sticker): array
    {
        return [
            'id' => $sticker->id,
            'name' => $sticker->name,
            'url' => route('stickers.file', $sticker),
            'mime' => $sticker->mime,
            'is_animated' => $sticker->mime === 'image/gif',
            'is_general' => $sticker->is_general,
        ];
    }
}
