<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreForumPostRequest;
use App\Http\Requests\StoreQuestionRequest;
use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Services\CommunityReactionService;
use App\Services\ForumPostService;
use App\Services\QuestionService;
use App\Services\QuestionVoteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function index(Request $request, CommunityReactionService $reactions): View
    {
        $sort = $request->string('sort', 'recent')->toString();
        abort_unless(in_array($sort, ['recent', 'unanswered', 'popular'], true), 404);
        $search = trim((string) $request->query('q'));

        $questions = ForumThread::query()
            ->questions()
            ->where('is_hidden', false)
            ->with(['author'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', '%'.$search.'%')
                        ->orWhereHas('posts', fn ($posts) => $posts->where('is_initial', true)->where('body', 'like', '%'.$search.'%'));
                });
            });

        match ($sort) {
            'unanswered' => $questions->where('replies_count', 0)->latest(),
            'popular' => $questions->orderByDesc('upvotes_count')->orderByDesc('views_count')->latest(),
            default => $questions->latest(),
        };

        $questions = $questions->paginate(20)->withQueryString();
        $reactions->hydrateSummaries($questions->getCollection(), $request->user());

        return view('questions.index', compact('questions', 'sort', 'search'));
    }

    public function create(): View
    {
        return view('questions.create');
    }

    public function store(StoreQuestionRequest $request, QuestionService $questions): RedirectResponse
    {
        $question = $questions->create($request->user(), $request->validated('title'), $request->validated('body'));

        return redirect()->route('questions.show', $question)->with('success', 'Pregunta publicada correctamente.');
    }

    public function show(Request $request, ForumThread $thread, CommunityReactionService $reactions): View
    {
        abort_unless($thread->isQuestion(), 404);
        $thread->load(['author.badges', 'author.communityRank', 'acceptedAnswer']);
        $this->authorize('view', $thread);
        $thread->increment('views_count');

        $posts = $thread->posts()
            ->when(! $request->user()?->shouldEnterAdminPanel(), fn ($query) => $query->where('is_hidden', false))
            ->with(['author.badges', 'author.communityRank', 'mentions.mentionedUser'])
            ->oldest()
            ->paginate(20)
            ->withQueryString();

        $reactions->hydrateSummaries(collect([$thread])->merge($posts->getCollection()), $request->user());

        return view('questions.show', compact('thread', 'posts'));
    }

    public function answer(StoreForumPostRequest $request, ForumThread $thread, ForumPostService $posts): RedirectResponse
    {
        abort_unless($thread->isQuestion(), 404);
        $post = $posts->reply($thread, $request->user(), $request->validated('body'));

        return redirect()->to(route('questions.show', $thread).'#post-'.$post->id);
    }

    public function accept(Request $request, ForumThread $thread, ForumPost $post, QuestionService $questions): RedirectResponse
    {
        abort_unless($thread->isQuestion(), 404);
        $this->authorize('acceptAnswer', $thread);
        $questions->acceptAnswer($thread, $post, $request->user());

        return back()->with('success', 'Respuesta marcada como aceptada.');
    }

    public function voteQuestion(Request $request, ForumThread $thread, QuestionVoteService $votes): RedirectResponse
    {
        abort_unless($thread->isQuestion(), 404);
        $this->authorize('vote', $thread);

        $registered = $votes->voteQuestion($thread, $request->user());

        return back()->with($registered ? 'success' : 'error', $registered ? 'Voto registrado.' : 'Ya habías votado esta pregunta.');
    }

    public function voteAnswer(Request $request, ForumPost $post, QuestionVoteService $votes): RedirectResponse
    {
        $post->load('thread');
        abort_unless($post->thread->isQuestion() && ! $post->is_initial, 404);
        $this->authorize('vote', $post);

        $registered = $votes->voteAnswer($post, $request->user());

        return back()->with($registered ? 'success' : 'error', $registered ? 'Voto registrado.' : 'Ya habías votado esta respuesta.');
    }
}
