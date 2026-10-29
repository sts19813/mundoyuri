<?php

namespace App\Http\Controllers;

use App\Models\Sticker;
use App\Services\StickerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StickerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $viewer = $request->user();
        $tab = $request->query('tab', 'recent');
        abort_unless(in_array($tab, ['recent', 'general', 'mine'], true), 422);

        $perPage = 24;
        $page = max(1, (int) $request->query('page', 1));
        $offset = ($page - 1) * $perPage;

        if ($tab === 'recent') {
            // Recientes es un atajo personal, no una biblioteca ilimitada en memoria.
            $maximumRecent = 48;
            $sentStickers = DB::table('direct_messages')
                ->select('attachment_path')
                ->selectRaw('MAX(created_at) as last_sent_at, COUNT(*) as times_sent')
                ->where('sender_id', $viewer->id)
                ->where('is_sticker', true)
                ->whereNotNull('attachment_path')
                ->groupBy('attachment_path');

            $query = Sticker::query()
                ->joinSub($sentStickers, 'sent_stickers', fn ($join) => $join->on('stickers.path', '=', 'sent_stickers.attachment_path'))
                ->visibleTo($viewer)
                ->orderByDesc('sent_stickers.last_sent_at')
                ->orderByDesc('sent_stickers.times_sent')
                ->orderByDesc('stickers.id')
                ->select('stickers.*');
            $limit = min($perPage + 1, max(0, $maximumRecent - $offset));
        } elseif ($tab === 'general') {
            $query = Sticker::query()
                ->visibleTo($viewer)
                ->where('is_general', true)
                ->latest('id');
            $limit = $perPage + 1;
        } else {
            $query = Sticker::query()
                ->where('owner_id', $viewer->id)
                ->latest('id');
            $limit = $perPage + 1;
        }

        $stickers = $query->offset($offset)->limit($limit)->get();
        $hasMore = $stickers->count() > $perPage;
        $stickers = $stickers->take($perPage);

        return response()->json([
            'tab' => $tab,
            'stickers' => $stickers->map(fn (Sticker $sticker) => $this->payload($sticker))->values(),
            'has_more' => $hasMore,
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
