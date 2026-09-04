<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreForumRequest;
use App\Http\Requests\Admin\UpdateForumRequest;
use App\Models\Forum;
use App\Models\ForumCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ForumController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(): View
    {
        return view('admin.forums.index', ['forums' => Forum::query()->with('category')->withCount('threads')->orderBy('forum_category_id')->orderBy('sort_order')->paginate(30)]);
    }

    public function create(): View
    {
        return view('admin.forums.create', ['categories' => ForumCategory::query()->orderBy('sort_order')->get()]);
    }

    public function store(StoreForumRequest $request): RedirectResponse
    {
        Forum::query()->create($request->validated());

        return redirect()->route('admin.forums.index')->with('success', 'Foro creado correctamente.');
    }

    public function edit(Forum $forum): View
    {
        return view('admin.forums.edit', ['forum' => $forum, 'categories' => ForumCategory::query()->orderBy('sort_order')->get()]);
    }

    public function update(UpdateForumRequest $request, Forum $forum): RedirectResponse
    {
        $forum->update($request->validated());

        return redirect()->route('admin.forums.index')->with('success', 'Foro actualizado correctamente.');
    }
}
