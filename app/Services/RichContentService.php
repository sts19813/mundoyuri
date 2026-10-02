<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class RichContentService
{
    /** @var array<string, list<string>> */
    private const ALLOWED_ATTRIBUTES = [
        'a' => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'title'],
        'iframe' => ['src', 'title', 'allow', 'allowfullscreen', 'loading', 'referrerpolicy'],
        'p' => ['class'],
        'h2' => ['class'],
        'h3' => ['class'],
        'blockquote' => ['class'],
        'ol' => ['class'],
        'ul' => ['class'],
        'li' => ['class'],
        'pre' => ['class'],
        'code' => ['class'],
    ];

    /** @var list<string> */
    private const ALLOWED_TAGS = [
        'a', 'blockquote', 'br', 'code', 'div', 'em', 'h2', 'h3', 'iframe', 'img',
        'li', 'ol', 'p', 'pre', 's', 'span', 'strong', 'u', 'ul',
    ];

    public function sanitize(?string $html): string
    {
        $html = trim((string) $html);

        if ($html === '') {
            return '';
        }

        $document = $this->document($html);
        $body = $document->getElementsByTagName('body')->item(0);

        if (! $body) {
            return '';
        }

        $this->sanitizeChildren($body);
        $this->convertYoutubeAnchors($document, $body);

        $clean = '';
        foreach ($body->childNodes as $child) {
            $clean .= $document->saveHTML($child);
        }

        return trim($clean);
    }

    public function isRich(?string $html): bool
    {
        return preg_match('/<(p|h2|h3|ul|ol|li|blockquote|pre|strong|em|u|s|a|img|iframe|div|br)\b/i', (string) $html) === 1;
    }

    /** @param iterable<\App\Models\User|null> $mentionedUsers */
    public function renderForumBody(string $body, iterable $mentionedUsers): HtmlString
    {
        if (! $this->isRich($body)) {
            return new HtmlString(nl2br(app(MentionService::class)->render($body, $mentionedUsers)->toHtml()));
        }

        return app(MentionService::class)->renderHtml($this->sanitize($body), $mentionedUsers);
    }

    public function renderProfileBiography(?string $html): HtmlString
    {
        $html = $this->sanitize($html);

        if (! $this->isRich($html)) {
            return new HtmlString(nl2br(e($html)));
        }

        return new HtmlString($html);
    }

    public function plainText(string $html): string
    {
        return trim(html_entity_decode(strip_tags($this->sanitize($html)), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    private function sanitizeChildren(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMElement) {
                $tag = strtolower($child->tagName);

                if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                    if (in_array($tag, ['script', 'style'], true)) {
                        $child->parentNode?->removeChild($child);

                        continue;
                    }

                    $this->unwrap($child);

                    continue;
                }

                $this->sanitizeAttributes($child, $tag);
            }

            $this->sanitizeChildren($child);
        }
    }

    private function sanitizeAttributes(DOMElement $element, string $tag): void
    {
        $allowed = self::ALLOWED_ATTRIBUTES[$tag] ?? [];

        foreach (iterator_to_array($element->attributes) as $attribute) {
            $name = strtolower($attribute->name);
            $value = trim($attribute->value);

            if (! in_array($name, $allowed, true) || str_starts_with($name, 'on')) {
                $element->removeAttribute($attribute->name);

                continue;
            }

            if (($name === 'href' || $name === 'src') && ! $this->allowedUrl($value, $tag)) {
                $element->removeAttribute($attribute->name);
            }
        }

        if ($tag === 'a' && $element->hasAttribute('href')) {
            $element->setAttribute('target', '_blank');
            $element->setAttribute('rel', 'nofollow noopener noreferrer');
        }

        if ($tag === 'iframe') {
            $src = $this->youtubeEmbedUrl($element->getAttribute('src'));
            if (! $src) {
                $this->unwrap($element);

                return;
            }
            $element->setAttribute('src', $src);
            $element->setAttribute('loading', 'lazy');
            $element->setAttribute('allowfullscreen', 'allowfullscreen');
            $element->setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share');
            $element->setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');
            $element->setAttribute('title', $element->getAttribute('title') ?: 'Video incrustado');
        }

        if ($tag === 'img') {
            $element->setAttribute('loading', 'lazy');
            $element->setAttribute('alt', Str::limit($element->getAttribute('alt') ?: 'Imagen insertada', 160, ''));
        }

        if ($element->hasAttribute('class')) {
            $classes = collect(explode(' ', $element->getAttribute('class')))
                ->filter(fn (string $class): bool => preg_match('/^ql-align-(center|right|justify)$/', $class) === 1)
                ->implode(' ');

            if ($classes === '') {
                $element->removeAttribute('class');
            } else {
                $element->setAttribute('class', $classes);
            }
        }
    }

    private function convertYoutubeAnchors(DOMDocument $document, DOMNode $body): void
    {
        foreach (iterator_to_array($document->getElementsByTagName('a')) as $anchor) {
            if (! $anchor instanceof DOMElement) {
                continue;
            }

            $embed = $this->youtubeEmbedUrl($anchor->getAttribute('href'));
            if (! $embed) {
                continue;
            }

            $iframe = $document->createElement('iframe');
            $iframe->setAttribute('src', $embed);
            $iframe->setAttribute('title', 'Video de YouTube');
            $iframe->setAttribute('loading', 'lazy');
            $iframe->setAttribute('allowfullscreen', 'allowfullscreen');
            $iframe->setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share');
            $iframe->setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');
            $anchor->parentNode?->replaceChild($iframe, $anchor);
        }
    }

    private function allowedUrl(string $url, string $tag): bool
    {
        if ($url === '') {
            return false;
        }

        if (str_starts_with($url, '/storage/community-post-images/')) {
            return true;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (! in_array($scheme, ['http', 'https'], true)) {
            return false;
        }

        if ($tag === 'iframe') {
            return $this->youtubeEmbedUrl($url) !== null;
        }

        if ($tag === 'img') {
            $host = parse_url($url, PHP_URL_HOST);

            return $host === request()->getHost();
        }

        return true;
    }

    private function youtubeEmbedUrl(string $url): ?string
    {
        $url = trim($url);

        if ($url === '') {
            return null;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $path = (string) parse_url($url, PHP_URL_PATH);
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        $id = null;
        if (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com'], true)) {
            if (str_starts_with($path, '/embed/')) {
                $id = trim(substr($path, 7), '/');
            } elseif (($query['v'] ?? null) !== null) {
                $id = (string) $query['v'];
            } elseif (str_starts_with($path, '/shorts/')) {
                $id = trim(substr($path, 8), '/');
            }
        } elseif (in_array($host, ['youtu.be', 'www.youtu.be'], true)) {
            $id = trim($path, '/');
        } elseif (in_array($host, ['youtube-nocookie.com', 'www.youtube-nocookie.com'], true) && str_starts_with($path, '/embed/')) {
            $id = trim(substr($path, 7), '/');
        }

        if (! $id || preg_match('/^[A-Za-z0-9_-]{6,32}$/', $id) !== 1) {
            return null;
        }

        return 'https://www.youtube-nocookie.com/embed/'.$id;
    }

    private function unwrap(DOMElement $element): void
    {
        while ($element->firstChild) {
            $element->parentNode?->insertBefore($element->firstChild, $element);
        }

        $element->parentNode?->removeChild($element);
    }

    private function document(string $html): DOMDocument
    {
        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8"><body>'.$html.'</body>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return $document;
    }
}
