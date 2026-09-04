<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommunityModerationActionRequest;
use App\Http\Requests\UpdateCommunityReportRequest;
use App\Models\CommunityReport;
use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Models\User;
use App\Services\CommunityModerationService;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommunityReportController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', CommunityReport::class);
        $status = $request->string('status')->toString();
        abort_unless($status === '' || in_array($status, CommunityReport::STATUSES, true), 404);

        $reports = CommunityReport::query()
            ->with([
                'reporter',
                'reviewer',
                'reportable' => fn (MorphTo $morphTo) => $morphTo->morphWith([
                    ForumPost::class => ['thread'],
                    ForumThread::class => ['forum'],
                    User::class => [],
                ]),
            ])
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.community-reports.index', compact('reports', 'status'));
    }

    public function update(UpdateCommunityReportRequest $request, CommunityReport $communityReport, CommunityModerationService $moderation): RedirectResponse
    {
        $moderation->review(
            $communityReport,
            $request->user(),
            $request->validated('status'),
            $request->validated('resolution'),
        );

        return back()->with('success', 'Reporte actualizado.');
    }

    public function action(StoreCommunityModerationActionRequest $request, CommunityReport $communityReport, CommunityModerationService $moderation): RedirectResponse
    {
        $moderation->act($communityReport, $request->user(), $request->validated('action'));

        return back()->with('success', 'Acción de moderación aplicada.');
    }
}
