<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBadgeRequest;
use App\Http\Requests\Admin\UpdateBadgeRequest;
use App\Models\Badge;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BadgeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(): View
    {
        $this->authorize('manage', Badge::class);

        $badges = Badge::query()->withCount('users')->ordered()->paginate(20);

        return view('admin.badges.index', compact('badges'));
    }

    public function create(): View
    {
        $this->authorize('manage', Badge::class);

        return view('admin.badges.create');
    }

    public function store(StoreBadgeRequest $request): RedirectResponse
    {
        Badge::query()->create($request->validated());

        return redirect()->route('admin.badges.index')->with('success', 'Insignia creada correctamente.');
    }

    public function edit(Badge $badge): View
    {
        $this->authorize('manage', $badge);

        return view('admin.badges.edit', compact('badge'));
    }

    public function update(UpdateBadgeRequest $request, Badge $badge): RedirectResponse
    {
        $badge->update($request->validated());

        return redirect()->route('admin.badges.index')->with('success', 'Insignia actualizada correctamente.');
    }

    public function destroy(Badge $badge): RedirectResponse
    {
        $this->authorize('manage', $badge);
        $badge->delete();

        return redirect()->route('admin.badges.index')->with('success', 'Insignia eliminada correctamente.');
    }
}
