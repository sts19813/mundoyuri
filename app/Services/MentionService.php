<?php

namespace App\Services;

use App\Models\ForumMention;
use App\Models\ForumPost;
use App\Models\User;
use App\Notifications\ForumMentionNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use DOMDocument;
use DOMElement;
use DOMText;

class MentionService
{
    // A dot is supported inside an alias, but never consumed as sentence punctuation.
    private const PATTERN = '/(?<![\pL\pN_])@([\pL\pN](?:[\pL\pN_-]|\.(?=[\pL\pN_-])){1,59})/u';

    /**
     * Synchronize valid mentions for a forum post and notify only newly added users.
     *
     * @return array<int, int>
     */
    public function record(ForumPost $post): array
    {
        $aliases = $this->extractAliases($post->body);

        if ($aliases->isEmpty()) {
            $post->mentions()->delete();

            return [];
        }

        $author = $post->author;
        $users = User::query()
            ->whereNotNull('alias')
            ->whereIn(DB::raw('LOWER(alias)'), $aliases)
            ->where('id', '!=', $post->user_id)
            ->get();

        $eligibleUsers = $users
            ->filter(fn (User $user) => $author && ! $author->cannotInteractWith($user))
            ->values();

        $post->mentions()->whereNotIn('mentioned_user_id', $eligibleUsers->modelKeys())->delete();

        $mentioned = [];
        foreach ($eligibleUsers as $user) {
            $mention = ForumMention::query()->firstOrCreate([
                'forum_post_id' => $post->id,
                'mentioned_user_id' => $user->id,
            ], ['mentioner_user_id' => $post->user_id]);

            // Existing records mean this person was already notified for this same
            // unchanged mention, including a regular post edit.
            if (! $mention->wasRecentlyCreated) {
                continue;
            }

            $mentioned[] = $user->id;
            $user->notify(new ForumMentionNotification($post, $author));
        }

        return $mentioned;
    }

    /**
     * Escape a body and replace only recorded, valid aliases with profile links.
     * No source HTML is rendered as markup.
     *
     * @param  iterable<User|null>  $mentionedUsers
     */
    public function render(string $body, iterable $mentionedUsers): HtmlString
    {
        $usersByAlias = collect($mentionedUsers)
            ->filter(fn ($user) => $user instanceof User && filled($user->alias))
            ->keyBy(fn (User $user) => mb_strtolower($user->alias));

        $escaped = e($body);
        $rendered = preg_replace_callback(self::PATTERN, function (array $match) use ($usersByAlias): string {
            $alias = mb_strtolower($match[1]);
            /** @var User|null $user */
            $user = $usersByAlias->get($alias);

            if (! $user) {
                return $match[0];
            }

            return '<a class="forum-mention" href="'.e($user->publicProfileUrl()).'">'.e($match[0]).'</a>';
        }, $escaped) ?? $escaped;

        return new HtmlString($rendered);
    }

    /**
     * Replace recorded mentions inside already-sanitized HTML text nodes.
     *
     * @param  iterable<User|null>  $mentionedUsers
     */
    public function renderHtml(string $html, iterable $mentionedUsers): HtmlString
    {
        $usersByAlias = collect($mentionedUsers)
            ->filter(fn ($user) => $user instanceof User && filled($user->alias))
            ->keyBy(fn (User $user) => mb_strtolower($user->alias));

        if ($usersByAlias->isEmpty()) {
            return new HtmlString($html);
        }

        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8"><body>'.$html.'</body>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $body = $document->getElementsByTagName('body')->item(0);
        if (! $body) {
            return new HtmlString($html);
        }

        $this->replaceMentionsInNode($document, $body, $usersByAlias);

        $rendered = '';
        foreach ($body->childNodes as $child) {
            $rendered .= $document->saveHTML($child);
        }

        return new HtmlString($rendered);
    }

    /** @return Collection<int, string> */
    private function extractAliases(string $body)
    {
        preg_match_all(self::PATTERN, $body, $matches);

        return collect($matches[1] ?? [])
            ->map(fn (string $alias) => mb_strtolower($alias))
            ->unique()
            ->values();
    }

    /** @param Collection<string, User> $usersByAlias */
    private function replaceMentionsInNode(DOMDocument $document, \DOMNode $node, Collection $usersByAlias): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMText) {
                $this->replaceMentionText($document, $child, $usersByAlias);

                continue;
            }

            if ($child instanceof DOMElement && ! in_array(strtolower($child->tagName), ['a', 'code', 'pre'], true)) {
                $this->replaceMentionsInNode($document, $child, $usersByAlias);
            }
        }
    }

    /** @param Collection<string, User> $usersByAlias */
    private function replaceMentionText(DOMDocument $document, DOMText $text, Collection $usersByAlias): void
    {
        $value = $text->nodeValue;
        if (! is_string($value) || preg_match(self::PATTERN, $value) !== 1) {
            return;
        }

        $fragment = $document->createDocumentFragment();
        $offset = 0;
        preg_match_all(self::PATTERN, $value, $matches, PREG_OFFSET_CAPTURE);

        foreach ($matches[0] as $index => [$mention, $position]) {
            $alias = mb_strtolower($matches[1][$index][0]);
            /** @var User|null $user */
            $user = $usersByAlias->get($alias);
            $fragment->appendChild($document->createTextNode(substr($value, $offset, $position - $offset)));

            if ($user) {
                $link = $document->createElement('a', $mention);
                $link->setAttribute('class', 'forum-mention');
                $link->setAttribute('href', $user->publicProfileUrl());
                $fragment->appendChild($link);
            } else {
                $fragment->appendChild($document->createTextNode($mention));
            }

            $offset = $position + strlen($mention);
        }

        $fragment->appendChild($document->createTextNode(substr($value, $offset)));
        $text->parentNode?->replaceChild($fragment, $text);
    }
}
