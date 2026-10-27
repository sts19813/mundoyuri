<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sticker;
use App\Services\StickerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StickerController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(): View
    {
        $stickers = Sticker::query()->with(['owner', 'approver'])->latest('id')->paginate(30);

        return view('admin.stickers.index', compact('stickers'));
    }

    public function store(Request $request, StickerService $stickers): RedirectResponse
    {
        $validated = $request->validate([
            'sticker' => [
                'required',
                'file',
                'max:'.config('messenger.sticker_max_kilobytes'),
                'mimes:png,webp,gif,jpg,jpeg',
            ],
        ]);

        $stickers->store($validated['sticker'], $request->user(), true);

        return back()->with('success', 'Sticker general creado. Ya está disponible para toda la comunidad.');
    }

    public function approve(Request $request, Sticker $sticker): RedirectResponse
    {
        if (! $sticker->is_general) {
            $sticker->update([
                'is_general' => true,
                'approved_at' => now(),
                'approved_by' => $request->user()->id,
            ]);
        }

        return back()->with('success', 'Sticker aprobado y publicado para toda la comunidad.');
    }

    public function destroy(Sticker $sticker): RedirectResponse
    {
        $sticker->delete();

        return back()->with('success', 'Sticker eliminado del catálogo. Los mensajes antiguos no se alteran.');
    }
}
