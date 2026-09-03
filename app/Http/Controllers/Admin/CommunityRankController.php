<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCommunityRankRequest;
use App\Http\Requests\Admin\UpdateCommunityRankRequest;
use App\Models\CommunityRank;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CommunityRankController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(): View
    {
        $communityRanks = CommunityRank::query()
            ->withCount('users')
            ->orderByDesc('priority')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.community-ranks.index', compact('communityRanks'));
    }

    public function create(): View
    {
        return view('admin.community-ranks.create');
    }

    public function store(StoreCommunityRankRequest $request): RedirectResponse
    {
        CommunityRank::query()->create($this->normalizedRankData($request->validated()));

        return redirect()
            ->route('admin.community-ranks.index')
            ->with('success', 'Rango comunitario creado correctamente.');
    }

    public function edit(CommunityRank $communityRank): View
    {
        return view('admin.community-ranks.edit', compact('communityRank'));
    }

    public function update(UpdateCommunityRankRequest $request, CommunityRank $communityRank): RedirectResponse
    {
        DB::transaction(function () use ($request, $communityRank): void {
            $wasSpecial = $communityRank->is_special;
            $communityRank->update($this->normalizedRankData($request->validated()));

            if ($wasSpecial && ! $communityRank->is_special) {
                $communityRank->users()->update(['community_rank_id' => null]);
            }
        });

        return redirect()
            ->route('admin.community-ranks.index')
            ->with('success', 'Rango comunitario actualizado correctamente.');
    }

    public function destroy(CommunityRank $communityRank): RedirectResponse
    {
        $communityRank->delete();

        return redirect()
            ->route('admin.community-ranks.index')
            ->with('success', 'Rango eliminado; sus miembros volverán al cálculo automático.');
    }

    /** @param array<string, mixed> $validated */
    private function normalizedRankData(array $validated): array
    {
        if ($validated['is_special']) {
            $validated['minimum_posts'] = null;
        }

        return $validated;
    }
}
