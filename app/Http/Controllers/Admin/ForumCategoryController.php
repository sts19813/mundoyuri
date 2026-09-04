<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreForumCategoryRequest;
use App\Http\Requests\Admin\UpdateForumCategoryRequest;
use App\Models\ForumCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ForumCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(): View
    {
        return view('admin.forum-categories.index', ['categories' => ForumCategory::query()->withCount('forums')->orderBy('sort_order')->paginate(30)]);
    }

    public function create(): View
    {
        return view('admin.forum-categories.create');
    }

    public function store(StoreForumCategoryRequest $request): RedirectResponse
    {
        ForumCategory::query()->create($request->validated());

        return redirect()->route('admin.forum-categories.index')->with('success', 'Categoría creada correctamente.');
    }

    public function edit(ForumCategory $forumCategory): View
    {
        return view('admin.forum-categories.edit', compact('forumCategory'));
    }

    public function update(UpdateForumCategoryRequest $request, ForumCategory $forumCategory): RedirectResponse
    {
        $forumCategory->update($request->validated());

        return redirect()->route('admin.forum-categories.index')->with('success', 'Categoría actualizada correctamente.');
    }
}
