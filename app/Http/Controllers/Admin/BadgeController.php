<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBadgeRequest;
use App\Http\Requests\Admin\UpdateBadgeRequest;
use App\Models\Badge;
use App\Models\BadgeAwardLog;
use App\Services\BadgeAutomationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BadgeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(BadgeAutomationService $automation): View
    {
        $this->authorize('manage', Badge::class);

        $badges = Badge::query()->withCount(['users', 'legacyProfiles'])->ordered()->paginate(100);
        $logs = BadgeAwardLog::query()->with(['user', 'actor'])->latest()->paginate(100, ['*'], 'history_page');
        $automationRules = collect($automation->definitions())
            ->map(fn (array $definition) => $definition['condition'])
            ->all();

        return view('admin.badges.index', compact('badges', 'logs', 'automationRules'));
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

    public function syncAutomation(Request $request, BadgeAutomationService $automation): RedirectResponse
    {
        $this->authorize('manage', Badge::class);

        $result = $automation->syncAll();

        return back()->with('success', "Automatización revisada para {$result['users']} usuarios: {$result['awarded']} insignias asignadas y {$result['revoked']} retiradas.");
    }
}
