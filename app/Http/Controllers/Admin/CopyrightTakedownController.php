<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CopyrightTakedown;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CopyrightTakedownController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        abort_unless($status === '' || in_array($status, CopyrightTakedown::STATUSES, true), 404);

        $takedowns = CopyrightTakedown::query()
            ->with(['recorder', 'resolver'])
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest('received_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.copyright-takedowns.index', compact('takedowns', 'status'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'received_at' => ['required', 'date'],
            'claimant_name' => ['required', 'string', 'max:255'],
            'claimant_email' => ['required', 'email:rfc', 'max:255'],
            'claimant_organization' => ['nullable', 'string', 'max:255'],
            'work_description' => ['required', 'string', 'max:5000'],
            'reported_urls' => ['required', 'string', 'max:12000'],
        ]);

        CopyrightTakedown::create([...$data, 'recorded_by' => $request->user()->id]);

        return back()->with('success', 'Aviso registrado. Los registros no se eliminan desde el panel.');
    }

    public function update(Request $request, CopyrightTakedown $copyrightTakedown): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(CopyrightTakedown::STATUSES)],
            'action_taken' => ['nullable', Rule::in(CopyrightTakedown::ACTIONS)],
            'resolution_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $isResolved = in_array($data['status'], ['resolved', 'dismissed'], true);
        $copyrightTakedown->update([
            ...$data,
            'resolved_at' => $isResolved ? now() : null,
            'resolved_by' => $isResolved ? $request->user()->id : null,
        ]);

        return back()->with('success', 'Registro actualizado.');
    }
}
